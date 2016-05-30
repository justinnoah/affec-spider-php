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

define("ALL_SIBLINGS_SELECTOR", "div[id=pageContent] > div:0 > div[class=galleryImage]");
define("CHILD_CASE_NUMBER", "div[id=pageContent] > div:0 > div:0 > div:2 > span:0");

define("ATTACHMENT_SELECTORS", serialize(array(
    "profile_picture" => "
        div[id=#Information] > div[class=galleryImage] > a[class=imageLightbox]
    ",
    "other_pictures" => "div[id=contentGallery] > a[class=imageLightbox]"
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
     */
    function __construct($base, $url, $type)
    {
        $this->base = $base;
        $this->url = $base . $url;
        if ($type == "Child")
        {
            $this->data = new Child();
        } else if ($type == "SiblingGroup")
        {
            $this->data = new SiblingGroup();
        } else {
            trigger_error(error_log("'$type' is not understood."));
        }
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
        $this->soup = \str_get_html($page_data);
        curl_close($ch);

        // Using a try/catch paradigm, parse attachments,
        // caseworker info, and child or group data
        try
        {
            $this->parse_attachments();
        } catch (Exception $e) {
            trigger_error(
                error_log(
                    "Falide to parse Attachments for $this->url\n$e"
                )
            );
        }
    }

    /**
     * Short Desc
     *
     * Properly traverse CSS selector path
     *
     * Long Desc
     *
     * Have simple_html_dom css selction behave a more like python's
     * BeautifulSoup css selection. The ">" character denotes going down a
     * level in the CSS tree which simple_html_dom does not seem to understand
     *
     * @param \simple_html_dom $soup soup to traverse
     * @param string $css_path path to follow
     *
     * @return \simple_html_dom_node
     */
    function css_traverse(\simple_html_dom $soup, $css_path)
    {
        // Explode on ">" to go deeper in the css tree
        $css_split_path = explode(
            " > ", $css_path
        );

        // Start at the $soup level
        $node = $soup;

        // Move down the tree/path
        foreach ($css_split_path as $path)
        {
            // Use ":" as a delimiter for a child index
            $path_burst = explode(":", $path);

            // If the ":" delimiter was used, select appropriately
            if (count($path_burst) > 1)
            {
                $node = $node->find($path_burst[0], $path_burst[1]);
            // else do as normal
            } else {
                $node = $node->find($path)[0];
            }
        }

        // Return the final selected node
        return $node;
    }

    /**
     * Short Desc
     *
     * Parse Attachments for Child and Sibling Groups
     */
    function parse_attachments()
    {
        // $this->data->set_value("Attachments", array())
        $attachments = array();

        // Find Profile Picture node
        $node = $this->css_traverse(
            $this->soup, unserialize(ATTACHMENT_SELECTORS)["profile_picture"]
        );

        // Safely grab the picture url if possible
        $profile_picture_url = "";
        if (array_key_exists("href", $node->attr) &&
            preg_match("/.*Media\.aspx\/GetPhoto.*/", $node->attr["href"], $res))
        {
            $profile_picture_url = $this->base . $node->attr["href"];
        }

        // Download Picture data and create an attachment for it
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

        // Now to grab all other pictures
        $nodes = $this->soup->find(
            unserialize(ATTACHMENT_SELECTORS)["other_pictures"]
        );
        $picture_url = "";
        foreach ($nodes as $node)
        {
            // Safely grab the picture url if possible
            if (array_key_exists("href", $node->attr) &&
                preg_match("/.*Media\.aspx\/GetPhoto.*/", $node->attr["href"], $res))
            {
                $picture_url = $this->base . $node->attr["href"];
            }

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
