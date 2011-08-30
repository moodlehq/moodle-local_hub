#!/bin/bash
/usr/bin/php -r "posix_setuid('48'); require_once('/var/www/vhosts/moodle.org/html/admin/cli/upgrade.php');" -- --non-interactive
