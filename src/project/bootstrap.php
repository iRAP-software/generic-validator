<?php

/* 
 * This "bootstraps" the application/script to initialize necessary resources and autoloading
 * This will be called by both the webservice and any scripts that may run.
 */

session_start();

require_once(__DIR__ . '/defines.php');
require_once(__DIR__ . '/../settings/Settings.php');
require_once(__DIR__ . '/vendor/autoload.php');

$classDirs = array(
    __DIR__,
    __DIR__ . '/controllers',
    __DIR__ . '/models',
    __DIR__ . '/middleware',
    __DIR__ . '/libs',
    __DIR__ . '/scripts',
    __DIR__ . '/views',
);

$autoloader = new iRAP\Autoloader\Autoloader($classDirs);
