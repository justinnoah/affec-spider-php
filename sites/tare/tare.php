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

namespace Crawler\Sites\Tare;

use \Crawler\DataTypes;

require("sites/tare/utils.php");

require("page_parser.php");
use \Crawler\Sites\Tare\PageParse\PageParser;

/**
 * Long Desc
 *
 * The encapsulated nature of Tare wrapped in a class. This handles logging in,
 * searching, firing off page parsing jobs, and manages the entire session.
 *
 * Short Desc
 *
 * TARE Site management
 */
class TareSite
{
    /**
     * @var string BASEURL TARE base URL
     */
    const BASEURL = "https://www.dfps.state.tx.us";
    /**
     * @var string LOGINURL TARE login URL
     */
    const LOGINURL = self::BASEURL . "/Application/TARE/Account.aspx/Logon";
    /**
     * @var string SEARCHURL TARE search URL
     */
    const SEARCHURL = self::BASEURL ."/Application/TARE/Search.aspx/NonMatchingSearchResults";
    /**
     * @var array STATE State Identifier
     */
    const STATE = array("short" => "TX", "long" => "Texas");
    /**
     * @var string LGINREDIRECT Correct landing page upon successful login
     */
    const LOGINREDIRECT = "https://www.dfps.state.tx.us/Application/TARE/Search.aspx/Children";

    /**
     * Short Desc
     *
     * Login to TARE for session prep
     *
     * @param array $config user/pass for TARE
     */
    function __construct($config)
    {
        $this->username = $config["username"];
        $this->password = $config["password"];
        $this->login();
    }

    /**
     * Short Desc
     *
     * Login to Tare
     */
    function login()
    {
        // Login Credentials
        $data = array(
            "UserName" => $this->username,
            "Password" => $this->password,
        );
        printf("Login URL: %s\n", self::LOGINURL);
        $opts = array(
            CURLOPT_URL => self::LOGINURL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        );

        // Login
        $ch = curl_init();
        $result = Utils\curl_exec_opts($ch, $opts);
        if (!$result)
        {
            trigger_error((curl_error($ch)));
        } else if (!curl_getinfo($ch)["redirect_url"] == self::LOGINREDIRECT)
        {
            exit("Fail");
        }
        curl_close($ch);
    }

    /**
     * TARE decided to revamp their search page.
     *
     * @param string $name Name parameter for search
     *
     * @return DataTypes\AllChildren containing the parsed data of the
     * children and sibling groups found by the search
     */
    function search_by_name($name="aa")
    {
        $data = array(
            "Name" => $name,
            "TAREId" => "",
            "AA" => "false",
            "AN" => "false",
            "BK" => "false",
            "DC" => "false",
            "HP" => "false",
            "UD" => "false",
            "WT" => "false",
        );
        $opts = array(
            CURLOPT_URL => self::SEARCHURL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        );

        // Simple Search
        $ch = curl_init();
        $result = Utils\curl_exec_opts($ch, $opts);
        $info = curl_getinfo($ch);
        if (!$result)
        {
            trigger_error((curl_error($ch)));
        }
        curl_close($ch);

        // Parse results for links
        $soup = new \simple_html_dom($result, $lower=true);
        $links = $soup->find("a");

        // Specifically Child links
        $child_links = array_filter($links, function($link) {
            if (array_key_exists("href", $link->attr) &&
                preg_match("/.*Child\.aspx.*/", $link->attr["href"], $res))
            {
                return $res[0];
            }
            return false;
        });

        // Specifically Sibling Group links
        $group_links = array_filter($links, function($link) {
            if (array_key_exists("href", $link->attr) &&
                preg_match("/.*Group\.aspx.*/", $link->attr["href"], $res))
            {
                return $res[0];
            }
            return false;
        });

        // Parse child pages for details to import
        foreach ($child_links as $clink)
        {
            $child_url = self::BASEURL . $clink->attr["href"];
            printf("URL: %s\n", $child_url);
            $child_obj = new PageParser(
                self::BASEURL, $clink->attr["href"], "Child"
            );
            $child_obj->parse();
            break;
        }
        // Parse group pages for details to import
        foreach ($group_links as $glink)
        {
            $group_url = self::BASEURL . $glink->attr["href"];
            printf("URL: %s\n", $group_url);
            $group_obj = new PageParser(
                self::BASEURL, $glink->attr["href"], "SiblingGroup"
            );
            $group_obj->parse();
        }
    }
}

?>
