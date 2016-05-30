<?php

require("vendor/autoload.php");

use \Symfony\Component\Yaml\Yaml;

$cfg = array(
    "sites" => array(
        "tare" => array(
            "username" => "",
            "password" => "test",
        )
    ),
    "databases" => array(
        "salesforce" => array(
            "username" => "test",
            "password" => "test",
            "token" => "aosenthu",
            "sandbox" => true,
            "contact_account" => "aosnetuhasonetuh",
            "cache_db" => "derp.sql",
        )
    )
);

// Joy, magic numbers
// 3 as the 2nd param is nice and pretty printed yaml
$yaml = Yaml::dump($cfg, 3);

// Output to config
file_put_contents("./config.yaml.dist", $yaml);

?>
