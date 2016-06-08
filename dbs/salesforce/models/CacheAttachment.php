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
 * CacheAttachment
 */
class CacheAttachment
{
    /**
     * @var array sf_map  Attachment -> CacheAttachment
     */
    const parsed_map = array(
        "BodyLength" => "setBodyLength",
        "Content" => "setContent",
        "ContentType" => "setContentType",
        "Name" => "setName",
        "Profile" => "setProfile",
        "child" => "setChild",
        "group" => "setGroup",
    );

    /**
     * @var array sf_map  SF -> CacheAttachment
     */
    const sf_map = array(
        "BodyLength" => "setBodyLength",
        "ContentType" => "setContentType",
        "Name" => "setName",
        "ParentId" => "setParentId",
    );

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $Name;

    /**
     * @var int
     */
    private $BodyLength;

    /**
     * @var blob
     */
    private $Content;

    /**
     * @var string
     */
    private $ContentType;

    /**
     * @var string
     */
    private $ParentId;

    /**
     * @var bool
     */
    private $Profile = false;

    /**
     * @var string
     */
    private $sf_id;

    /**
     * @var \CacheChild
     */
    private $child;

    /**
     * @var \CacheGroup
     */
    private $group;

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
     * Set name
     *
     * @param string $name
     *
     * @return CacheAttachment
     */
    public function setName($name)
    {
        $this->Name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * Set bodyLength
     *
     * @param int $bodyLength
     *
     * @return CacheAttachment
     */
    public function setBodyLength($bodyLength)
    {
        $this->BodyLength = $bodyLength;

        return $this;
    }

    /**
     * Get bodyLength
     *
     * @return int
     */
    public function getBodyLength()
    {
        return $this->BodyLength;
    }

    /**
     * Set content
     *
     * @param blob $content
     *
     * @return CacheAttachment
     */
    public function setContent($content)
    {
        $this->Content = $content;
        $this->setName(md5($content));

        return $this;
    }

    /**
     * Get content
     *
     * @return blob
     */
    public function getContent()
    {
        return $this->Content;
    }

    /**
     * Set contentType
     *
     * @param string $contentType
     *
     * @return CacheAttachment
     */
    public function setContentType($contentType)
    {
        $this->ContentType = $contentType;

        return $this;
    }

    /**
     * Get contentType
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->ContentType;
    }

    /**
     * Set parentId
     *
     * @param string $parentId
     *
     * @return CacheAttachment
     */
    public function setParentId($parentId)
    {
        $this->ParentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return string
     */
    public function getParentId()
    {
        return $this->ParentId;
    }

    /**
     * Set profile
     *
     * @param bool $profile
     *
     * @return CacheAttachment
     */
    public function setProfile($profile)
    {
        $this->Profile = $profile;

        return $this;
    }

    /**
     * Get profile
     *
     * @return bool
     */
    public function getProfile()
    {
        return $this->Profile;
    }

    /**
     * Set sfId
     *
     * @param string $sfId
     *
     * @return CacheAttachment
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
     * Set child
     *
     * @param \CacheChild $child
     *
     * @return CacheAttachment
     */
    public function setChild(\CacheChild $child = null)
    {
        $this->child = $child;

        return $this;
    }

    /**
     * Get child
     *
     * @return \CacheChild
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Set group
     *
     * @param \CacheGroup $group
     *
     * @return CacheAttachment
     */
    public function setGroup(\CacheGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \CacheGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Hydration via a Traversable data and map array
     *
     * @param Traversable $traversable to convert to CacheAttachment
     * @param array $map array to convert to CacheAttachment
     */
    private static function from_map($traversable, $map)
    {
        $t = new CacheAttachment();
        foreach ($traversable as $key => $value)
        {
            if (array_key_exists($key, $map) && $map[$key])
            {
                // Attachment property setter (e.g. setContent)
                $prop = $map[$key];
                // Set the property's value (e.g. $t->setContent($value))
                $t->$prop($value);
            }
        }
        return $t;
    }

    /**
     * Hydration from parsed data
     *
     * @param array $arr array to convert to CacheAttachment
     */
    public static function from_parsed($arr)
    {
        $attachment = self::from_map($arr, self::parsed_map);
        return $attachment;
    }

    /**
     * Hydration from salesforce data
     *
     * @param string $id sf id of Attachment
     * @param \SObject $sob array to convert to CacheAttachment
     */
    public static function from_sf($id, $sob)
    {
        $attachment = self::from_map($sob, self::sf_map);
        $attachment->setSfId($id);
        return $attachment;
    }

    /**
     * @ORM\PreUpdate
     */
    public function validateUpdate()
    {
        $this->validate();
    }

    /**
     * @ORM\PrePersist
     */
    public function validatePersist()
    {
        $this->validate();
    }

    /**
     * Simple validation for Attachments
     */
     function validate()
     {
         if (!$this->ParentId && !$this->Content)
             throw Exception("ParentId and Content cannot both be NULL");
     }
}
?>
