<?php

use MyShopKit\Shared\AutoPrefix;

return [
    'popup_general_settings_section' => [
        'id'           => 'popup_general_settings_section',
        'title'        => esc_html__('Popup General Settings', 'myshopkit'),
        'object_types' => [AutoPrefix::namePrefix('popup')],
        'fields'        => [
            'config' => [
                'name'    => esc_html__('Popup config', 'myshopkit'),
                'default' => '',
                'id'      => 'config',
                'type'    => 'textarea',
            ],
        ]
    ]
];
