<?php


namespace MyShopKit\Popup\Controllers;


use MyShopKit\Shared\AutoPrefix;

class PopupRegistration
{
    public function __construct()
    {
        add_action('cmb2_admin_init', [$this, 'registerBox']);
        add_action('init', [$this, 'registerPopup']);
    }

    public function registerBox()
    {
        $aConfig = include MYSHOPKIT_PATH . 'src/Popup/configs/PostMeta.php';
        foreach ($aConfig as $aSection) {
            $aFields = $aSection['fields'];
            unset($aSection['fields']);
            $oCmb = new_cmb2_box($aSection);
            foreach ($aFields as $aField) {
                $aField['id'] = AutoPrefix::namePrefix($aField['id']);
                $oCmb->add_field($aField);
            }
        }
    }

    public function registerPopup()
    {
        register_post_type(AutoPrefix::namePrefix('popup'), include MYSHOPKIT_PATH . 'src/Popup/configs/PostType.php');
    }
}