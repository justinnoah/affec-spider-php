TARE Spider written in PHP
==========================

Rewrite of the python tare spider in PHP

Setup
-----

`composer install`

Configure
---------

`cp config.yaml.dist config.yaml`

Modify as necessary.

Run
---

`php run.php`

Troubleshooting
---------------

Accidentally delete config.yaml and config.yaml.dist?

`php gen_new_config.php`

This will regenerate config.yaml.dist
