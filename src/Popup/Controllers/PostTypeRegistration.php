<?php

namespace MyShopKit\Popup\Controllers;

use MyShopKit\Shared\AutoPrefix;

class PostTypeRegistration
{
    public function __construct()
    {
        add_action('init', [$this, 'registerPostType']);
    }

    public function registerPostType()
    {
        register_post_type(AutoPrefix::namePrefix('popup'), include MYSHOPKIT_PATH.'src/Popup/configs/PostType.php');
    }
}
