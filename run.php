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

namespace Crawler;

// Third Party includes
include("vendor/autoload.php");

// Project includes
require("crawler/utils.php");
require("sites/tare/tare.php");
use \Crawler\Sites\Tare\TareSite;
require("dbs/salesforce/salesforce.php");
use \Crawler\Databases\Salesforce\Salesforce;
require("config.php");


/**
 * Start a Logger
 *
 * @param string $logfile path to log file
 */
function setup_log_handler($logfile="./spider.log")
{
    $handler = new \Monolog\Handler\StreamHandler(
        $logfile, \Monolog\Logger::DEBUG
    );
    return $handler;
}


/**
 * Search, parse, and import
 *
 * @param array $cfg Tare Config
 */
function search_and_import($cfg)
{
    // Logger
    $logHandler = setup_log_handler();
    $log = new \Monolog\Logger("Runner");
    // Database backend
    $sf = new Salesforce($cfg["databases"]["salesforce"], $logHandler);
    // Sites
    $tare = new TareSite($cfg["sites"]["tare"], $logHandler);
    $sites = array(
        $tare,
    );
    // Do the work
    foreach ($sites as $site)
    {
        // $dot = Utils\string_dot(range("a", "z"), range("a", "z"));
        $dot = Utils\string_dot(range("a", "a"), range("a", "a"));
        foreach ($dot as $name)
        {
            // Grab a chunk of the children
            $results = $site->search_by_name($name);
            // Add the small chunk of children to the database
            $sf->import_all_children($results);
        }
        $sf->exit_handler();
    }
}

/**
 * Put it all together
 */
function main()
{
    try {
        if (!($config = load_config()))
        {
            throw new \ErrorException("Unable to continue without a proper config.");
        }
        search_and_import($config);
    } catch (\ErrorException $e) {
        printf($e . "\n");
    }
}

// GOGOGOGOgogogogogogogogogogogogogo
main();
