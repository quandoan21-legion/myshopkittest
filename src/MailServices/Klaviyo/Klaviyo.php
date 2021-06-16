<?php
define( 'KLAVIYO_URL', plugin_dir_url( __FILE__));
define( 'KLAVIYO_PATH', plugin_dir_path( __FILE__));

require_once (KLAVIYO_PATH . 'vendor/autoload.php');

new \MyShopKit\MailServices\Klaviyo\Controllers\KlaviyoController();
