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

class TareSite
{
    const BASEURL = "https://dfps.state.tx.us";
    const LOGINURL = self::BASEURL . "/Application/TARE/Account.aspx/Logon";
    const STATE = array("short" => "TX", "long" => "Texas");

    function login()
    {
        // Login Credentials
        $data = array(
            "UserName" => "test", "Password" => "test"
        );
        $opts = array(
            CURLOPT_URL => self::LOGINURL,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        );

        // Login
        $ch = curl_init();
        curl_setopt_array($ch, array_merge($opts, $this->curl_opts));
        curl_exec($ch);
        curl_close($ch);
    }

    function __construct()
    {
        $this->cookie_jar = tempnam("/tmp", "axv");
        $this->curl_opts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIESESSION => true,
            CURLOPT_COOKIEJAR => $this->cookie_jar,
        );
        $this->login();
    }

?>
