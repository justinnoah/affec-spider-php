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
 * CacheResolve
 */
class CacheResolve
{
    /**
     * @var \DateTime
     */
    private $LastChecked;


    /**
     * Set lastChecked
     *
     * @param \DateTime $lastChecked
     *
     * @return CacheResolve
     */
    public function setLastChecked($lastChecked = "")
    {
        $this->LastChecked = date_create()->format(DateTime::ATOM);

        return $this;
    }

    /**
     * Get lastChecked
     *
     * @return \DateTime
     */
    public function getLastChecked()
    {
        return $this->LastChecked;
    }
}
?>
