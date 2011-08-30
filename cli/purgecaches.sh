#!/bin/bash

# This script purges all caches, we need to run this after an upgrade when we change things like local/plugins
# This script is suitable to be run via cron if required.
/usr/bin/php -r "posix_setuid('48'); define('CLI_SCRIPT', true); require_once('/var/www/vhosts/moodle.org/html/config.php'); require_once(\$CFG->libdir.'/adminlib.php'); purge_all_caches();"
