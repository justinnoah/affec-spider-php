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
    const BASEURL = "https://www.dfps.state.tx.us";
    const LOGINURL = self::BASEURL . "/Application/TARE/Account.aspx/Logon";
    const SEARCHURL = self::BASEURL ."/Application/TARE/Search.aspx/NonMatchingSearchResults";
    const STATE = array("short" => "TX", "long" => "Texas");

    /**
     * Short Desc
     *
     * Login to Tare
     */
    function login()
    {
        // Login Credentials
        $data = array(
            "UserName" => "test", "Password" => "test"
        );
        printf("Login URL: %s\n", self::LOGINURL);
        $opts = array(
            CURLOPT_URL => self::LOGINURL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        );

        // Login
        $ch = curl_init();
        $result = $this->__curl_exec($ch, $opts);
        if (!$result)
        {
            trigger_error((curl_error($ch)));
        }
        curl_close($ch);
    }

    /**
     * Short Desc
     *
     * Hondle curl exec common opts
     *
     * Long Desc
     *
     * Simplify the process of common curl options. Instead of needing to
     * remember combining the common options, just do it by calling this exec.
     *
     * @param $ch cURL handle
     * @param $data array to concat with common cURL opts
     *
     * @return String of rutrened data from curl_exec
     */
    function __curl_exec($ch, $data)
    {
        curl_setopt_array($ch, $this->curl_opts + $data);
        return curl_exec($ch);
    }

    /**
     * Short Desc
     *
     * Login to TARE for session prep
     */
    function __construct()
    {
        $this->cookie_jar = tempnam("/tmp", "axv");
        $this->curl_opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => $this->cookie_jar,
        );
        $this->login();
    }

    /**
     * TARE decided to revamp their search page.
     *
     * @param search: Name parameter for search
     * @return: An AllChildren object containing the parsed data of the
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
        $result = $this->__curl_exec($ch, $opts);
        $info = curl_getinfo($ch);
        if (!$result)
        {
            trigger_error((curl_error($ch)));
        }
        curl_close($ch);
        $soup = new simple_html_dom($result, $lower=true);
        $links = $soup->find("a");
        $child_links = array_filter($links, function($link) {
            if (array_key_exists("href", $link->attr) && preg_match("/.*Child\.aspx.*/", $link->attr["href"], $res))
            {
                printf("%s", print_r(var_dump($res), true));
                return $res[0];
            }
            return false;
        });
        printf("ChildLinks:\n%s", print_r($child_links, true));
        printf("ChildLinkCont:\n%s", count($child_links));
    }
}

?>
