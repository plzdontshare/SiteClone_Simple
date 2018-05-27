<?php

define('DEBUG', false);
define('ROOT_DIR', realpath(dirname(__FILE__)));
define('CONFIG_DIR', ROOT_DIR . '/conf');
define('CACHE_DIR', ROOT_DIR . '/cache');
define('DATABASE_DIR', ROOT_DIR . '/db');

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);
} else {
    error_reporting(0);
    ini_set('display_errors', false);
}

require ROOT_DIR . '/autoload.php';