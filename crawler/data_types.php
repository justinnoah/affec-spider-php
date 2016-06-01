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
 * Child specific keys
 */
define("CHILDREN_KEYS", serialize(array(
    "Age",
    "AdoptionRecruitment",
    "BulletinDate",
    "BulletinNumber",
    "Ethnicity",
    "Gender",
    "Name",
    "PrimaryLanguage",
    "Race",
)));

/**
 * SiblingGroup specific keys
 */
define("SIBLING_GROUP_KEYS", serialize(array(
    // An array of Child objects
    "RelatedChildren"
)));

/**
 * Commone keys for Child and SiblingGroup objects
 */
define("COMMON_KEYS", serialize(array(
    "Attachments",
    "Biography",
    "CaseNumber",
    "CaseWorker",
    "ImportedFrom",
    "LegalStatus",
    "ListingNotesForFamily",
    "Name",
    "PageURL",
    "Region",
    "State",
)));

/**
 * Valid Attachment Keys
 */
define("ATTACHMENT_KEYS", serialize(array(
    "Content",
    "ContentType",
    "Profile",
    "BodyLength",
)));

/**
 * Valid CaseWorker Keys
 */
define("CASEWORKER_KEYS",serialize(array(
    "Address",
    "Email",
    "Name",
    "PhoneNumber",
    "Region",
)));

/**
 * Simple Guarenties for Common Objects
 */
interface SpiderCommonInterface
{
    /**
     * Set a Value for a given Key
     *
     * @param string $key array key
     * @param mixed $value array value
     */
    function set_value($key, $value);

    /**
     * Get value from guarded array
     *
     * @param string $key to retrive value of
     * @return mixed value
     */
     function get_value($key);

    /**
     * Return the guarded array
     *
     * @return array
     */
    function to_array();

    /**
     * Import an array as an object
     *
     * @param array $data
     *
     * @return mixed
     */
     function from_array(array $data);
}

/**
 * Base class with common methods for the majority of the DataTypes
 */
class DType implements SpiderCommonInterface
{
    /**
     * Set a data point forAthe guarded array
     *
     * @param string $slot is a valid key for the guarded array
     * @param mixed $data is the value to set for the slot
     */
    function set_value($slot, $data)
    {
        if (in_array($slot, $this->allowed_keys, true))
        {
            $this->guarded_array[$slot] = $data;
        } else {
            $cls = get_class($this);
            error_log("Cannot use $slot in a(n) $cls object.");
        }
    }

    /**
     * Get value from guarded array
     *
     * @param string $key of guarded array
     * @return mixed value
     */
     function get_value($key)
     {
         if (in_array($key, $this->allowed_keys))
         {
             if (in_array($key, array_keys($this->guarded_array)))
             {
                 return $this->guarded_array[$key];
             } else {
                 $this->guarded_array[$key] = "";
                 return $this->guarded_array[$key];
             }
         } else {
             $cls = get_class($this);
             error_log("$key is not a valid $cls key");
         }
     }

    /**
     * Returns an Attachment as an unguarded array
     *
     * @return array
     */
    function to_array()
    {
        return $this->guarded_array;
    }

    /**
     * Import an array as an Attachment
     *
     * @param array $data
     *
     * @return Attachment
     */
     function from_array(array $data)
     {
         foreach($data as $key => $value)
         {
             $this->set_value($key, $value);
         }
     }

}

/**
 * Attachment representation
 */
class Attachment extends DType
{
    /**
     * Initialize an Attachment object with an array
     */
    function __construct()
    {
        $this->allowed_keys = unserialize(ATTACHMENT_KEYS);
        $this->guarded_array = array();
    }

}

/**
 * CaseWorker representation
 */
class CaseWorker extends DType
{
    /**
     * Initialize a CaseWorker object with an array
     */
    function __construct()
    {
        $this->allowed_keys = unserialize(CASEWORKER_KEYS);
        $this->guarded_array = array();
    }
}

/**
 * Sibling Group representation
 */
class SiblingGroup extends DType
{
    /**
     * Initialize a SiblingGroup object with an array
     */
    function __construct()
    {
        $this->allowed_keys = array_merge(
            unserialize(SIBLING_GROUP_KEYS), unserialize(COMMON_KEYS)
        );
        $this->guarded_array = array();
    }
}

/**
 * Child representation
 */
class Child extends DType
{
    /**
     * Initialize a Child object with a child array
     */
    function __construct()
    {
        $this->allowed_keys = array_merge(
            unserialize(CHILDREN_KEYS), unserialize(COMMON_KEYS)
        );
        $this->guarded_array = array();
    }
}

/**
 * Object containing Children and SiblingGroup Objects
 */
class AllChildren
{
    /**
     * Initialize an AllChildren object with Child and SiblingGroup arrays
     *
     * @param array $children list of Child objects to initilize with
     * @param array $sibling_groups list of SiblingGroup objects to initilize with
     */
    function __construct(array $children=array(), array $sibling_groups=array())
    {
        // Guarantee each element of the Array is a Child object.
        foreach ($children as $child)
        {
            if (not ($child instanceof Child))
            {
                error_log("$child is not a Child object.");
            }
        }
        unset($child);
        $this->children = $children;

        // Guarantee each element of the Array is a SiblingGroup object.
        foreach ($sibling_groups as $group)
        {
            if (not ($group instanceof SiblingGroup))
            {
                error_log("$group is not a SiblingGroup object.");
            }
        }
        unset($group);
        $this->sibling_groups = $sibling_groups;
    }

    /**
     * Retrieve the list of Child objects
     *
     * @return array containing Child objects
     */
    function get_children()
    {
         return $this->children;
    }

    /**
     * Add a Child to the list of children
     *
     * @param Child $child Child being added
     */
    function add_child(Child $child)
    {
        if ($child instanceof Child)
        {
            array_push($this->children, $child);
        } else {
            $type = gettype($child) || get_class($child);
            error_log("Unable to add $type to children list");
        }
    }

    /**
     * Retrieve the list of SiblingGroup objects
     *
     * @return array containing SiblingGroup objects
     */
     function get_sibling_groups()
     {
         return $this->sibling_groups;
     }

    /**
     * Add a SiblingGroup to the list of groups
     *
     * @param SiblingGroup $group Group being added
     */
    function add_sibling_group(SiblingGroup $group)
    {
        if ($group instanceof SiblingGroup)
        {
            array_push($this->sibling_groups, $group);
        } else {
            error_log("Unable to add $group to groups list");
        }
    }

    /**
     * Determine wither the AllChildren object is empty
     *
     * @return bool of whether or not the lists are empty
     */
    function is_empty()
    {
        // A simple count
        $count = count($this->children) + count($this->sibling_groups);
        if ($count == 0)
        {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Merge an AllChildren into this one
     *
     * @param AllChildren $other AllChildren to merge
     */
    function merge(AllChildren $other)
    {
        // Why bother adding if there aren't things to add?
        if (not ($other->is_empty()))
        {
            $this->children += $other->get_children();
            $this->sibling_groups += $other->get_sibling_groups();
        }
    }
}
?>
