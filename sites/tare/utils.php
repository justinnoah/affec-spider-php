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

namespace Crawler\Sites\Tare\Utils;

define("COOKIEJAR", tempnam("/tmp", "axv"));

/**
 * Short Desc
 *
 * Hondle curl exec common opts
 *
 * Long Desc
 *
 * Simplify the process of common curl options. Instead of needing to
 * remember combining the common options, just do it by calling this exec.
 *
 * @param mixed $ch cURL handle
 * @param array $data array to concat with common cURL opts
 *
 * @return string of returned data from curl_exec
 */
function curl_exec_opts($ch, $data)
{
    $curl_opts = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => COOKIEJAR,
        CURLOPT_USERAGENT => "Mozilla/5.0 (X11; Linux x86_64; rv:49.0) Gecko/20100101 Firefox/49.0",
    );

    curl_setopt_array($ch, $curl_opts + $data);
    return curl_exec($ch);
};
?>
