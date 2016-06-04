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
    }

    /**
     * Pull from SF
     */
    function import_sf_into_cache()
    {
        $this->log->debug("Logging Into SF");
        $this->sf_conn = new \SforcePartnerClient();
        $this->sf_conn->createConnection($this->sf_wsdl . ".partner.wsdl");
        $this->sf_conn->login(
            $this->sf_username,
            $this->sf_pass . $this->sf_token
        );
        $this->log->debug("Logged Into SF");

        // Grab Contacts
        $where = "WHERE MailingState='" . CURRENT_STATE . "'";
        $this->import_from_sf("CacheContact", $where);
        // Grab Children
        $where = "WHERE Child_s_State__c='" . CURRENT_STATE_LONG . "'";
        $this->import_from_sf("CacheChild", $where);
        // Grab Groups
        $where = "WHERE State__c='" . CURRENT_STATE_LONG . "'";
        $this->import_from_sf("CacheGroup", $where);
        // Grab Attachments

        $this->log->debug("Flushing SF Data");
        $this->em->flush();
    }

    /**
     * Query all the things
     *
     * @param string $query Query to call on sf
     */
    function sfQueryAll($query)
    {
        $this->log->debug($query);
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
            "CacheAttachment" => "Attachment",
            "CacheChild" => "Children__c",
            "CacheContact" => "Contact",
            "CacheGroup" => "Sibling_Group__c"
        );

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

            // Add or replace
            $new_cache_obj = $type::from_sf($sf_record->Id, $sf_record->fields);
            if (!$exist)
            {
                $this->em->persist($new_cache_obj);
            } else {
                $this->em->remove($exist);
                $this->em->persist($new_cache_obj);
            }
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
     * @param array $child array to convert
     */
    protected function upsert_parsed_child($child)
    {
        $this->em->persist(CacheChild::from_parsed($child));
    }

    /**
     * Import parsed group as a cache object
     *
     * @param array $group array to convert
     */
    protected function upsert_parsed_group($group)
    {
        $this->em->persist(CacheGroup::from_parsed($group));
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
