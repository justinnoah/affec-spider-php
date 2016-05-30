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

// If we are on PHP 7 or better, force type checking
if (version_compare(phpversion(), '7.0.0', '>='))
{
    declare(strict_types=1);
}

// STDLib includes
use ErrorException;

// Third Party includes
include("vendor/autoload.php");
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

// Project includes
require("crawler/utils.php");
require("sites/tare/tare.php");
use Crawler\Sites\Tare\TareSite;



/**
 * Short Desc
 *
 * Load config
 */
function load_config()
{
    $file  = file_get_contents("./config.yaml");
    try {
        $config = Yaml::parse($file);
    } catch (ParseException $e) {
        exit($e);
    }

    return $config;
}

/**
 * Short Desc
 *
 * Begin Search
 *
 * @param array $cfg Tare Config
 */
function search_tare($cfg)
{
    $tare = new TareSite($cfg);
    // $dot = Utils\string_dot(range("a", "z"), range("a", "z"));
    $dot = Utils\string_dot(range("a", "b"), range("a", "c"));
    foreach ($dot as $name)
    {
        $tare->search_by_name($name);
    }
}

try {
    if (!($config = load_config()))
    {
        throw new ErrorException("Unable to continue without a proper config.");
    }
    search_tare($config["sites"]["tare"]);
} catch (ErrorException $e) {
    printf("%s\n", $e);
}
