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


use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

include("dbs/salesforce/models/CacheAttachment.php");
include("dbs/salesforce/models/CacheContact.php");
include("dbs/salesforce/models/CacheChild.php");
include("dbs/salesforce/models/CacheGroup.php");
include("dbs/salesforce/models/CacheResolve.php");


function init_cache_db($cfg)
{
    $db_models = Setup::createYAMLMetadataConfiguration(array(__DIR__."/models/metadata"), true);
    $em = EntityManager::create($cfg, $db_models);
    return $em;
}
?>
