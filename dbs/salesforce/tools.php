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
 * Parse a string as a first and last name
 *
 * @param string $name Name to parse into First, Last
 * @return array {"FirstName" => "", "LastName" => ""}
 */
function parse_name($name)
{
    // Object for returning two values
    $parsed_name = array(
        "FirstName" => "",
        "LastName" => "",
    );

    if (strpos($name, ",") !== false)
    {
        // Split on ,
        $name_split = explode(",", $name);
        // Handle FirstName
        $fname = trim($name_split[1]);
        if (strpos($fname, " "))
        {
            $parsed_name["FirstName"] = trim(explode(" ", $fname)[0]);
        } else {
            $parsed_name["FirstName"] = $fname;
        }
        // Grab the LastName
        $parsed_name["LastName"] = trim($name_split[0]);
    } else {
        // Parse name strings without the dividing ,
        $name_split = explode(" ", $name);
        $parsed_name["FirstName"] = $name_split[0];
        if (count($name_split) > 1)
        {
            $parsed_name["LastName"] = implode(
                " ", array_slice($name_split, 1)
            );
        }
    }

    return $parsed_name;
}


/**
 * Very basic US postal address parsing
 *
 * @param string $addr_str Address to parse
 * @return array {
 * "MailingPostalCode" => 00000,
 * "MailingState" => "TX",
 * "MailingCity" => "Austin",
 * "MailingStreet" => "1234 Everything Else"
 *}
 */
function parse_address($addr_str)
{

    $addr_arr = explode(" ", $addr_str);
    // Split off chunks
    $zip = array_pop($addr_arr);
    $state = array_pop($addr_arr);
    $city = array_pop($addr_arr);
    $street = implode(" ", $addr_arr);

    // CacheContact form Address
    $addr = array(
        "MailingPostalCode" => $zip,
        "MailingState" => $state,
        "MailingCity" => $city,
        "MailingStreet" => $street,
    );

    return $addr;

}
