<?php

/* 
 * Script to be called by the docker container's startup script when the container is first started
 * up. This will run migrations that one time.
 */


require_once(__DIR__ . '/../bootstrap.php');

$connection = SiteSpecific::getDb();
$vida_migrations = new \iRAP\Migrations\MigrationManager(__DIR__ . '/../migrations', $connection);
$vida_migrations->migrate();

