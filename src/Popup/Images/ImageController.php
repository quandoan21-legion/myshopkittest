<?php

namespace MyShopKit\Popup\Images;

use Exception;
use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Shared\Upload\Base64Upload;
use MyShopKit\Shared\Upload\ImageURLUpload;
use MyShopKit\Shared\Upload\WPUpload;
use WP_Post;
use WP_Query;
use WP_REST_Request;

class ImageController
{
    private array $aParameters
        = [
            'post_mime_type',
            'posts_per_page',
            'orderby',
            'order',
            'id',
            'userID'
        ];

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRouters']);
    }

    public function registerRouters()
    {
        register_rest_route(MYSHOPKIT_REST, 'images',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => [$this, 'uploadImage'],
                    'permission_callback' => '__return_true'
                ],
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getImages'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(MYSHOPKIT_REST, 'me/images',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getMeImages'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
        register_rest_route(MYSHOPKIT_REST, 'images/(?P<id>(\d+))',
            [
                [
                    'methods'             => 'GET',
                    'callback'            => [$this, 'getImage'],
                    'permission_callback' => '__return_true'
                ]
            ]
        );
    }

    public function uploadImage(WP_REST_Request $oRequest)
    {
        return $this->updateImage($oRequest);
    }

    public function updateImage(WP_REST_Request $oRequest)
    {
        try {
            $source = $oRequest->get_param('source');
            $id = $oRequest->get_param('id');

            switch ($source) {
                case 'base64':
                    $oUpload = new Base64Upload();
                    $aFileInfo = $oRequest->get_params();
                    $isSingular = true;
                    break;
                case 'stock':
                case 'self_hosted': // duplicate image
                    $oUpload = new ImageURLUpload();
                    $aFileInfo = $oRequest->get_params();
                    $isSingular = true;
                    break;
                default:
                    $oUpload = new WPUpload();
                    $aFileInfo = $oRequest->get_file_params();
                    $isSingular = isset($aFileInfo['tmp']);
                    break;
            }

            unset($aFileInfo['id']);
            unset($aFileInfo['source']);

            if (empty($aFileInfo)) {
                return MessageFactory::factory('rest')
                    ->error(
                        esc_html__('The file is required', 'myshopkit'),
                        422
                    );
            }

            $oUpload->isSingleUpload($isSingular)
                ->setFile($aFileInfo);

            if ($type = $oRequest->get_param('type')) {
                $oUpload->setType($type);
            }

            if (!empty($source)) {
                $oUpload->setImageSource($source);
            }

            if ($id) {
                $oUpload->setUpdateAttachmentId($id);
            }

            $aResponse = $oUpload->processUpload();

            if ($aResponse['status'] == 'error') {
                return MessageFactory::factory('rest')->error(
                    $aResponse['msg'], $aResponse['code']
                );
            }

            if ($isSingular) {
                return MessageFactory::factory('rest')->success($aResponse['msg'], [
                    'item' => $aResponse['item']
                ]);
            }

            return MessageFactory::factory('rest')->success($aResponse['msg'], [
                'items' => $aResponse['items']
            ]);
        } catch (Exception $oException) {
            return MessageFactory::factory('rest')->error(
                $oException->getMessage(), $oException->getCode()
            );
        }
    }

    public function getImages(WP_REST_Request $oRequest)
    {
        $aArgs = $this->handleParamArgs($oRequest->get_params());
        $aResponse = $this->queryImages($aArgs);
        return MessageFactory::factory('rest')->success(esc_html__('There is featured image', 'myshopkit'),
            [
                'items'    => $aResponse['aImages'],
                'maxPages' => $aResponse['maxPages']
            ]
        );
    }

    public function handleParamArgs($aParams): array
    {
        $aArgs = [];
        foreach ($this->aParameters as $keyArgs) {
            if (array_key_exists($keyArgs, $aParams)) {
                switch ($keyArgs) {
                    case 'id':
                        $aArgs['p'] = $aParams['id'];
                        break;
                    case 'userID':
                        $aArgs['author__in'] = $aParams['userID'];
                        break;
                    default:
                        $aArgs[$keyArgs] = $aParams[$keyArgs];
                }
            }
        }
        return $aArgs;
    }

    public function queryImages($aArgs): array
    {
        $aImages = [];
        $maxPages = 0;
        $aArgs = wp_parse_args($aArgs, [
            'post_type'      => 'attachment',
            'post_mime_type' => 'image/jpeg,image/jpg,image/png',
            'post_status'    => 'inherit',
            'posts_per_page' => 20,
            'orderby'        => 'id',
            'order'          => 'ASC',
        ]);
        if (!empty($userID = get_current_user_id())) {
            $aArgs['author__in'] = $userID;
        }
        $oQuery = new WP_Query($aArgs);
        if ($oQuery->have_posts()) {
            while ($oQuery->have_posts()) {
                $oQuery->the_post();
                $aImages[] = $this->getImageInfo($oQuery->post);
            }
            $maxPages = $oQuery->max_num_pages;
        }
        wp_reset_postdata();
        return [
            'aImages'  => $aImages,
            'maxPages' => $maxPages
        ];
    }

    public function getImageInfo(WP_Post $oPost): array
    {
        $aImages = [];
        $aThumbnails = [];
        $aDataIMG = wp_get_attachment_metadata($oPost->ID);
        $baseDir = str_replace($oPost->post_title, '', $oPost->guid);
        foreach ($aDataIMG['sizes'] as $aItem) {
            $aThumbnails[] = [
                'id'     => $oPost->ID,
                'url'    => $baseDir . $aItem['file'],
                'width'  => $aItem['width'],
                'height' => $aItem['height'],
            ];
        }
        $aImages[] = [
            'id'         => $oPost->ID,
            'title'      => $oPost->post_title,
            'url'        => $oPost->guid,
            'width'      => $aDataIMG['width'],
            'height'     => $aDataIMG['height'],
            'thumbnails' => $aThumbnails
        ];
        return $aImages;
    }

    public function getMeImages(WP_REST_Request $oRequest)
    {
        if (empty(get_current_user_id())) {
            return MessageFactory::factory('rest')
                ->error(esc_html__('You must be logged in before performing this function', 'myshopkit'), 401);
        }
        $oRequest->set_param('userID', get_current_user_id());
        $aArgs = $this->handleParamArgs($oRequest->get_params());
        $aResponse = $this->queryImages($aArgs);
        return MessageFactory::factory('rest')->success(esc_html__('There is featured image', 'myshopkit'),
            [
                'items'    => $aResponse['aImages'],
                'maxPages' => $aResponse['maxPages']
            ]
        );
    }

    public function getImage(WP_REST_Request $oRequest)
    {
        $postID = (int)$oRequest->get_param('id');
        if (!wp_attachment_is('image', $postID)) {
            return MessageFactory::factory('rest')->error(esc_html__('Sorry,this image does not exists in database',
                'myshopkit'), 401);
        }
        return MessageFactory::factory('rest')->success(esc_html__('This image fetched successfully', 'myshopkit'),
            [
                'item' => $this->getImageInfo(get_post($postID))
            ]
        );
    }
}