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

            // Bond Siblings to Groups via FK
            if ($type == "CacheGroup")
            {
                for ($i=1; $i<9; $i++)
                {
                    $c = "Child_" . $i ."_First_Name__c";
                    if ($fields[$c])
                    {
                        $qb = $this->em->createQueryBuilder();
                        $fields[$c] = $qb
                            ->select('c')
                            ->from("CacheChild", "c")
                            ->where("c.sf_id = '$fields[$c]'")
                            ->getQuery()
                            ->getResult()[0];
                    }
                }
            }

            // Add or replace
            $new_cache_obj = $type::from_sf($sf_record->Id, $fields);
            if (!$exist)
            {
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
        $this->em->persist(CacheContact::from_parsed($contact));
    }

    /**
     * Import parsed child as a cache object
     *
     * @param mixed $child array to convert
     * @param string $bltn given bulletin number if part of a group
     * @param array $others list of other siblings' names
     */
    protected function upsert_parsed_child($child, $bltn="", $others=array())
    {
        // Set $child["Siblings"] if part of a Sibling Group
        if ($others)
        {
            // Remove this Child's name from the list of Siblings
            unset($others[$child["Name"]]);
            // Add the sibling names to $child["Siblings"]
            $child["Siblings"] = implode(", ", $others);
            $child["BulletinNumber"] = $bltn;
        } else {
            // Create a bulletin number for the child
            $new_bltn = CURRENT_STATE_SHORT . $this->current_bltn;
            $child->set_value("BulletinNumber") = $new_bltn;

            // If this is an only child and *NOT* a sibling group member
            // Increase the bulletin number. otherwise the upsert sibling
            // group method will handle the bump
            $this->current_bltn += 1;
        }

        // Find an existing Child with a given TAREId
        $qb = $this->em->createQueryBuilder();
        $existing = $qb->select('c')
                        ->from("CacheChild", "c")
                        ->where("c.Case_Number__c = " . $child["CaseNumber"])
                        ->getQuery()
                        ->getResult();
        // If one exists, add to list of children to check for updates
        if ($existing)
        {
            array_push($this->children_with_updates, $child);
            $c = $existing;
        // If one doesn't exists, add to list of new children
        } else {
            // Create the new BLTN number
            $db_child = CacheChild::from_parsed($child);
            array_push($this->children_added, $db_child);
            $c = $db_child;
        }
        // Return the child object. Needed for SiblingGroups
        return $c;
    }

    /**
     * Import parsed group as a cache object
     *
     * @param array $group array to convert
     */
    protected function upsert_parsed_group($group)
    {
        // Find an existing Child with a given TAREId
        $qb = $this->em->createQueryBuilder();
        $existing = $qb->select('g')
                        ->from("CacheGroup", "g")
                        ->where("g.Case_number__c = " . $group["CaseNumber"])
                        ->getQuery()
                        ->getResult();

        // Children in the Sibling Group
        $children_in_group = $group->get_value("RelatedChildren");

        // Sort Siblings by name
        uksort($childre_in_group, function($a, $b) {
            $n = array($a->get_value("Name"), $b->get_value("Name"));
            natsort($n);
            $first = $n[0];
            if ($a->get_value("Name") == $first)
                return -1;
            else
                return 1;
        });

        // Set the sibling group's name
        $names = array();
        foreach ($children_in_group as $child)
        {
            array_push($names, $child->get_value("Name"));
        }
        $group->set_value("Name", implode(", ", $names));

        // Cache the parsed children
        if ($existing)
        {
            $bltn = $existing->getBulletinNumberC();
        } else {
            $bltn = CURRENT_STATE_SHORT . $this->current_bltn;
            $this->current_bltn += 1;
        }
        // Bulletin Number Addition
        $bulletin_addition = array("", "B", "C", "D", "E", "F", "G", "H");
        // We can only handle a max of 8 Siblings in a group....
        $sibling_count = count($children_in_group) <= 8 ? count($children_ingroup) : 8;
        // Cache them
        for ($i=0; i<$sibling_count; $i++)
        {
            $sibling_bltn = $bltn . $bulletin_addition[$i];
            $sibling_cached = $this->usert_parsed_child(
                $children_in_group[$i], $sibling_bltn, $names
            );
            $sn = $i+1;
            $group["Sibling$sn"] = $sibling_cached;
        }
        // Set the groups bulletin number as the first sibling's
        $group["BulletinNumber"] = $group["Sibling1"]->getAdoptionBulletinNumberC();
        // Play nice and increment the bullletin counter
        $this->current_bltn += 1;

        // If one exists, add to list of children to check for updates
        if ($existing)
        {
            array_push($this->groups_with_updates, $group);
        // If one doesn't exists, add to list of new children
        } else {
            $db_group = CacheGroup::from_parsed($group);
            array_push($this->groups_added, $db_group);
        }
    }

    /**
     * Import parsed attachment as a cache object
     *
     * @param array $attachment array to convert
     */
    protected function upsert_parsed_attachment($attachment)
    {
        $this->em->persist(CacheAttachment::from_parsed($attachment));
    }

    /**
     * Wrapper/API method to update Salesforce with locally cached data
     */
    function exit_handler()
    {
    }
}

?>
