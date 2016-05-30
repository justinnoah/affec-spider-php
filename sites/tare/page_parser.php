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
use \Crawler\DataTypes\Child;
use \Crawler\DataTypes\AllChildren;
use \Crawler\DataTypes\SiblingGroup;

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
     * @param string $url Child or Sibling Group url
     *
     * @return Child or SiblingGroup object
     */
    function parse($url)
    {
    }

    /**
     * Short Desc
     *
     * Parse Attachments for Child and Sibling Groups
     */
    function parse_attachments()
    {
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
