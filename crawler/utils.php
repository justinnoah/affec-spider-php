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

namespace Crawler\Utils;

/**
 * Dot product of arrays
 *
 * @param array $outer characters in outer loop
 * @param array $inner characters in inner loop
 *
 * @return array of combined strings
 */
function string_dot($outer, $inner)
{
    // array of combined strings to return
    $strings = array();

    foreach ($outer as $fchar)
    {
        foreach ($inner as $schar)
        {
            $x = $fchar . $schar;
            array_push($strings, $x);
        }
    }

    // Return the array of strings
    return $strings;
}

?>
