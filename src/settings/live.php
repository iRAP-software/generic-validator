<?php

/* 
 * Define all the settins in this file as either defines or static constants
 */

define("DB_HOST", "");
define("DB_DATABASE", "");
define("DB_USERNAME", "");
define("DB_PASSWORD", "");


# Specify the details of the RabbitMQ exchange we fetch jobs from.
define("RABBITMQ_HOST", "");
define("RABBITMQ_PORT", 5672);
define("RABBITMQ_USERNAME", "");
define("RABBITMQ_PASSWORD", "");
define("RABBITMQ_JOB_QUEUE_NAME", "");
define("RABBITMQ_LOGS_QUEUE_NAME", "");


# Specify SMTP details for sending emails
define('SMTP_HOST', "");
define('SMTP_USERNAME', "");
define('SMTP_PASSWORD', "");
define('SMTP_FROM_EMAIL', "");
define('SMTP_FROM_NAME', "");


# Specify the SSO broker details
define('SSO_BROKER_ID', 13);
define('SSO_BROKER_SECRET', "");
define('SSO_SITE_HOSTNAME', '');
define('iRAP\SsoClient\IRAP_SSO_URL', '');