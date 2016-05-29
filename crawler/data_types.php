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

namespace Crawler\DataTypes;

/**
 * Short Desc
 *
 * Child specific keys
 */
$children_keys = array(

);

/**
 * Short Desc
 *
 * SiblingGroup specific keys
 */
$sibling_group_keys = array(

);

/**
 * Short Desc
 *
 * Commone keys for Child and SiblingGroup objects
 */
$common_keys = array(

);

/**
 * Short Desc
 *
 * Object containing Children and SiblingGroup Objects
 */
class AllChildren
{
    /**
     * Short Desc
     *
     * Initialize an AllChildren object with children and sibling_group arrays
     */
    function __constructor()
    {
        $this->children = array();
        $this->sibling_groups = array();
    }
}

/**
 * Short Desc
 *
 * Child representation
 */
class Child
{
    /**
     * Short Desc
     *
     * Initialize a Child object with a child array
     */
    function __constructor()
    {
        $this->child = array();
    }
}

/**
 * Short Desc
 *
 * Sibling Group representation
 */
class SiblingGroup
{
    /**
     * Short Desc
     *
     * Initialize a SiblingGroup object with a sibling_group array
     */
    function __constructor()
    {
        $thi->sgroup = array();
    }
}

?>
