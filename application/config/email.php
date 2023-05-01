<?php defined('BASEPATH') or exit('No direct script access allowed');

// Add custom values by settings them to the $config array.
// Example: $config['smtp_host'] = 'smtp.gmail.com';
// @link https://codeigniter.com/user_guide/libraries/email.html

$config['useragent'] = 'VisitationLink';
$config['protocol'] = Config::MAIL_PROTOCOL;
$config['mailtype'] = Config::MAIL_TYPE;
$config['smtp_debug'] = Config::SMTP_DEBUG;
$config['smtp_auth'] = Config::SMTP_AUTH;
$config['smtp_host'] = Config::SMTP_HOST;
$config['smtp_user'] = Config::SMTP_USER;
$config['smtp_pass'] = Config::SMTP_PASS;
$config['smtp_crypto'] = Config::SMTP_CRYPTO;
$config['smtp_port'] = Config::SMTP_PORT;
