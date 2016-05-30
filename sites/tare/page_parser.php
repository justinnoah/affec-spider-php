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

namespace Crawler\Sites\Tare\PageParse;

use Exception;

require("crawler/data_types.php");
use \Crawler\DataTypes\Child;
use \Crawler\DataTypes\AllChildren;
use \Crawler\DataTypes\SiblingGroup;
use \Crawler\Sites\Tare\Utils;
use \Crawler\DataTypes\Attachment;

define("ALL_SIBLINGS_SELECTOR", "div#pageContent > div > div.galleryImage");
define("CHILD_CASE_NUMBER", "div#pageContent > div > div > div:nth-child(2) > span");

define("ATTACHMENT_SELECTORS", serialize(array(
    "profile_picture" => "div##Information > div.galleryImage > a.imageLightbox",
    "other_pictures" => "div#contentGallery > a.imageLightbox"
)));

/**
 * Short Desc
 *
 * Child and Sibling Page Parser for TARE
 */
class PageParser
{
    /**
     * Short Desc
     *
     * Construct a PageParser initialized with $url
     *
     * @param string $base url of TARE site
     * @param string $url to initialize the PageParser
     * @param string $type of page to parse
     * @param \Monolog\Handler\StreamHandler $logHandler log handler/dispatcher
     */
    function __construct($base, $url, $type, $logHandler)
    {
        // Scaffolding
        $this->base = $base;
        $this->url = $url;
        $this->type = $type;

        if ($type == "Child")
        {
            $this->data = new Child();
            $this->log = new \Monolog\Logger("Child Page Parser");
        } else if ($type == "SiblingGroup") {
            $this->data = new SiblingGroup();
            $this->log = new \Monolog\Logger("SiblingGroup Page Parser");
        } else {
            $this->log = new \Monolog\Logger("!ERRER! Page Parser");
        }

        $this->log->pushHandler($logHandler);
        $this->log->info($url);
    }

    /**
     * Short Desc
     *
     * Parse all data needed from a child or sibling page
     *
     * @return AllChildren containing a Child or SiblingGroup object
     */
    function parse()
    {
        // Using the curl session we have setup, grab the page data
        $ch = curl_init();
        $opts = array(
            CURLOPT_URL => $this->url,
            CURLOPT_POST => false,
        );
        $page_data = Utils\curl_exec_opts($ch, $opts);
        $this->soup = \FluentDOM::QueryCss($page_data);
        curl_close($ch);

        // Using a try/catch paradigm, parse attachments,
        // caseworker info, and child or group data
        try
        {
            $this->parse_attachments();
        } catch (Exception $e) {
            $this->log->error("Falied to parse Attachments for $this->url");
            $this->log->error($e);
        }
        try
        {
            $this->parse_caseworker_info();
        } catch (Exception $e) {
            $this->log->error("Falied to parse CaseWorker for $this->url");
            $this->log->error($e);
        }

        // Return the parsed object
        return $this->data;
    }

    /**
     * Short Desc
     *
     * Parse Attachments for Child and Sibling Groups
     */
    function parse_attachments()
    {
        $this->log->debug("Begin parsing attachments...");
        $selectors = unserialize(ATTACHMENT_SELECTORS);
        // $this->data->set_value("Attachments", array())
        $attachments = array();

        // Find Profile Picture node
        $node = $this->soup->first($selectors["profile_picture"]);

        // Safely grab the picture url if possible
        $profile_picture_url = false;
        if (preg_match("/.*Media\.aspx\/GetPhoto.*/", $node["href"]))
        {
            $profile_picture_url = $node["href"];
        }

        // Download Picture data and create an attachment for it
        if ($profile_picture_url)
        {
            $ch = curl_init();
            $opts = array(
                CURLOPT_URL => $profile_picture_url,
                CURLOPT_POST => false,
            );
            $profile_picture_data = Utils\curl_exec_opts($ch, $opts);
            $profile_picture = Attachment::from_array(array(
                "Profile" => true,
                "Content" => $profile_picture_data,
                // This works for BodyLength, or curl_getinfo($ch)['download_content_length']
                "BodyLength" => count(unpack("C*", $profile_picture_data)),
            ));
            curl_close($ch);

            // Add the profile picture to the list of attachments
            array_push($attachments, $profile_picture);
        } else {
            $this->log->error("No profile picture found for $this->url\n");
        }

        // Now to grab all other pictures
        $nodes = $this->soup->find($selectors["other_pictures"]);
        $picture_url = "";
        foreach ($nodes as $node)
        {
            // Safely grab the picture url if possible
            if (array_key_exists("href", $node) &&
                preg_match("/.*Media\.aspx\/GetPhoto.*/", $node["href"]))
            {
                $picture_url = $this->base . $node["href"];

                // Download Picture data and create an attachment for it
                $ch = curl_init();
                $opts = array(
                    CURLOPT_URL => $picture_url,
                    CURLOPT_POST => false,
                );
                $picture_data = Utils\curl_exec_opts($ch, $opts);
                $picture = Attachment::from_array(array(
                    "Profile" => true,
                    "Content" => $picture_data,
                    "BodyLength" => count(unpack("C*", $picture_data)),
                ));
                curl_close($ch);

                // Add new photo to attachment list
                array_push($attachments, $picture);
            }
        }

        // Update the Child or SiblingGroup object with the attachments
        $this->data->set_value("Attachments", $attachments);
    }

    /**
     * Short Desc
     *
     * Porse Caseworker Data and add it to the Child/Sibling object
     */
    function parse_caseworker_info()
    {
        // TARE to crawler Key map
        $caseworker_tare_map = array(
            "Address" => "Address",
            "Email" => "Email",
            "Email Address" => "Email",
            "Name" => "Name",
            "Phone" => "PhoneNumber",
            "Phone Number" => "PhoneNumber",
            "Region" => "Region",
            "TARE Coordinator" => "Name",
        );

        // CSS Selectors for CaseWorkers

        $child_cw_selector = "fieldset > div";
        $group_cw_selector = "span[text='TARE Coordinator']";

        if ($this->type == "Child")
        {
            $selected = $this->soup->find($child_cw_selector);
        } else if ($this->type == "SiblingGroup") {
            $selected = $this->soup->find($group_cw_selector);
        }
    }

    /**
    * Short Desc
    *
    * Parse child data from Child.aspx pages
    */
    function parse_child_info()
    {
    }

    /**
     * Short Desc
     *
     * Grab children from sibling group and parse them
     */
    function parse_children_in_group()
    {
    }

    /**
     * Short Desc
     *
     * Parse sibling group data from Group.aspx  pages
     */
    function parse_sibling_info()
    {
    }

    /**
     * Short Desc
     *
     * Identify which type of page to parse
     */
    function parse_info()
    {
    }
}
?>
