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
 * CacheGroup
 */
class CacheGroup
{
    /**
     * @var array sf_map  Attachment -> CacheGroup
     */
    const parsed_map = array(
        "Biography" => "setChildrenSBioC",
        "BulletinNumber" => "setBulletinNumberC",
        "CaseNumber" => "setCaseNumberC",
        "LegalStatus" => "setLegalStatus2C",
        "Name" => "setName",
        "PageURL" => "setChildrenSWebpageC",
        "RecruitmentStatus" => "setRecruitmentStatusC",
        "RecruitmentUpdate" => "setRecruitmentUpdateC",
        "Region" => "setDistrictC",
        "Siblings" => "addSibling",
        "State" => "setStateC",
    );
    /**
     * @var array sf_map  SF -> CacheGroup
     */
    const sf_map = array(
        "Bulletin_Number__c" => "setBulletinNumberC",
        "Case_Number__c" => "setCaseNumberC",
        "Caseworker__c" => "setCaseWorkerC",
        "Child_1_First_Name__c" => "setChild1FirstNameC",
        "Child_2_First_Name__c" => "setChild2FirstNameC",
        "Child_3_First_Name__c" => "setChild3FirstNameC",
        "Child_4_First_Name__c" => "setChild4FirstNameC",
        "Child_5_First_Name__c" => "setChild5FirstNameC",
        "Child_6_First_Name__c" => "setChild6FirstNameC",
        "Child_7_First_Name__c" => "setChild7FirstNameC",
        "Child_8_First_Name__c" => "setChild8FirstNameC",
        "Children_s_Bio__c" => "setChildrenSBioC",
        "Children_s_Webpage__c" => "setChildrenSWebpageC",
        "District__c" => "setDistrictC",
        "Legal_Status2__c" => "setLegalStatus2C",
        "Name" => "setName",
        "Recruitment_Status__c" => "setRecruitmentStatusC",
        "Recruitment_Update__c" => "setRecruitmentUpdateC",
        "State__c" => "setStateC",
    );

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $Bulletin_Number__c;

    /**
     * @var string
     */
    private $Caseworker__c;

    /**
     * @var string
     */
    private $Case_Number__c;

    /**
     * @var string
     */
    private $Children_s_Bio__c;

    /**
     * @var string
     */
    private $Child_1_First_Name__c;

    /**
     * @var string
     */
    private $Child_2_First_Name__c;

    /**
     * @var string
     */
    private $Child_3_First_Name__c;

    /**
     * @var string
     */
    private $Child_4_First_Name__c;

    /**
     * @var string
     */
    private $Child_5_First_Name__c;

    /**
     * @var string
     */
    private $Child_6_First_Name__c;

    /**
     * @var string
     */
    private $Child_7_First_Name__c;

    /**
     * @var string
     */
    private $Child_8_First_Name__c;

    /**
     * @var string
     */
    private $Children_s_Webpage__c;

    /**
     * @var string
     */
    private $District__c;

    /**
     * @var string
     */
    private $Name;

    /**
     * @var string
     */
    private $Legal_Status2__c;

    /**
     * @var string
     */
    private $Recruitment_Status__c;

    /**
     * @var string
     */
    private $Recruitment_Update__c;

    /**
     * @var string
     */
    private $State__c;

    /**
     * @var string
     */
    private $sf_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $attachments;

    /**
     * @var \CacheContact
     */
    private $contact;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $siblings;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attachments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set bulletinNumberC
     *
     * @param string $bulletinNumberC
     *
     * @return CacheGroup
     */
    public function setBulletinNumberC($bulletinNumberC)
    {
        $this->Bulletin_Number__c = $bulletinNumberC;

        return $this;
    }

    /**
     * Get bulletinNumberC
     *
     * @return string
     */
    public function getBulletinNumberC()
    {
        return $this->Bulletin_Number__c;
    }

    /**
     * Set caseworkerC
     *
     * @param string $caseworkerC
     *
     * @return CacheGroup
     */
    public function setCaseworkerC($caseworkerC)
    {
        $this->Caseworker__c = $caseworkerC;

        return $this;
    }

    /**
     * Get caseworkerC
     *
     * @return string
     */
    public function getCaseworkerC()
    {
        return $this->Caseworker__c;
    }

    /**
     * Set caseNumberC
     *
     * @param string $caseNumberC
     *
     * @return CacheGroup
     */
    public function setCaseNumberC($caseNumberC)
    {
        $this->Case_Number__c = $caseNumberC;

        return $this;
    }

    /**
     * Get caseNumberC
     *
     * @return string
     */
    public function getCaseNumberC()
    {
        return $this->Case_Number__c;
    }

    /**
     * Set childrenSBioC
     *
     * @param string $childrenSBioC
     *
     * @return CacheGroup
     */
    public function setChildrenSBioC($childrenSBioC)
    {
        $this->Children_s_Bio__c = $childrenSBioC;

        return $this;
    }

    /**
     * Get childrenSBioC
     *
     * @return string
     */
    public function getChildrenSBioC()
    {
        return $this->Children_s_Bio__c;
    }

    /**
     * Set child1FirstNameC
     *
     * @param string $child1FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild1FirstNameC($child1FirstNameC = null)
    {
        $this->Child_1_First_Name__c = $child1FirstNameC;

        return $this;
    }

    /**
     * Get child1FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild1FirstNameC()
    {
        return $this->Child_1_First_Name__c;
    }

    /**
     * Set child2FirstNameC
     *
     * @param string $child2FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild2FirstNameC($child2FirstNameC = null)
    {
        $this->Child_2_First_Name__c = $child2FirstNameC;

        return $this;
    }

    /**
     * Get child2FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild2FirstNameC()
    {
        return $this->Child_2_First_Name__c;
    }

    /**
     * Set child3FirstNameC
     *
     * @param string $child3FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild3FirstNameC($child3FirstNameC = null)
    {
        $this->Child_3_First_Name__c = $child3FirstNameC;

        return $this;
    }

    /**
     * Get child3FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild3FirstNameC()
    {
        return $this->Child_3_First_Name__c;
    }

    /**
     * Set child4FirstNameC
     *
     * @param string $child4FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild4FirstNameC($child4FirstNameC = null)
    {
        $this->Child_4_First_Name__c = $child4FirstNameC;

        return $this;
    }

    /**
     * Get child4FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild4FirstNameC()
    {
        return $this->Child_4_First_Name__c;
    }

    /**
     * Set child5FirstNameC
     *
     * @param string $child5FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild5FirstNameC($child5FirstNameC = null)
    {
        $this->Child_5_First_Name__c = $child5FirstNameC;

        return $this;
    }

    /**
     * Get child5FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild5FirstNameC()
    {
        return $this->Child_5_First_Name__c;
    }

    /**
     * Set child6FirstNameC
     *
     * @param string $child6FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild6FirstNameC($child6FirstNameC = null)
    {
        $this->Child_6_First_Name__c = $child6FirstNameC;

        return $this;
    }

    /**
     * Get child6FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild6FirstNameC()
    {
        return $this->Child_6_First_Name__c;
    }

    /**
     * Set child7FirstNameC
     *
     * @param string $child7FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild7FirstNameC($child7FirstNameC = null)
    {
        $this->Child_7_First_Name__c = $child7FirstNameC;

        return $this;
    }

    /**
     * Get child7FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild7FirstNameC()
    {
        return $this->Child_7_First_Name__c;
    }

    /**
     * Set child8FirstNameC
     *
     * @param string $child8FirstNameC
     *
     * @return CacheGroup
     */
    public function setChild8FirstNameC($child8FirstNameC = null)
    {
        $this->Child_8_First_Name__c = $child8FirstNameC;

        return $this;
    }

    /**
     * Get child8FirstNameC
     *
     * @return \CacheChild
     */
    public function getChild8FirstNameC()
    {
        return $this->Child_8_First_Name__c;
    }

    /**
     * Set childrenSWebpageC
     *
     * @param string $childrenSWebpageC
     *
     * @return CacheGroup
     */
    public function setChildrenSWebpageC($childrenSWebpageC)
    {
        $this->Children_s_Webpage__c = $childrenSWebpageC;

        return $this;
    }

    /**
     * Get childrenSWebpageC
     *
     * @return string
     */
    public function getChildrenSWebpageC()
    {
        return $this->Children_s_Webpage__c;
    }

    /**
     * Set districtC
     *
     * @param string $districtC
     *
     * @return CacheGroup
     */
    public function setDistrictC($districtC)
    {
        $this->District__c = $districtC;

        return $this;
    }

    /**
     * Get districtC
     *
     * @return string
     */
    public function getDistrictC()
    {
        return $this->District__c;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CacheGroup
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
     * Set legalStatus2C
     *
     * @param string $legalStatus2C
     *
     * @return CacheGroup
     */
    public function setLegalStatus2C($legalStatus2C)
    {
        $this->Legal_Status2__c = $legalStatus2C;

        return $this;
    }

    /**
     * Get legalStatus2C
     *
     * @return string
     */
    public function getLegalStatus2C()
    {
        return $this->Legal_Status2__c;
    }

    /**
     * Set recruitmentStatusC
     *
     * @param string $recruitmentStatusC
     *
     * @return CacheGroup
     */
    public function setRecruitmentStatusC($recruitmentStatusC)
    {
        $this->Recruitment_Status__c = $recruitmentStatusC;

        return $this;
    }

    /**
     * Get recruitmentStatusC
     *
     * @return string
     */
    public function getRecruitmentStatusC()
    {
        return $this->Recruitment_Status__c;
    }

    /**
     * Set recruitmentUpdateC
     *
     * @param string $recruitmentUpdateC
     *
     * @return CacheGroup
     */
    public function setRecruitmentUpdateC($recruitmentUpdateC)
    {
        $this->Recruitment_Update__c = $recruitmentUpdateC;

        return $this;
    }

    /**
     * Get recruitmentUpdateC
     *
     * @return string
     */
    public function getRecruitmentUpdateC()
    {
        return $this->Recruitment_Update__c;
    }

    /**
     * Set siblings
     *
     * @return CacheGroup
     */
    public function setSiblings()
    {
        return $this;
    }

    /**
     * Set stateC
     *
     * @param string $stateC
     *
     * @return CacheGroup
     */
    public function setStateC($stateC)
    {
        $this->State__c = $stateC;

        return $this;
    }

    /**
     * Get stateC
     *
     * @return string
     */
    public function getStateC()
    {
        return $this->State__c;
    }

    /**
     * Set sfId
     *
     * @param string $sfId
     *
     * @return CacheGroup
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
     * Add attachment
     *
     * @param \CacheAttachment $attachment
     *
     * @return CacheGroup
     */
    public function addAttachment(\CacheAttachment $attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * Remove attachment
     *
     * @param \CacheAttachment $attachment
     */
    public function removeAttachment(\CacheAttachment $attachment)
    {
        $this->attachments->removeElement($attachment);
    }

    /**
     * Get attachments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set contact
     *
     * @param \CacheContact $contact
     *
     * @return CacheGroup
     */
    public function setContact(\CacheContact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \CacheContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Add sibling
     *
     * @param \CacheChild $sibling
     *
     * @return CacheGroup
     */
    public function addSibling(\CacheChild $sibling)
    {
        $this->siblings[] = $sibling;

        return $this;
    }

    /**
     * Remove sibling
     *
     * @param \CacheChild $sibling
     */
    public function removeSibling(\CacheChild $sibling)
    {
        $this->siblings->removeElement($sibling);
    }

    /**
     * Reset Siblings
     */
    public function resetSiblings()
    {
        $this->siblings = array();
    }

    /**
     * Get siblings
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSiblings()
    {
        return $this->siblings;
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
     * @param Traversable $traversable to convert to CacheGroup
     * @param array $map array to convert to CacheGroup
     */
    private static function from_map($traversable, $map)
    {
        $t = new CacheGroup();
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
     * @param array $arr array to convert to CacheGroup
     */
    public static function from_parsed($arr)
    {
        $group = self::from_map($arr, self::parsed_map);
        return $group;
    }

    /**
     * Hydration from salesforce data
     *
     * @param string $id sf id of Group
     * @param \SObject $sob array to convert to CacheGroup
     */
    public static function from_sf($id, $sob)
    {
        $group = self::from_map($sob, self::sf_map);
        $group->setSfId($id);
        return $group;
    }
}
