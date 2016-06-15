<?php
#  Copyright 2016 A Family For Every Child
#
#  Licensed under the Apache License, Version 2.0 (the "License");
#  you may not use this file except in compliance with the License.
#  You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
#
#  Unless required by applicable law or agreed to in writing, software
#  distributed under the License is distributed on an "AS IS" BASIS,
#  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#  See the License for the specific language governing permissions and
#  limitations under the License.

namespace Crawler\Databases\Salesforce;


use Crawler\DataTypes\AllChildren;
use Crawler\DataTypes\Attachment;
use Crawler\DataTypes\Child;
use Crawler\DataTypes\SiblingGroup;
require("dbs/salesforce/tools.php");
require("dbs/salesforce/cache_db.php");
use CacheAttachment;
use CacheChild;
use CacheContact;
use CacheGroup;
use CacheResolve;


/**
 * Crawler export into Salesforce Database
 */
class Salesforce
{

    /**
    * Setup defaults
    *
    * First we need config options and loghandler(s)
    *
    * @param array $cfg salesforce and cachedb config options
    * @param \Monolog\Handler\StreamHandler $lHandler logoutput handler
    */
    function __construct($cfg, $lHandler)
    {
        // Prep config
        $this->sf_username = $cfg["username"];
        $this->sf_pass = $cfg["password"];
        $this->sf_token = $cfg["token"];
        $this->sf_sandbox = $cfg["sandbox"];
        $this->sf_wsdl = $this->sf_sandbox ? "test" : "live";
        $this->cache_cfg = $cfg["cache_db"];
        $this->cfg = $cfg;

        // Setup logging
        $this->log = new \Monolog\Logger("Salesforce");
        $this->log->pushHandler($lHandler);
        $this->log->info("SalesForce Activated and ready to go!");

        // Cache init
        $this->em = init_cache_db($this->cache_cfg);

        // Import from SF
        $this->import_sf_into_cache();

        /* These are for reporting purposes */
        // List of children with possible changes
        $this->children_with_updates = array();
        // List of sibling groups with possibl chianges
        $this->groups_with_updates = array();
        // List of new children
        $this->children_added = array();
        // List of new sibling groups
        $this->groups_added = array();

        // Report generation - create a mailer
        $this->mail = new \PHPMailer();
        $this->mail->isSMTP();
    }

    /**
     * Pull from SF
     */
    function import_sf_into_cache()
    {
        $this->log->debug("Logging Into SF");
        // A new LastModifiedDate check
        $cr = new CacheResolve();
        $cr->setLastChecked();

        $this->sf_conn = new \SforcePartnerClient();
        $this->sf_conn->createConnection($this->sf_wsdl . ".partner.wsdl");
        $this->sf_conn->login(
            $this->sf_username,
            $this->sf_pass . $this->sf_token
        );
        $this->log->debug("Logged Into SF");

        $lastPulled = $this->em->createQuery(
            "SELECT r FROM CacheResolve r ORDER BY r.LastChecked DESC")->getResult();
        if (!$lastPulled)
        {
            $this->lm_iso = "";
        } else {
            $this->lm_iso = "LastModifiedDate > " . $lastPulled[0]->getLastChecked();
        }

        // Grab Contacts
        $where = "WHERE MailingState='" . CURRENT_STATE . "'";
        $this->import_from_sf("CacheContact", $where);
        // Grab Children
        $where = "WHERE Child_s_State__c='" . CURRENT_STATE_LONG . "'";
        $this->import_from_sf("CacheChild", $where);
        // Grab Groups
        $where = "WHERE State__c='" . CURRENT_STATE_LONG . "'";
        $this->import_from_sf("CacheGroup", $where);
        // Grab Attachments (More complex than above...so...)
        $this->import_sf_attachments();

        // Remove current pull date
        $this->log->debug("Flushing SF Data");
        foreach ($lastPulled as $lp)
        {
            $this->em->remove($lp);
        }
        // Persist the new date
        $this->em->persist($cr);
        $this->em->flush();

        // Find Largest Bulletin Number currently in use
        $bltns = $this->em->createQueryBuilder()
                          ->select("c.Adoption_Bulletin_Number__c")
                          ->from("CacheChild", "c")
                          ->getQuery()
                          ->getResult();
        // Start with 0
        $this->current_bltn = 0;
        // Go through them all replacing the current with a new highest
        foreach ($bltns as $bltn)
        {
            // Parse the bulletin down to an INT
            $possible_bltn = intval(ltrim(
                $bltn["Adoption_Bulletin_Number__c"], CURRENT_STATE_SHORT
            ));
            $this->current_bltn = max($this->current_bltn, $possible_bltn);
        }
        // Increment by one for next use
        $this->current_bltn += 1;
    }

    /**
     * Query all the things
     *
     * @param string $query Query to call on sf
     */
    function sfQueryAll($query)
    {
        $response = $this->sf_conn->query($query);
        $all_records = array();
        foreach ($response->records as $record)
            array_push($all_records, $record);
        $qr = new \QueryResult($response);
        while (!$qr->done)
        {
            $response = $this->sf_conn->queryMore($qr->queryLocator);
            foreach ($response->records as $record)
                array_push($all_records, $record);
            $qr = new \QueryResult($response);
        }
        return $all_records;
    }

    /**
     * Generic method to grab $type data from SF and import it into the cache
     *
     * @param string $type one of the four Cache types
     * @param string $where WHERE clause for the SF query
     */
    protected function import_from_sf($type, $where)
    {
        $this->log->debug("SFImport: " . $type . "s");

        // Simple type to SF Table name map
        $type_table_map = array(
            "CacheChild" => "Children__c",
            "CacheContact" => "Contact",
            "CacheGroup" => "Sibling_Group__c"
        );

        // Before SF is hit, let's figure out how
        // far back in time we need to search
        if ($this->lm_iso)
        {
            if ($where)
            {
                $where .= " AND $this->lm_iso";
            } else {
                $where .= " WHERE $this->lm_iso";
            }
        }

        // Build the field list needed for the data we track given $type
        $fields = array_keys($type::sf_map);
        array_push($fields, "Id");
        $select_fields = implode(", ", $fields);

        // Grab all the things from SF
        $query = "SELECT $select_fields FROM $type_table_map[$type] $where";
        $returned_records = $this->sfQueryAll($query);

        // Either add new data or replace current data.
        // SF store is the ultimate authority.
        foreach ($returned_records as $sf_record)
        {
            // Using DQL] query our objects for existing ones
            // http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
            $exist = $this->em->createQuery(
                "SELECT c FROM $type c WHERE c.sf_id = '$sf_record->Id'"
            )->getResult();

            $fields = get_object_vars($sf_record->fields);

            // Add or replace
            $new_cache_obj = $type::from_sf($sf_record->Id, $fields);
            if (!$exist)
            {
                // Bond Siblings to Groups via FK
                if ($type == "CacheGroup")
                {
                    for ($i=1; $i<9; $i++)
                    {
                        $c = "Child_" . $i ."_First_Name__c";
                        if ($fields[$c])
                        {
                            $qb = $this->em->createQueryBuilder();
                            $x = $qb
                                ->select('c')
                                ->from("CacheChild", "c")
                                ->where("c.sf_id = '$fields[$c]'")
                                ->getQuery()
                                ->getResult()[0];
                            if ($x)
                                $new_cache_obj->addSibling($x);
                        }
                    }
                }

                $this->em->persist($new_cache_obj);
            } else {
                foreach ($exist as $me)
                    $this->em->remove($me);
                $this->em->persist($new_cache_obj);
            }
        }
        $this->em->flush();
    }

    /**
     * Grab attachments with the given ParentIds
     *
     * @param string $q_start the beginning of the query
     * @param array $ids ParentIds of attachments
     */
    function import_sf_attachment_chunk($q_start, $ids)
    {
        $this->log->debug("SFImport: Attachments");
        // Query and List of IDs to search within
        $q = $q_start . implode(",", $ids);
        // Close the list then apply the lm_iso if exists
        $this->lm_iso ? $q .= ") AND $this->lm_iso" : $q .= ")";
        // Query it
        $attachments = $this->sfQueryAll($q);
        // Do things with the attachments
        foreach ($attachments as $attachment)
        {
            $a = \CacheAttachment::from_sf($attachment->Id, $attachment->fields);
            $exist = $this->em->createQuery(
                "SELECT a FROM Attachment a WHERE a.sf_id='$attachment->Id'"
            );
            if (!$exist)
            {
                $this->em->persist($a);
            } else {
                foreach ($exist as $me)
                    $this->em->remove($me);
                $this->em->persist($a);
            }
        }
        $this->em->flush();
    }

    /**
     * Gather Child and Group SF ids and pull attachments with
     * those as a ParentId
     */
    function import_sf_attachments()
    {
        $this->log->debug("SFImport: CacheAtachments");
        $cqb = $this->em->createQueryBuilder();
        $gqb = $this->em->createQueryBuilder();
        // SELECT c.sf_if FROM CacheChild c WHERE c.sf_id IS NOT NULL
        $cqb->select('c.sf_id')
           ->from("CacheChild", "c")
           ->where($cqb->expr()->isNotNull("c.sf_id"));
        // SELECT g.sf_if FROM CacheGroup g WHERE cgsf_id IS NOT NULL
        $gqb->select('g.sf_id')
           ->from("CacheGroup", "g")
           ->where($gqb->expr()->isNotNull("g.sf_id"));

        // Query cached results
        $cache_results = array_merge(
            $cqb->getQuery()->getResult(), $gqb->getQuery()->getResult()
        );
        $this->log->debug("Cache Result Count: " . count($cache_results));

        $att_q = "SELECT Id,Name,BodyLength,ContentType,ParentId " .
                 "FROM Attachment WHERE ParentId IN (";
        $start_length = strlen($att_q);
        $parent_ids = array();
        foreach ($cache_results as $result)
        {
            $new_id = "'" . $result["sf_id"] . "'";
            $id_list = array_merge($parent_ids + array($new_id));
            // 1 - a trailing closed paren - ')'
            $l = strlen($att_q . implode(",", $id_list) . ") AND $this->lm_iso");
            if ($l < (20000 - strlen($new_id) - strlen(") AND $this->lm_iso")))
            {
                array_push($parent_ids, $new_id);
            } else {
                $this->import_sf_attachment_chunk($att_q, $parent_ids);
                $parent_ids = array();
            }
        }
        if (count($parent_ids))
        {
            $this->import_sf_attachment_chunk($att_q, $parent_ids);
            $parent_ids = array();
        }
    }

    /**
     * Import Allchildren into our cache
     *
     * @param AllChildren $all_children Crawler parsed Child/SiblingGroups
     */
    function import_all_children(AllChildren $all_children)
    {
        $children = $all_children->get_children();
        $this->log->debug("Children Count: " . count($children));
        foreach ($children as $child)
        {
             $this->upsert_parsed_child($child);
        }
        $groups = $all_children->get_sibling_groups();
        foreach ($groups as $group)
        {
             $this->upsert_parsed_group($group);
        }
        $this->em->flush();
    }

    /**
     * Import parsed contact as a cache object
     *
     * @param array $contact array to convert
     */
    protected function upsert_parsed_contact($contact)
    {
        // Transform Data
        $c_dict = $contact->to_array();
        $name = parse_name($c_dict["Name"]);
        $addr = parse_address($c_dict["Address"]);
        $phone = $c_dict["PhoneNumber"];
        $region = $c_dict["Region"];
        $emails = $this->mail->parseAddresses($c_dict["Email"]);
        $email = $emails ? $emails[0]["address"] : "";
        $this->log->debug(
            "Upserting Contact: " . $name["FirstName"] . " " . $name["LastName"]
        );
        if (!$addr["MailingState"])
            $addr["MailingState"] = CURRENT_STATE_SHORT;

        // And create an array to import as a CacheContact
        $cache_array = array(
            "FirstName" => $name["FirstName"],
            "LastName" => $name["LastName"],
            "MailingPostalCode" => $addr["MailingPostalCode"],
            "MailingState" => $addr["MailingState"],
            "MailingCity" => $addr["MailingCity"],
            "MailingStreet" => $addr["MailingStreet"],
            "Email" => $email
        );

        // Find an existing Contact given some details if possibl
        $q_b = $this->em->createQueryBuilder();
        $fname = $name["FirstName"] ?
            $q_b->expr()->eq('c.FirstName', "'" . $name['FirstName'] . "'") :
            $q_b->expr()->isNull("c.FirstName");
        $lname = $name["LastName"] ?
            $q_b->expr()->eq('c.LastName', "'" . $name['LastName'] . "'") :
            $q_b->expr()->isNull("c.LastName");
        $mail = $email ?
            $q_b->expr()->eq('c.Email', "'" . $email . "'") :
            $q_b->expr()->isNull("c.Email");
        $zip = $addr["MailingPostalCode"] ?
            $q_b->expr()->eq('c.MailingPostalCode', "'" . $addr['MailingPostalCode'] . "'") :
            $q_b->expr()->isNull("c.MailingPostalCode");
        $state = $addr["MailingState"] ?
            $q_b->expr()->eq('c.MailingState', "'" . $addr['MailingState'] . "'") :
            $q_b->expr()->isNull("c.MailingState");

        $existing = $q_b
            ->select('c')
            ->from("CacheContact", "c")
            ->where($q_b->expr()->orX(
                $q_b->expr()->andX($fname, $lname, $mail),
                $q_b->expr()->andX($fname, $lname, $zip, $state)
            ))
            ->getQuery()
            ->getResult();

        if (count($existing) > 0)
        {
            $ret_contact = $existing[0];
        } else {
            $ret_contact = CacheContact::from_parsed($cache_array);
            $this->em->persist($ret_contact);
        }

        return $ret_contact;
    }

    /**
     * Import parsed child as a cache object
     *
     * @param Child $child array to convert
     * @param string $bltn given bulletin number if part of a group
     * @param array $others list of other siblings' names
     */
    protected function upsert_parsed_child($child, $bltn="", $others=array())
    {
        $name = $child->get_value("Name");
        $url = $child->get_value("PageURL");
        $this->log->debug("Upserting Child: $name - $url");

        // Pull Contact and create a CacheContact from it
        $child_contact = $child->get_value("CaseWorker");
        $cache_contact = $this->upsert_parsed_contact($child_contact);

        // Set $child["Siblings"] if part of a Sibling Group
        if ($others)
        {
            // Remove this Child's name from the list of Siblings
            unset($others[$child->get_value("Name")]);
            // Add the sibling names to $child["Siblings"]
            $child->set_value("Siblings", implode(", ", $others));
            $child->set_value("BulletinNumber", $bltn);
        } else {
            // Create a bulletin number for the child
            $new_bltn = CURRENT_STATE_SHORT . $this->current_bltn;
            $child->set_value("BulletinNumber", $new_bltn);
            // If this is an only child and *NOT* a sibling group member
            // Increase the bulletin number. otherwise the upsert sibling
            // group method will handle the bump
            $this->current_bltn += 1;
        }

        // Find an existing Child with a given TAREId
        $existing = $this->em->createQueryBuilder()
                    ->select('c')
                    ->from("CacheChild", "c")
                    ->where("c.Case_Number__c = " . $child->get_value("CaseNumber"))
                    ->getQuery()
                    ->getResult();

        // If one exists, add to list of children to check for updates
        if ($existing)
        {
            $c = $existing[0];
            array_push($this->children_with_updates, $c);
            $c->setContact($cache_contact);
        // If one doesn't exists, add to list of new children
        } else {
            // Parse the age into a birthdate
            $age_str = $child->get_value("Age");
            $bday = age_to_birthdate($age_str);
            $child->set_value("Age", $bday);

            $db_child = CacheChild::from_parsed($child->to_array());
            $db_child->setContact($cache_contact);
            $this->em->persist($db_child);
            $c = $db_child;
            array_push($this->children_added, $c);
        }

        // Attachment things
        $attachments = $child->get_value("Attachments");
        foreach ($attachments as $attachment)
        {
            $this->upsert_parsed_attachment($attachment, $c, null);
        }

        // Return the child object. Needed for SiblingGroups
        return $c;
    }

    /**
     * Import parsed group as a cache object
     *
     * @param SiblingGroup $group array to convert
     */
    protected function upsert_parsed_group($group)
    {
        $name = $group->get_value("Name");
        $url = $group->get_value("PageURL");
        $this->log->debug("Upserting Group: $name - $url");
        // Find an existing Child with a given TAREId
        $qb = $this->em->createQueryBuilder();
        $existing = $qb->select('g')
                        ->from("CacheGroup", "g")
                        ->where("g.Case_Number__c = " . $group->get_value("CaseNumber"))
                        ->getQuery()
                        ->getResult();

        // Pull Contact and create a CacheContact from it
        $group_contact = $group->get_value("CaseWorker");
        $cache_contact = $this->upsert_parsed_contact($group_contact);

        // Children in the Sibling Group
        $children_in_group = $group->get_value("RelatedChildren");

        // Set the sibling group's name
        $names = array();
        foreach ($children_in_group as $child)
        {
            array_push($names, $child->get_value("Name"));
        }
        $group->set_value("Name", implode(", ", $names));

        // If one exists, add to list of children to check for updates
        if ($existing)
        {
            $g = $existing[0];
            array_push($this->groups_with_updates, $g);
            $bltn = $g->getBulletinNumberC();
            $g->resetSiblings();
        // If one doesn't exists, add to list of new children
        } else {
            $db_group = CacheGroup::from_parsed($group->to_array());
            $this->em->persist($db_group);
            $g = $db_group;
            array_push($this->groups_added, $g);
            $bltn = CURRENT_STATE_SHORT . $this->current_bltn;
            $this->current_bltn += 1;
        }

        // Bulletin Number Addition
        $bulletin_addition = array("", "B", "C", "D", "E", "F", "G", "H");
        // We can only handle a max of 8 Siblings in a group....
        $siblings_count = count($children_in_group);
        $sibling_count = $siblings_count <= 8 ? $siblings_count : 8;
        // Cache them
        for ($i=0; $i<$sibling_count; $i++)
        {
            $sibling_bltn = $bltn . $bulletin_addition[$i];
            if ($i === 0)
                $group->set_value("BulletinNumber", $sibling_bltn);
            $sibling_cached = $this->upsert_parsed_child(
                $children_in_group[$i], $sibling_bltn, $names
            );
            $g->addSibling($sibling_cached);
        }

        // Play nice and increment the bullletin counter
        $this->current_bltn += 1;

        // Attachment things
        $attachments = $group->get_value("Attachments");
        foreach ($attachments as $attachment)
        {
            $this->upsert_parsed_attachment($attachment, null, $g);
        }
    }

    /**
     * Import parsed attachment as a cache object
     *
     * @param Attachment $attachment array to convert
     * @param CacheChild $child child owner of the attachment
     * @param CacheGroup $group group owner of the attechment
     */
    protected function upsert_parsed_attachment($attachment, $child=null, $group=null)
    {
        $this->log->debug("Upserting Attachment");
        // Convert to Array
        $arr = $attachment->to_array();
        $q_b = $this->em->createQueryBuilder();
        if ($child)
        {
            $arr["child"] = $child;
            $parent = "a.child";
        } else if ($group) {
            $arr["group"] = $group;
            $parent = "a.group";
        }

        // Look for existing
        $existing = $q_b
            ->select('a')
            ->from("CacheAttachment", "a")
            ->join($parent, "c")
            ->where(
                $q_b->expr()->eq("a.BodyLength", "'" . $arr["BodyLength"] . "'")
            )->getQuery()
            ->getResult();

        // If none exist, create
        if (count($existing) <= 0)
        {
            $c_at = CacheAttachment::from_parsed($arr);
            $this->em->persist($c_at);
        }
    }

    /**
     * Wrapper/API method to update Salesforce with locally cached data
     */
    function exit_handler()
    {
        $this->log->debug("Generating Report");

        // For new, email a report of newly added and update children/groups
        $html_start = "<table><thead><th>Name</th><th>Tare ID</th><th>URL</th></thead><tbody>";
        $html_end = "</tbody></table><br><br>";

        // Newly added Children
        $new_children = "<strong>" . count($this->children_added) . " Children to be Added</strong><br>" . $html_start;
        foreach ($this->children_added as $child)
        {
            $name = $child->getName();
            $tid = $child->getCaseNumberC();
            $url = $child->getLinkToChildSPageC();
            $row = "<tr><td>$name</td><td>$tid</td><td><a href='$url'>$url</a></td></tr>";
            $new_children .= $row;
        }
        $new_children .= $html_end;

        // Updates detected for Children
        $upd_children = "<strong>" . count($this->children_with_updates) . " Children to Check for Changes</strong><br>" . $html_start;
        foreach ($this->children_with_updates as $child)
        {
            $name = $child->getName();
            $tid = $child->getCaseNumberC();
            $url = $child->getLinkToChildSPageC();
            $row = "<tr><td>$name</td><td>$tid</td><td><a href='$url'>$url</a></td></tr>";
            $upd_children .= $row;
        }
        $upd_children .= $html_end;

        // Newly added Groups
        $new_groups = "<strong>" . count($this->groups_added) . " Sibling Groups to be Added</strong><br>" . $html_start;
        foreach ($this->groups_added as $group)
        {
            $name = $group->getName();
            $tid = $group->getCaseNumberC();
            $url = $group->getChildrenSWebpageC();
            $row = "<tr><td>$name</td><td>$tid</td><td><a href='$url'>$url</a></td></tr>";
            $new_groups .= $row;
        }
        $new_groups .= $html_end;

        // Updates detected for Groups
        $upd_groups = "<strong>" . count($this->groups_with_updates) . " Sibling Groups to Check for Changes</strong><br>" . $html_start;
        foreach ($this->groups_with_updates as $group)
        {
            $name = $group->getName();
            $tid = $group->getCaseNumberC();
            $url = $group->getChildrenSWebpageC();
            $row = "<tr><td>$name</td><td>$tid</td><td><a href='$url'>$url</a></td></tr>";
            $upd_groups .= $row;
        }
        $upd_groups .= $html_end;

        // HTML For report
        $full_html = $new_children . $upd_children . $new_groups . $upd_groups;

        // Mailer options - move to config later
        $this->mail->Host = $this->cfg["mail"]["host"];
        $this->mail->SMTPAuth = $this->cfg["mail"]["smtp_auth"];
        $this->mail->Username = $this->cfg["mail"]["username"];
        $this->mail->Password = $this->cfg["mail"]["password"];
        $this->mail->SMPTSecure = $this->cfg["mail"]["smtp_secure"];
        $this->mail->Port = $this->cfg["mail"]["port"];
        $this->mail->SMTPDebug = 2;

        $this->mail->setFrom($this->cfg["mail"]["send_as"]);
        $this->mail->addAddress($this->cfg["mail"]["send_to"]);
        $this->mail->isHTML(true);

        $this->mail->Subject = "TARE Log: " . date_format(new \DateTime("now"), \DateTime::ATOM);
        $this->mail->Body = $full_html;

        $this->log->debug("Sending Report");
        if (!$this->mail->send())
        {
            $this->log->debug("Message could not be sent.");
            $this->log->debug($this->mail->ErrorInfo);
        } else {
            $this->log->debug("Message sent.");
        }
    }
}
?>
