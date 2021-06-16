<?php
/**
 * Plugin Name: My Shop Kit
 * Plugin URI: https://myshopkit.app
 * Author: myshopkit
 * Author URI: https://myshopkit.app
 * Version: 1.0
 */

use MyShopKit\Insight\Clicks\Controllers\ClickStatisticAPIController;
use MyShopKit\Insight\Clicks\Database\ClickStatisticTbl;
use MyShopKit\Insight\Views\Controllers\ViewStatisticAPIController;
use MyShopKit\Insight\Views\Database\ViewStatisticTbl;
use MyShopKit\Popup\Controllers\PopupAPIController;
use MyShopKit\Popup\Controllers\PopupRegistration;
use MyShopKit\Popup\Controllers\PostTypeRegistration;
use MyShopKit\Popup\Images\ImageController;
use MyShopKit\MailServices;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

define('MYSHOPKIT_VERSION', '1.0');
define('MYSHOPKIT_HOOK_PREFIX', 'myshopkit/');
define('MYSHOPKIT_PREFIX', 'myshopkit_');
define('MYSHOPKIT_REST_VERSION', 'v1');
define('MYSHOPKIT_REST_NAMESPACE', 'myshopkit');
define('MYSHOPKIT_DS', '/');
define('MYSHOPKIT_REST', MYSHOPKIT_REST_NAMESPACE . MYSHOPKIT_DS . MYSHOPKIT_REST_VERSION);
define('MYSHOPKIT_URL', plugin_dir_url(__FILE__));
define('MYSHOPKIT_PATH', plugin_dir_path(__FILE__));

require_once (MYSHOPKIT_PATH . ('src/MailServices/MailServices.php'));
new PostTypeRegistration();
new ImageController();
//click Statistic
new ClickStatisticAPIController();
new ClickStatisticTbl();
//view Statistic
new ViewStatisticAPIController();
new ViewStatisticTbl();
//popup
new PopupAPIController();
new PopupRegistration();
new \MyShopKit\Shared\Validation\Test();
