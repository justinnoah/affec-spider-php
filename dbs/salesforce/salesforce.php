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
         $this->cache_path = $cfg["cache_db"];
         $this->sf_username = $cfg["username"];
         $this->sf_pass = $cfg["password"];
         $this->sf_token = $cfg["token"];
         $this->sf_sandbox = $cfg["sandbox"];

         // Setup logging
        $this->log = new \Monolog\Logger("Salesforce");
        $this->log->pushHandler($lHandler);
        $this->log->info("SalesForce Activated and ready to go!");
     }
}

?>
