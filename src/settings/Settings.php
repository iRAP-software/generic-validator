<?php

/*
 * Define all the settins in this file as either defines or static constants
 */

define("ENVIRONMENT", "live");

require_once(__DIR__ . '/' . ENVIRONMENT . '.php');
