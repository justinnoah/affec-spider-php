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

use Crawler\DataTypes\AllChildren;

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
     * @param \Monolog\Handler\StreamHandler $lHandler log handler/dispatcher
     */
    function __construct($config, $lHandler)
    {
        // Setup Logger
        $this->logHandler = $lHandler;
        $this->log = new \Monolog\Logger("TARE");
        $this->log->pushHandler($lHandler);

        // Log in to TARE
        $this->username = $config["username"];
        $this->password = $config["password"];
        if (!$this->login())
        {
            throw new \ErrorException("Failed to Login");
        }
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
        $this->log->info("Login URL: " . self::LOGINURL);
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
            $this->log->critical("Failed to Login.");
            $this->log->critical(curl_error($ch));
            return false;
        } else if (!curl_getinfo($ch)["redirect_url"] == self::LOGINREDIRECT)
        {
            $this->log->critical("Failed to Login. Check config.");
            return false;
        }
        curl_close($ch);
        return true;
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
            $this->log->error(curl_error($ch));
            return new AllChildren();
        }
        curl_close($ch);

        // Parse results for links
        $soup = \FluentDOM::QueryCss($result, "text/html");

        // Specifically Child links
        $child_links = array_map(
            function($x)
            {
                return self::BASEURL . $x["href"];
            },
            array_filter(
                $soup->find("a")->get(),
                function($node)
                {
                    $link = \FluentDOM($node)->attr("href");
                    if (preg_match("/.*Child\.aspx.*/", $link))
                    {
                        return true;
                    }
                    return false;
                })
        );

        // Specifically Sibling Group links
        $group_links = array_map(
            function($x)
            {
                return self::BASEURL . $x["href"];
            },
            array_filter(
                $soup->find("a")->get(),
                function($node)
                {
                    $link = FluentDOM($node)->attr("href");
                    if (preg_match("/.*Group\.aspx.*/", $link))
                    {
                        return true;
                    }
                    return false;
                })
        );

        $parsed_pages = new AllChildren();

        // Parse child pages for details to import
        foreach ($child_links as $clink)
        {
            $child_obj = new PageParser(
                self::BASEURL, $clink, "Child",
                $this->logHandler
            );
            $parsed_pages->add_child($child_obj->parse());
        }
        // Parse group pages for details to import
        foreach ($group_links as $glink)
        {
            $group_obj = new PageParser(
                self::BASEURL, $glink, "SiblingGroup",
                $this->logHandler
            );
            $parsed_pages->add_sibling_group($group_obj->parse());
        }

        return $parsed_pages;
    }
}
?>
