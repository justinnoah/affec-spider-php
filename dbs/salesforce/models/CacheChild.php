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
 * CacheChild
 */
class CacheChild
{
    /**
     * @var array Child -> CacheChild map
     */
    const parsed_map = array(
        "Age" => "setChildSBirthdateC",
        "AdoptionRecruitment" => "setRecruitmentStatusC",
        "Biography" => "setChildSBioC",
        "BulletinDate" => "",
        "BulletinNumber" => "",
        "CaseNumber" => "",
        "Ethnicity" => "setChildSNationalityC",
        "Gender" => "setChildSSexC",
        "ImportedFrom" => "",
        "LegalStatus" => "setLegalStatus2C",
        "ListingNotesForFamily" => "",
        "Name" => "setName",
        "PageURL" => "setLinkToChildSPageC",
        "PrimaryLanguage" => "setChildSPrimaryLanguageC",
        "Race" => "setChildSNationalityC",
        "Region" => "setDistrictC",
        "State" => "setStateC",
    );

    /**
     * @var array SF Child -> CacheChild map
     */
    const sf_map = array(
        "Adoption_Bulletin_Number__c" => "setAdoptionBulletinNumberC",
        "Child_s_Bio__c" => "setChildSBioC",
        "Child_s_Birthdate__c" => "setChildSBirthdateC",
        "Child_s_Nationality__c" => "setChildSNationalityC",
        "Child_s_Primary_Language__c" => "setChildSPrimaryLanguageC",
        "Child_s_Sex__c" => "setChildSSexC",
        "Child_s_Siblings__c" => "setChildSSiblingsC",
        "Child_s_State__c" => "setChildSStateC",
        "District__c" => "setDistrictC",
        "Legal_Status2__c" => "setLegalStatus2C",
        "Link_to_Child_s_Page__c" => "setLinkToChildSPageC",
        "Name" => "setName",
        "Recruitment_Status__c" => "setRecruitmentStatusC",
        "Recruitment_Update__c" => "setRecruitmentUpdateC",
    );

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $Adoption_Bulletin_Number__c;

    /**
     * @var string
     */
    private $Child_s_Bio__c;

    /**
     * @var \DateTime
     */
    private $Child_s_Birthdate__c;

    /**
     * @var string
     */
    private $Child_s_Nationality__c;

    /**
     * @var string
     */
    private $Child_s_Primary_Language__c;

    /**
     * @var string
     */
    private $Child_s_Sex__c;

    /**
     * @var string
     */
    private $Child_s_Siblings__c;

    /**
     * @var string
     */
    private $Child_s_State__c;

    /**
     * @var string
     */
    private $District__c;

    /**
     * @var string
     */
    private $Legal_Status2__c;

    /**
     * @var string
     */
    private $Link_to_Child_s_Page__c;

    /**
     * @var string
     */
    private $Name;

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
     * Set adoptionBulletinNumberC
     *
     * @param string $adoptionBulletinNumberC
     *
     * @return CacheChild
     */
    public function setAdoptionBulletinNumberC($adoptionBulletinNumberC)
    {
        $this->Adoption_Bulletin_Number__c = $adoptionBulletinNumberC;

        return $this;
    }

    /**
     * Get adoptionBulletinNumberC
     *
     * @return string
     */
    public function getAdoptionBulletinNumberC()
    {
        return $this->Adoption_Bulletin_Number__c;
    }

    /**
     * Set childSBioC
     *
     * @param string $childSBioC
     *
     * @return CacheChild
     */
    public function setChildSBioC($childSBioC)
    {
        $this->Child_s_Bio__c = $childSBioC;

        return $this;
    }

    /**
     * Get childSBioC
     *
     * @return string
     */
    public function getChildSBioC()
    {
        return $this->Child_s_Bio__c;
    }

    /**
     * Set childSBirthdateC
     *
     * @param \DateTime $childSBirthdateC
     *
     * @return CacheChild
     */
    public function setChildSBirthdateC($childSBirthdateC)
    {
        $this->Child_s_Birthdate__c = date_create($childSBirthdateC);

        return $this;
    }

    /**
     * Get childSBirthdateC
     *
     * @return \DateTime
     */
    public function getChildSBirthdateC()
    {
        return $this->Child_s_Birthdate__c;
    }

    /**
     * Set childSNationalityC
     *
     * @param string $childSNationalityC
     *
     * @return CacheChild
     */
    public function setChildSNationalityC($childSNationalityC)
    {
        $this->Child_s_Nationality__c = $childSNationalityC;

        return $this;
    }

    /**
     * Get childSNationalityC
     *
     * @return string
     */
    public function getChildSNationalityC()
    {
        return $this->Child_s_Nationality__c;
    }

    /**
     * Set childSPrimaryLanguageC
     *
     * @param string $childSPrimaryLanguageC
     *
     * @return CacheChild
     */
    public function setChildSPrimaryLanguageC($childSPrimaryLanguageC)
    {
        $this->Child_s_Primary_Language__c = $childSPrimaryLanguageC;

        return $this;
    }

    /**
     * Get childSPrimaryLanguageC
     *
     * @return string
     */
    public function getChildSPrimaryLanguageC()
    {
        return $this->Child_s_Primary_Language__c;
    }

    /**
     * Set childSSexC
     *
     * @param string $childSSexC
     *
     * @return CacheChild
     */
    public function setChildSSexC($childSSexC)
    {
        $this->Child_s_Sex__c = $childSSexC;

        return $this;
    }

    /**
     * Get childSSexC
     *
     * @return string
     */
    public function getChildSSexC()
    {
        return $this->Child_s_Sex__c;
    }

    /**
     * Set childSSiblingsC
     *
     * @param string $childSSiblingsC
     *
     * @return CacheChild
     */
    public function setChildSSiblingsC($childSSiblingsC)
    {
        $this->Child_s_Siblings__c = $childSSiblingsC;

        return $this;
    }

    /**
     * Get childSSiblingsC
     *
     * @return string
     */
    public function getChildSSiblingsC()
    {
        return $this->Child_s_Siblings__c;
    }

    /**
     * Set childSStateC
     *
     * @param string $childSStateC
     *
     * @return CacheChild
     */
    public function setChildSStateC($childSStateC)
    {
        $this->Child_s_State__c = $childSStateC;

        return $this;
    }

    /**
     * Get childSStateC
     *
     * @return string
     */
    public function getChildSStateC()
    {
        return $this->Child_s_State__c;
    }

    /**
     * Set districtC
     *
     * @param string $districtC
     *
     * @return CacheChild
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
     * Set legalStatus2C
     *
     * @param string $legalStatus2C
     *
     * @return CacheChild
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
     * Set linkToChildSPageC
     *
     * @param string $linkToChildSPageC
     *
     * @return CacheChild
     */
    public function setLinkToChildSPageC($linkToChildSPageC)
    {
        $this->Link_to_Child_s_Page__c = $linkToChildSPageC;

        return $this;
    }

    /**
     * Get linkToChildSPageC
     *
     * @return string
     */
    public function getLinkToChildSPageC()
    {
        return $this->Link_to_Child_s_Page__c;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CacheChild
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
     * Set recruitmentStatusC
     *
     * @param string $recruitmentStatusC
     *
     * @return CacheChild
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
     * @return CacheChild
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
     * Set sfId
     *
     * @param string $sfId
     *
     * @return CacheChild
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
     * @return CacheChild
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
     * @return CacheChild
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
     * Validoter Method
     */
    private function validate()
    {
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
     * Hydration via a Traversable data and map array
     *
     * @param Traversable $traversable to convert to CacheChild
     * @param array $map array to convert to CachChild
     */
    private static function from_map($traversable, $map)
    {
        $t = new CacheChild();
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
     * @param array $arr array to convert to CacheChild
     */
    public static function from_parsed($arr)
    {
        $child = self::from_map($arr, self::parsed_map);
        return $child;
    }

    /**
     * Hydration from salesforce data
     *
     * @param string $id sf id of Child
     * @param \SObject $sob array to convert to CacheChild
     */
    public static function from_sf($id, $sob)
    {
        $arr = get_object_vars($sob);
        $child = self::from_map($arr, self::sf_map);
        $child->setSfId($id);
        return $child;
    }
}
?>
