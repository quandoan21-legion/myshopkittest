<?php
define( 'MAILCHIMP_URL', plugin_dir_url( __FILE__));
define( 'MAILCHIMP_PATH', plugin_dir_path( __FILE__));

require_once (MAILCHIMP_PATH . 'vendor/autoload.php');

new \MyShopKit\MailServices\MailChimp\Controllers\MailChimpController();
