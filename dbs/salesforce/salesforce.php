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
         $this->cache_cfg = $cfg["cache_db"];

         // Setup logging
        $this->log = new \Monolog\Logger("Salesforce");
        $this->log->pushHandler($lHandler);
        $this->log->info("SalesForce Activated and ready to go!");

        // Cache init
        $this->em = init_cache_db($this->cache_cfg);
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
      * Import parsed child as a cache object
      *
      * @param \sObject $child array to convert
      */
     protected function import_sf_child($child)
     {
         $this->em->persist(CacheChild::from_sf($child));
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
      * Import parsed sf group as a cache object
      *
      * @param \sObject $group array to convert
      */
     protected function import_sf_group($group)
     {
         $this->em->persist(CacheGroup::from_sf($group));
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
      * Import parsed sf contact as a cache object
      *
      * @param array $contact array to convert
      */
     protected function import_sf_contact($contact)
     {
         $this->em->persist(CacheContact::from_sf($contact));
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
      * Import parsed attachment as a cache object
      *
      * @param array $attachment array to convert
      */
     protected function import_sf_attachment($attachment)
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
