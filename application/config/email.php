<?php defined('BASEPATH') or exit('No direct script access allowed');

// Add custom values by settings them to the $config array.
// Example: $config['smtp_host'] = 'smtp.gmail.com';
// @link https://codeigniter.com/user_guide/libraries/email.html

$config['useragent'] = 'VisitationLink';
$config['protocol'] = 'smtp'; // 'mail' or 'smtp'
$config['mailtype'] = 'html'; // 'html' or 'text'
$config['smtp_debug'] = 2;
$config['smtp_auth'] = true;
$config['smtp_host'] = 'email-smtp.us-east-1.amazonaws.com';
$config['smtp_user'] = 'AKIAYK5KWZXUKKSHT5F2';
$config['smtp_pass'] = 'BPpFTkCb9hcTc8LAn4ylnEXcbrsVZbO3xuQPFtNWI5vx';
$config['smtp_crypto'] = 'tls'; // 'ssl' or 'tls'
$config['smtp_port'] = 587;  // for authenticated TLS
