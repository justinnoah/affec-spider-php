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
use \Crawler\DataTypes\AllChildren;
use \Crawler\DataTypes\Attachment;
use \Crawler\DataTypes\CaseWorker;
use \Crawler\DataTypes\Child;
use \Crawler\DataTypes\SiblingGroup;
use \Crawler\Sites\Tare\Utils;

define("ALL_SIBLINGS_SELECTOR", "div#pageContent > div > div.galleryImage");
define("CHILD_CASE_NUMBER", "div#pageContent > div > div > div:nth-child(2) > span");

define("ATTACHMENT_SELECTORS", serialize(array(
    "profile_picture" => "div.galleryImage > a.imageLightbox",
    "other_pictures" => "div#contentGallery > div > div > a.imageLightbox"
)));

/**
 * Child and Sibling Page Parser for TARE
 */
class PageParser
{
    /**
     * Construct a PageParser initialized with $url
     *
     * @param string $base url of TARE site
     * @param string $url to initialize the PageParser
     * @param string $type of page to parse
     * @param \Monolog\Handler\StreamHandler $logHandler log handler/dispatcher
     * @param resource $session shared loggged in curl session
     */
    function __construct($base, $url, $type, $logHandler, $session)
    {
        // Scaffolding
        // Tare baseurl
        $this->base = $base;
        // path
        $this->url = $url;
        // Child or SiblingGroup
        $this->type = $type;
        // Curl Session passed in by TareSite
        $this->session = $session;

        // Object creation and logger setup
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

        // Yay, log things
        $this->log->info($url);
    }

    /**
     * Parse all data needed from a child or sibling page
     *
     * @return AllChildren containing a Child or SiblingGroup object
     */
    function parse()
    {
        // Using the curl session we have setup, grab the page data
        $opts = array(
            CURLOPT_URL => $this->url,
            CURLOPT_POST => false,
        );
        $page_data = Utils\curl_exec_opts($this->session, $opts);
        $this->soup = new \FluentDOM\Document();
        $this->soup->loadHTML($page_data);

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
     * Parse Attachments for Child and Sibling Groups
     */
    function parse_attachments()
    {
        $this->log->debug("Begin parsing attachments...");
        $selectors = unserialize(ATTACHMENT_SELECTORS);
        // $this->data->set_value("Attachments", array())
        $attachments = array();

        // Find Profile Picture node
        $node = $this->soup->querySelector($selectors["profile_picture"]);

        // Safely grab the picture url if possible
        $profile_picture_url = false;
        if (preg_match("/.*Media\.aspx\/GetPhoto.*/", $node["href"]))
        {
            $profile_picture_url = $this->base . $node["href"];
            $this->log->debug("Found profile picture url: $profile_picture_url");
        } else {
            $this->log->debug("href: " . $node["href"]);
        }

        // Download Picture data and create an attachment for it
        if ($profile_picture_url)
        {
            $opts = array(
                CURLOPT_URL => $profile_picture_url,
                CURLOPT_POST => false,
            );
            $profile_picture_data = Utils\curl_exec_opts($this->session, $opts);
            $profile_picture = new Attachment();
            $profile_picture->from_array(array(
                "Profile" => true,
                "Content" => $profile_picture_data,
                // This works for BodyLength, or curl_getinfo($ch)['download_content_length']
                "BodyLength" => count(unpack("C*", $profile_picture_data)),
            ));

            // Add the profile picture to the list of attachments
            array_push($attachments, $profile_picture);
        } else {
            $this->log->error("No profile picture found for $this->url\n");
        }

        // Now to grab all other pictures and create attachments out of them
        $nodes = $this->soup->querySelectorAll($selectors["other_pictures"]);
        $this->log->debug("Found " . $nodes->length . " more attachments.");
        foreach ($nodes as $node)
        {
            // Safely grab the picture url if possible
            if (preg_match("/.*Media\.aspx\/GetPhoto.*/", $node["href"]))
            {
                $picture_url = $this->base . $node["href"];

                // Download Picture data and create an attachment for it
                $opts = array(
                    CURLOPT_URL => $picture_url,
                    CURLOPT_POST => false,
                );
                $picture_data = Utils\curl_exec_opts($this->session, $opts);
                $picture = new Attachment();
                $picture->from_array(array(
                    "Profile" => true,
                    "Content" => $picture_data,
                    "BodyLength" => count(unpack("C*", $picture_data)),
                ));

                // Add new photo to attachment list
                array_push($attachments, $picture);
            }
        }

        // Update the Child or SiblingGroup object with the attachments
        $this->log->debug("Found " . count($attachments) . " total attachments");
        $this->data->set_value("Attachments", $attachments);
    }

    /**
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
        $child_cw_selector = "fieldset div";
        // I hate magic numbers, sadly TARE gives us no choice
        // If Caseworker parsing breaks in some way....LOOK HERE :XXX:
        $group_cw_selector = "div#pageContent div:nth-child(1) div:nth-child(6) div";

        if ($this->type == "Child")
        {
            // Child page specific casworker query
            $cw_selected = $this->soup->querySelectorAll($child_cw_selector);
        } else if ($this->type == "SiblingGroup") {
            // SiblingGroup page specific casworker query
            $cw_selected = $this->soup->querySelectorAll($group_cw_selector);
        }

        // CaseWorker data
        $caseworker_data = array();
        // cLength is for indexing purposes
        $cLength = $cw_selected->length;

        /**
         * Add data to $caseworker_data if necessary
         *
         * @param array $store caseworker_data
         * @param array $keys caseworker_tare_keys
         * @param \DOMElement $current current element
         * @param \DOMElement $next next element
         */
        $get_data = function(&$store, &$map, $current, $next)
        {
            // Grab the text of the nodes
            $current = trim($current->textContent);
            $next = trim($next->textContent);
            if (in_array($current, array_keys($map)))
            {
                // Apply new data
                $store[$map[$current]] = $next;
            }
        };

        // Iterate through all elements
        for ($i = 0; $i < $cLength-1; $i++)
        {
            // Check for divs inside the selected div
            // (TARE is weird, it happens "sometimes")
            // There is only ever one level of child divs at most
            $inner = $cw_selected->item($i)->find("div");
            if ($inner)
            {
                $iLength = $inner->length;
                for ($j=0; $j<$iLength-1; $j++)
                {
                    // Check for and possibly add new data
                    $get_data(
                        $caseworker_data, $caseworker_tare_map,
                        $inner->item($j), $inner->item($j + 1)
                    );
                }
            } else {
                // Check for and possibly add new data
                $get_data(
                    $caseworker_data, $caseworker_tare_map,
                    $cw_selected->item($i), $cw_selected->item($i + 1)
                );
            }
        }

        // Create CaseWorker common type and import the array
        $cw = new CaseWorker();
        $cw->from_array($caseworker_data);

        // Save the Caseworker data to the Child or SiblingGroup
        $this->data->set_value("CaseWorker", $cw);
    }

    /**
    * Parse child data from Child.aspx pages
    */
    function parse_child_info()
    {
    }

    /**
     * Grab children from sibling group and parse them
     */
    function parse_children_in_group()
    {
    }

    /**
     * Parse sibling group data from Group.aspx  pages
     */
    function parse_sibling_info()
    {
    }

    /**
     * Identify which type of page to parse
     */
    function parse_info()
    {
    }
}
?>
