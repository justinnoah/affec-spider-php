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
 * CacheContact
 */
class CacheContact
{
    /**
     * @var array of Contact -> CacheContact map
     */
    const parsed_map = array(
        "FirstName" => "setFirstName",
        "LastName" => "setLastName",
        "MailingState" => "setMailingState",
        "MailingCity" => "setMailingCity",
        "MailingStreet" => "setMailingStreet",
        "MailingPostalCode" => "setMailingPostalCode",
        "Email" => "setEmail",
        "Phone" => "setPhone",
    );

    /**
     * @var array of SF Contact -> CacheContact map
     */
    const sf_map = array(
        "FirstName" => "setFirstName",
        "LastName" => "setLastName",
        "MailingState" => "setMailingState",
        "MailingCity" => "setMailingCity",
        "MailingStreet" => "setMailingStreet",
        "MailingPostalCode" => "setMailingPostalCode",
        "Email" => "setEmail",
        "Phone" => "setPhone",
    );

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $FirstName;

    /**
     * @var string
     */
    private $LastName;

    /**
     * @var string
     */
    private $Email;

    /**
     * @var string
     */
    private $MailingStreet;

    /**
     * @var string
     */
    private $MailingCity;

    /**
     * @var string
     */
    private $MailingState;

    /**
     * @var int
     */
    private $MailingPostalCode;

    /**
     * @var string
     */
    private $Phone;

    /**
     * @var string
     */
    private $sf_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groups;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Name helper method
     *
     * @param string $name Name to split to first and last
     */
    public function setName($name)
    {
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return CacheContact
     */
    public function setFirstName($firstName)
    {
        $this->FirstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return CacheContact
     */
    public function setLastName($lastName)
    {
        $this->LastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return CacheContact
     */
    public function setEmail($email)
    {
        $this->Email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->Email;
    }

    /**
     * Helper Function for setting an Address
     *
     * @param string $address Address string to validate
     */
    public function setAddress($address)
    {
    }

    /**
     * Set mailingStreet
     *
     * @param string $mailingStreet
     *
     * @return CacheContact
     */
    public function setMailingStreet($mailingStreet)
    {
        $this->MailingStreet = $mailingStreet;

        return $this;
    }

    /**
     * Get mailingStreet
     *
     * @return string
     */
    public function getMailingStreet()
    {
        return $this->MailingStreet;
    }

    /**
     * Set mailingCity
     *
     * @param string $mailingCity
     *
     * @return CacheContact
     */
    public function setMailingCity($mailingCity)
    {
        $this->MailingCity = $mailingCity;

        return $this;
    }

    /**
     * Get mailingCity
     *
     * @return string
     */
    public function getMailingCity()
    {
        return $this->MailingCity;
    }

    /**
     * Set mailingState
     *
     * @param string $mailingState
     *
     * @return CacheContact
     */
    public function setMailingState($mailingState)
    {
        $this->MailingState = $mailingState;

        return $this;
    }

    /**
     * Get mailingState
     *
     * @return string
     */
    public function getMailingState()
    {
        return $this->MailingState;
    }

    /**
     * Set mailingPostalCode
     *
     * @param int $mailingPostalCode
     *
     * @return CacheContact
     */
    public function setMailingPostalCode($mailingPostalCode)
    {
        $this->MailingPostalCode = $mailingPostalCode;

        return $this;
    }

    /**
     * Get mailingPostalCode
     *
     * @return int
     */
    public function getMailingPostalCode()
    {
        return $this->MailingPostalCode;
    }

    /**
     * Set setPhone
     *
     * @param string $phone
     *
     * @return CacheContact
     */
    public function setPhone($phone)
    {
        $this->Phone = $phone;

        return $this;
    }

    /**
     * Get Phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->Phone;
    }

    /**
     * Set sfId
     *
     * @param string $sfId
     *
     * @return CacheContact
     */
    public function setSfId($sfId)
    {
        $this->sf_id = $sfId;

        return $this;
    }

    /**
     * Get sfId
     *
     * @return string
     */
    public function getSfId()
    {
        return $this->sf_id;
    }

    /**
     * Add child
     *
     * @param \CacheChild $child
     *
     * @return CacheContact
     */
    public function addChild(\CacheChild $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \CacheChild $child
     */
    public function removeChild(\CacheChild $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add group
     *
     * @param \CacheGroup $group
     *
     * @return CacheContact
     */
    public function addGroup(\CacheGroup $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param \CacheGroup $group
     */
    public function removeGroup(\CacheGroup $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @ORM\PreUpdate
     */
    public function validateUpdate()
    {
        // Add your code here
    }

    /**
     * @ORM\PrePersist
     */
    public function validatePersist()
    {
        // Add your code here
    }

    /**
     * Hydration via a Traversable data and map array
     *
     * @param Traversable $traversable to convert to CacheContact
     * @param array $map array to convert to CacheContact
     */
    private static function from_map($traversable, $map)
    {
        $t = new CacheContact();
        foreach ($traversable as $key => $value)
        {
            if (array_key_exists($key, $map) && $map[$key])
            {
                $prop = $map[$key];
                $t->$prop($value);
            }
        }
        return $t;
    }

    /**
     * Hydration from parsed data
     *
     * @param array $arr array to convert to CachContact
     */
    public static function from_parsed($arr)
    {
        $contact = self::from_map($arr, self::parsed_map);
        return $contact;
    }

    /**
     * Hydration from salesforce data
     *
     * @param string $id sf id of Contact
     * @param \SObject $sob array to convert to CacheContact
     */
    public static function from_sf($id, $sob)
    {
        $contact = self::from_map($sob, self::sf_map);
        $contact->setSfId($id);
        return $contact;
    }
}
