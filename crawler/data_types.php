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
    "Siblings",
)));

/**
 * Short Desc
 *
 * SiblingGroup specific keys
 */
define("SIBLING_GROUP_KEYS", serialize(array(
    "siblings",
)));

/**
 * Short Desc
 *
 * Commone keys for Child and SiblingGroup objects
 */
define("COMMON_KEYS", serialize(array(
    "Attachments",
    "Biography",
    "CaseNumber",
    "Contact",
    "ImportedFrom",
    "LegalStatus",
    "ListingNotesForFamily",
    "Name",
    "PageURL",
    "Region",
    "State",
)));

/**
 * Short Desc
 *
 * Valid Attachment Keys
 */
define("ATTACHMENT_KEYS", serialize(array(
    "Content",
    "ContentType",
    "Profile",
    "BodyLength",
)));

/**
 * Short Desc
 *
 * Valid CaseWorker Keys
 */
define("CASEWORKER_KEYS",serialize(array(
    "Address",
    "EmailAddress",
    "Name",
    "PhoneNumber",
    "Region",
)));

/**
 * Short Desc
 *
 * Simple Guarenties for Common Objects
 */
interface SpiderCommonInterface
{
    /**
     * Short Desc
     *
     * Set a Value for a given Key
     *
     * @param string $key array key
     * @param mixed $value array value
     */
    function set_value($key, $value);

    /**
     * Short Desc
     *
     * Get value from guarded array
     *
     * @param string $key to retrive value of
     * @return mixed value
     */
     function get_value($key);

    /**
     * Short Desc
     *
     * Return the guarded array
     *
     * @return array
     */
    function as_array();

    /**
     * Short Desc
     *
     * Import an array as an object
     *
     * @param array $data
     *
     * @return mixed
     */
     static function from_array(array $data);
}

/**
 * Short Desc
 *
 * Attachment representation
 */
class Attachment implements SpiderCommonInterface
{
    /**
     * Short Desc
     *
     * Initialize an Attachment object with an array
     */
    function __construct()
    {
        $this->allowed_keys = unserialize(ATTACHMENT_KEYS);
        $this->attachment = array();
    }

    /**
     * Short Desc
     *
     * Set a data point for an Attachment object
     *
     * @param string $slot is a valid data point for an attechment
     * @param mixed $data is the value to set for the slot
     */
    function set_value($slot, $data)
    {
        if (in_array($slot, $this->allowed_keys, true))
        {
            $this->attachment[$slot] = $data;
        } else {
            trigger_error(error_log("Cannot use $slot in an Attachment object."));
        }
    }

    /**
     * Short Desc
     *
     * Get value from guarded array
     *
     * @param string $key to retrive value of
     * @return mixed value
     */
     function get_value($key)
     {
         if (in_array($key, $this->allowed_keys))
         {
             if (in_array($key, array_keys($this->attachment)))
             {
                 return $this->attachment[$key];
             } else {
                 $this->attachment[$key] = "";
                 return $this->attachment[$key];
             }
         } else {
             trigger_error(error_log("$key is not a valid Attachment key"));
         }
     }

    /**
     * Short Desc
     *
     * Returns an Attachment as an unguarded array
     *
     * @return array
     */
    function as_array()
    {
        return $this->attachment;
    }

    /**
     * Short Desc
     *
     * Import an array as an Attachment
     *
     * @param array $data
     *
     * @return Attachment
     */
     static function from_array(array $data)
     {
         $att = new Attachment();
         foreach($data as $key => $value)
         {
             $att->set_value($key, $value);
         }
         return $att;
     }
}

/**
 * Short Desc
 *
 * CaseWorker representation
 */
class CaseWorker implements SpiderCommonInterface
{
    /**
     * Short Desc
     *
     * Initialize a CaseWorker object with an array
     */
    function __construct()
    {
        $this->allowed_keys = unserialize(CASEWORKER_KEYS);
        $this->caseworker = array();
    }

    /**
     * Short Desc
     *
     * Set a data point for a CaseWorker object
     *
     * @param string $slot is a valid data point for a caseworker
     * @param mixed $data is the value to set for the slot
     */
    function set_value($slot, $data)
    {
        if (in_array($slot, $this->allowed_keys, true))
        {
            $this->caseworker[$slot] = $data;
        } else {
            trigger_error(error_log("Cannot use $slot in a CaseWorker object."));
        }
    }

    /**
     * Short Desc
     *
     * Get value from guarded array
     *
     * @param string $key to retrive value of
     * @return mixed value
     */
     function get_value($key)
     {
         if (in_array($key, $this->allowed_keys))
         {
             if (in_array($key, array_keys($this->caseworker)))
             {
                 return $this->caseworker[$key];
             } else {
                 $this->caseworker[$key] = "";
                 return $this->caseworker[$key];
             }
         } else {
             trigger_error(error_log("$key is not a valid CaseWorker key"));
         }
     }

    /**
     * Short Desc
     *
     * Returns an Attachment as an unguarded array
     *
     * @return array
     */
    function as_array()
    {
        return $this->caseworker;
    }

    /**
     * Short Desc
     *
     * Import an array as an CaseWorker
     *
     * @param array $data
     *
     * @return CaseWorker
     */
     static function from_array(array $data)
     {
         $att = new CaseWorker();
         foreach($data as $key => $value)
         {
             $att->set_value($key, $value);
         }
         return $att;
     }
 }

/**
 * Short Desc
 *
 * Sibling Group representation
 */
class SiblingGroup implements SpiderCommonInterface
{
    /**
     * Short Desc
     *
     * Initialize a SiblingGroup object with an array
     */
    function __construct()
    {
        $this->allowed_keys = array_merge(
            unserialize(SIBLING_GROUP_KEYS), unserialize(COMMON_KEYS)
        );
        $this->sgroup = array();
    }

    /**
     * Short Desc
     *
     * Set a data point for a SiblngGroup object
     *
     * @param string $slot is a valid data point for a sibling group
     * @param mixed $data is the value to set for the slot
     */
    function set_value($slot, $data)
    {
        if (in_array($slot, $this->allowed_keys, true))
        {
            $this->sgroup[$slot] = $data;
        } else {
            trigger_error(error_log("Cannot use $slot in a SiblingGroup object."));
        }
    }

    /**
     * Short Desc
     *
     * Get value from guarded array
     *
     * @param string $key to retrive value of
     * @return mixed value
     */
     function get_value($key)
     {
         if (in_array($key, $this->allowed_keys))
         {
             if (in_array($key, array_keys($this->sgroup)))
             {
                 return $this->sgroup[$key];
             } else {
                 $this->sgroup[$key] = "";
                 return $this->sgroup[$key];
             }
         } else {
             trigger_error(error_log("$key is not a valid SiblingGroup key"));
         }
     }


    /**
     * Short Desc
     *
     * Returns SiblingGroup as an unguarded array
     *
     * @return array
     */
    function as_array()
    {
        return $this->sgroup;
    }

    /**
     * Short Desc
     *
     * Import an array as a SiblingGroup
     *
     * @param array $data
     *
     * @return SiblingGroup
     */
     static function from_array(array $data)
     {
         $sg = new SiblingGroup();
         foreach($data as $key => $value)
         {
             $sg->set_value($key, $value);
         }
         return $sg;
     }
}

/**
 * Short Desc
 *
 * Child representation
 */
class Child implements SpiderCommonInterface
{
    /**
     * Short Desc
     *
     * Initialize a Child object with a child array
     */
    function __construct()
    {
        $this->allowed_keys = array_merge(
            unserialize(CHILDREN_KEYS), unserialize(COMMON_KEYS)
        );
        $this->child = array();
    }

    /**
     * Short Desc
     *
     * Set a data point for a Child object
     *
     * @param string $slot is a valid data point for a Child
     * @param mixed $data is the value to set for the slot
     */
    function set_value($slot, $data)
    {
        if (in_array($slot, $this->allowed_keys, true))
        {
            $this->child[$slot] = $data;
        } else {
            trigger_error(error_log("Cannot use $slot in a Child object."));
        }
    }

    /**
     * Short Desc
     *
     * Get value from guarded array
     *
     * @param string $key to retrive value of
     * @return mixed value
     */
     function get_value($key)
     {
         if (in_array($key, $this->allowed_keys))
         {
             if (in_array($key, array_keys($this->child)))
             {
                 return $this->child[$key];
             } else {
                 $this->child[$key] = "";
                 return $this->child[$key];
             }
         } else {
             trigger_error(error_log("$key is not a valid Child key"));
         }
     }


    /**
     * Short Desc
     *
     * Returns Child as an unguarded array
     *
     * @return array
     */
    function as_array()
    {
        return $this->child;
    }

    /**
     * Short Desc
     *
     * Import an array as a Child
     *
     * @param array $data
     *
     * @return Child
     */
     static function from_array(array $data)
     {
         $c = new Child();
         foreach($data as $key => $value)
         {
             $c->set_value($key, $value);
         }
         return $c;
     }
}

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
                trigger_error(error_log("$child is not a Child object."));
            }
        }
        unset($child);
        $this->children = $children;

        // Guarantee each element of the Array is a SiblingGroup object.
        foreach ($sibling_groups as $group)
        {
            if (not ($group instanceof SiblingGroup))
            {
                trigger_error(error_log("$group is not a SiblingGroup object."));
            }
        }
        unset($group);
        $this->sibling_groups = $sibling_groups;
    }

    /**
     * Short Desc
     *
     * Retrieve the list of Child objects
     *
     * @return array containing Child objects
     */
     function get_children()
     {
         return $this->children;
     }

    /**
     * Short Desc
     *
     * Add a Child to the list of children
     *
     * @param Child $child Child being added
     */
    function add_child(Child $child)
    {
        if ($group instanceof Child)
        {
            array_push($this->children, $child);
        } else {
            trigger_error(error_log("Unable to add $group to groups list"));
        }
    }

    /**
     * Short Desc
     *
     * Retrieve the list of SiblingGroup objects
     *
     * @return array containing SiblingGroup objects
     */
     function get_sibling_groups()
     {
         return $this->sibling_groups;
     }

    /**
     * Short Desc
     *
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
            trigger_error(error_log("Unable to add $group to groups list"));
        }
    }

    /**
     * Short Desc
     *
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
      * Short Desc
      *
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
