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
     * @param bool $is_sibling slight parsing modifications for siblings
     */
    function __construct($base, $url, $type, $logHandler, $session, $is_sibling = false)
    {
        // Scaffolding
        // Tare baseurl
        $this->base = $base;
        // Path ONLY! no domain
        $this->url = $url;
        // Child or SiblingGroup
        $this->type = $type;
        // Curl Session passed in by TareSite
        $this->session = $session;
        // Sibling identifier for slight page parsing changes
        $this->is_sibling = $is_sibling;

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
        $this->data->set_value("LegalStatus", "Unknown");
        $this->data->set_value("PageURL", $this->base . $this->url);
        $this->data->set_value("RecruitmentStatus", "Pre-Recruitment");
        $this->data->set_value("State", "Texas TX");
    }

    /**
     * Parse all data needed from a child or sibling page
     *
     * @return AllChildren containing a Child or SiblingGroup object
     */
    function parse()
    {
        // Using the curl session we have setup, grab the page data
        $this->log->debug("URL: " . $this->base . $this->url);
        $opts = array(
            CURLOPT_URL => $this->base . $this->url,
            CURLOPT_POST => false,
        );
        $page_data = Utils\curl_exec_opts($this->session, $opts);
        $this->soup = new \FluentDOM\Document();
        $this->soup->loadHTML($page_data);
        $this->soup->normalize();

        // Using a try/catch paradigm, parse attachments,
        // caseworker info, and child or group data
        try
        {
            $this->parse_attachments();
        } catch (\Exception $e) {
            $this->log->error("Falied to parse Attachments for $this->url");
            $this->log->error($e);
        }
        try {
            if ($this->type == "Child")
            {
                $this->parse_child_info();
            } else {
                $this->parse_sibling_group_info();
            }
        } catch (\Exception $e) {
            $this->log->error("Falied to parse $this->type for $this->url");
            $this->log->error($e);
        }

        // Return the parsed object
        $this->log->debug("Finished Parsing: " . $this->data->get_value("Name"));
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
            $profile_picture_url = array_key_exists(
                "schema", parse_url($node["href"])) ?
                $node["url"] :
                $this->base . $node["href"];
            $this->log->debug("Found profile picture url: $profile_picture_url");
        } else {
            $this->log->debug("href: " . $node["href"]);
        }

        // Download Picture data and create an attachment for it
        if ($profile_picture_url !== false)
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
                    "Profile" => false,
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
     *
     * @param string $selector CSS selector for CaseWorker data
     */
    function parse_caseworker_info($selector)
    {
        $this->log->debug("Parsing CaseWorker Info");
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

        $cw_selected = $this->soup->querySelectorAll($selector);

        // CaseWorker data
        $caseworker_data = array();
        // cLength is for indexing purposes
        $cLength = $cw_selected->length;

        /**
         * Add data to $caseworker_data if necessary
         *
         * @param array $store caseworker_data
         * @param array $map caseworker_tare_map
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
        $this->log->debug("Finished CaseWorker:\n" . print_r($caseworker_data, true));
    }

    /**
    * Parse data for $this->data
    *
    * Child and SiblingGroup pages are very similar in many ways. The data
    * gathered for $this->data can thus be obtained almost identically.
    *
    * @param array $map Map of TARE filds to $this->data keys
    * @param string $info_selector CSS Selector for child data
    * @param string $cn_selector CSS Selector for the Case Number
    */
    function parse_this_data_info($map, $info_selector, $cn_selector)
    {
        /**
         * Add data to $this->data if necessary
         *
         * Method internal to parse_this_data_info as it's used only to cut
         * out about 6 LOC in various spots specific to the outer method
         *
         * @param array $map child our group tare key mapping array
         * @param \DOMElement $current current element
         * @param \DOMElement $next next element
         */
        $get_data = function(&$map, $current, $next)
        {
            // Grab the text of the nodes
            $current = trim($current->textContent);
            $next = trim($next->textContent);
            if (in_array($current, array_keys($map)))
            {
                // Apply new data
                $this->log->debug($map[$current] . ": " . $next);
                $this->data->set_value($map[$current], $next);
            }
        };

        // CSS selection for Child or Group page
        $selected = $this->soup->querySelectorAll($info_selector);
        // cLength is for indexing purposes
        $cLength = $selected->length;

        // $this->log->debug("Selected: " . print_r($selected, true));
        // $this->log->debug("Map: " . print_r($map, true));
        // Iterate through all elements
        for ($i = 0; $i < $cLength-1; $i++)
        {
            // Check for divs inside the selected div
            // (TARE is weird, it happens "sometimes")
            // There is only ever one level of child divs at most
            $inner = $selected->item($i)->find("div");
            if ($inner)
            {
                $iLength = $inner->length;
                for ($j=0; $j<$iLength-1; $j++)
                {
                    // Check for and possibly add new data
                    $get_data($map, $inner->item($j), $inner->item($j + 1));
                }
            } else {
                // Check for and possibly add new data
                $get_data(
                    $map,$selected->item($i), $selected->item($i + 1)
                );
            }
        }

        // Now for the Child/SiblingGroup Bio
        $headers = $this->soup->querySelectorAll("div[id='#Information'] div.groupHeader");
        $hLen = $headers->length;
        $bodies = $this->soup->querySelectorAll("div[id='#Information'] div.groupBody");
        $bLen = $bodies->length;

        if (!($bLen == $hLen))
            $this->log-warning(
                "Bio headers and bodies do not have the same count, results may look odd."
            );

        $bioText = "";
        // Get the largest length to loop with
        $maxLen = $hLen >= $bLen ? $hLen : $bLen;
        for($i=0; $i>$maxLen; $i++)
        {
            // If Item exists, get its text
            $hText = $headers->item($i) ? $headers->item($i)->textContent : "";
            $bText = $bodies->item($i) ? $bodies->item($i)->textContent : "";

            // Append text to the bio
            $bioText .= trim($hText) . "\n\n" . trim($bText) . "\n\n";
        }

        // Set the Bio text to our object
        $this->data->set_value("Biography", trim($bioText));

        // :XXX: This is why things break
        $case_number = trim($this->soup->querySelector($cn_selector));
        $this->log->debug("CASE NUMBER: " . $case_number);
        $this->data->set_value("CaseNumber", trim($case_number));
    }

    /**
     * Parse child data from the Child.aspx page, store in $this->data
     */
    function parse_child_info()
    {
        // TARE Label to Crawler name map
        $child_tare_map = array(
            "Name" => "Name",
            "Age" => "Age",
            "Race" => "Race",
            "Gender" => "Gender",
            "Ethnicity" => "Ethnicity",
            "Region" => "Region",
            "Primary Language" => "PrimaryLanguage",
        );

        // :XXX: CSS Selector for Child Data on the Child's page
        // DOMNodeList of the child data assuming the selector with magic
        // numbers still works. See line above if  Child data stops parsing
        $selector = "div[id*='#Information'] > div:nth-child(2) div";
        $name_selector = "div[id*='#Information'] > div:nth-child(2) > div:nth-child(2) > span";
        if ($this->is_sibling)
            $cn_selector = "div#pageContent > div > div:nth-child(3) > div:nth-child(2) > span";
        else
            $cn_selector = "div#pageContent > div > div:nth-child(2) > div:nth-child(2) > span";

        // Select and Parse!
        print_r("$this->url\n");
        $name = trim($this->soup->querySelector($name_selector)->textContent);
        $this->log->debug("Name: $name");
        $this->data->set_value("Name", $name);
        $this->parse_this_data_info($child_tare_map, $selector, $cn_selector);
        try
        {
            // CSS Selectors for CaseWorkers
            $cw_selector = "fieldset div";
            $this->parse_caseworker_info($cw_selector);
        } catch (\Exception $e) {
            $this->log->error("Falied to parse CaseWorker for $this->url");
            $this->log->error($e);
        }
    }

    /**
     * Parse sibling group data from Group.aspx page, store in $this->data
     */
    function parse_sibling_group_info()
    {
        // Parse the individual children in the sibling group
        // :XXX: THIS STUPID MAGIC NUMBER STUFF IS WHAT BREAKS!
        $siblings_list_selector = "div#pageContent > div:nth-child(1) > div:nth-child(5)  span + a";
        $child_links = $this->soup->querySelectorAll($siblings_list_selector);
        $aLen = $child_links->length;

        // Parse Siblings in the Group
        $siblings = array();
        foreach ($child_links as $link)
        {
            $url_arr = parse_url($link["href"]);
            if (array_key_exists("schema", $url_arr))
            {
                $c_url = $url_arr["path"];
            } else {
                $c_url = $link["href"];
            }
            // Prep a page to parse
            $page = new PageParser(
                $this->base, $c_url, "Child",
                $this->log->getHandlers()[0], $this->session, true
            );
            // Parse and push it onto the siblings stack
            array_push($siblings, $page->parse());
        }

        // Set the Sibling Group's name
        $names = array();
        foreach ($siblings as $sibling)
        {
            array_push($names, $sibling->get_value("Name"));
        }
        natsort($names);
        $sgroup_name = implode(", ", $names);
        $this->data->set_value("Name", $sgroup_name);

        // CN DIV is the focus point for the CaseWorker selector as well
        // Since both data are in the same div. The magic number is calc'd
        // by sibling cout + 1 blank div + 1 since case worker data starts
        // at that point
        $sib_count = count($siblings) + 1;
        $sib_spaced = $sib_count + 1;
        // Selectors for the Case Number and the CaseWorker
        $cn_selectors = array(
            // Layout 1
            "div#pageContent > div:nth-child(1) > " .
            "div:nth-child(5) > div:nth-child(" . $sib_spaced . ")",
            // Layout 2
            "div#pageContent > div:nth-child(1) > div:nth-child(5) > " .
            "div:nth-child(1) > div:nth-child(" . $sib_count . ")",
            // Layout 3
            "div#pageContent > div:nth-child(1) > div:nth-child(5) > " .
            "div:nth-child(2) > div:nth-child(" . $sib_count . ")",
            // Layout 4
            "div#pageContent > div:nth-child(1) > div:nth-child(5) > " .
            "div:nth-child(1) > div:nth-child(1) > div:nth-child(" . $sib_count . ")",
            // Layout 5
            "div#pageContent > div:nth-child(1) > div:nth-child(6)",
            // Layout 6
            "div#pageContent > div:nth-child(1) > div:nth-child(5) > div:nth-child(2)"
        );

        $cn_selector = "";
        $cw_selector = "";
        for ($i=0; $i<count($cn_selectors); $i++)
        {
            $tare_id_label_selector = $cn_selectors[$i] . " > div:nth-child(1)";
            $text = trim($this->soup->querySelector($tare_id_label_selector)->textContent);
            if (strtolower($text) == "tare id")
            {
                $cn_selector = $cn_selectors[$i] . " > div:nth-child(2)";
                $cw_selector = $cn_selectors[$i] . " div";
                break;
            }
        }

        $this->log->debug("Using:\n$cw_selector");
        $this->parse_this_data_info(array(), "s", $cn_selector);
        try
        {
            $this->parse_caseworker_info($cw_selector);
        } catch (\Exception $e) {
            $this->log->error("Falied to parse CaseWorker for $this->url");
            $this->log->error($e);
        }

        // SiblingGroup["RelatedChildren"] = $siblings
        $this->log->debug("Pushing " . count($siblings) . " siblings.");
        $this->data->set_value("RelatedChildren", $siblings);
    }
}
?>
