<?php

namespace MyShopKit\MailServices\MailChimp\Controllers;

use Exception;
use MailchimpMarketing\ApiClient;
use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\MailServices\Shared\TraitGenerateRestEndpoint;
use MyShopKit\MailServices\Shared\TraitMailServicesConfiguration;
use MyShopKit\MailServices\Shared\TraitMailServicesValidation;
use WP_REST_Request;
use WP_REST_Response;

class MailChimpController {
	use TraitGenerateRestEndpoint;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;
	
	public static ?ApiClient $oClient = NULL;
	public string $key = 'mailchimp_info';
	public string $mailService = 'mailchimp';
	
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'registerRouters' ] );
		add_action( MYSHOPKIT_HOOK_PREFIX . 'after/subscribed', [ $this, 'subscribeEmailDirectly' ] );
	}
	
	public function registerRouters() {
		register_rest_route(
			MYSHOPKIT_REST,
			$this->getSetUpMailServicesEndPoint( $this->mailService ),
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'saveApiKeyToMailChimp' ],
					'permission_callback' => '__return_true',
				],
			]
		);
		register_rest_route(
			MYSHOPKIT_REST,
			$this->getSaveListIdEndPoint( $this->mailService ),
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'saveListIdToMailChimp' ],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'getAllLists' ],
					'permission_callback' => '__return_true',
				],
			]
		);
		register_rest_route(
			MYSHOPKIT_REST,
			$this->getSaveEmailEndPoint( $this->mailService ),
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'subscribeEmailManually' ],
					'permission_callback' => '__return_true',
				],
			]
		);
	}
	
	/**
	 * Connect to MailChimpServer
	 *
	 * @param string $apiKey
	 *
	 * @return ApiClient
	 */
	public function connectMailChimp( string $apiKey ): ApiClient {
		$server = $this->getServer( $apiKey );
		if ( self::$oClient == NULL ) {
			$oClient  = new ApiClient();
			$oConnect = $oClient->setConfig( [
				'apiKey' => $apiKey,
				'server' => $server,
			] );
			return self::$oClient = $oConnect;
		}
		return self::$oClient;
	}
	
	/**
	 * get server from api key
	 *
	 * @param $apiKey
	 *
	 * @return string
	 */
	public function getServer( $apiKey ): string {
		$aServerInfo = explode( '-', $apiKey );
		return $aServerInfo[1] ?? '';
	}
	
	/**
	 * check api key is valid
	 *
	 * @param string $apiKey
	 *
	 * @return array
	 */
	public function checkIsUserApiKeyValid( string $apiKey ): array {
		try {
			$this->connectMailChimp( $apiKey )->ping->get();
			return MessageFactory::factory()->success( esc_html__( 'OK', 'myshopkit' ) );
		} catch ( Exception $oException ) {
			return MessageFactory::factory()->error( esc_html__( 'Your api key is Invalid', 'myshopkit' ),
				$oException->getCode() );
		}
	}
	
	/**
	 * check user listID is valid
	 *
	 * @param string $apiKey
	 * @param string $listID
	 *
	 * @return array
	 */
	public function checkIsUserListIdValid( string $apiKey, string $listID ): array {
		try {
			$this->connectMailChimp( $apiKey )->lists->getList( $listID );
			return MessageFactory::factory()->success( esc_html( 'OK' ) );
		} catch ( Exception $oException ) {
			return MessageFactory::factory()->error( esc_html__( 'The key is incorrect, please check again.',
				'myshopkit' ),
				400 );
		}
	}
	
	/**
	 * Checking the configurations is valid to update user api key
	 *
	 * @param WP_REST_Request $oRequest
	 *
	 * @return WP_REST_Response
	 */
	public function saveApiKeyToMailChimp( WP_REST_Request $oRequest ): WP_REST_Response {
		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			$apiKey = $oRequest->get_param( 'apiKey' );
			if ( ! empty( $apiKey ) ) {
				$aResponseCheckApiKey = $this->checkIsUserApiKeyValid( $apiKey );
				if ( $aResponseCheckApiKey['status'] == 'success' ) {
					$this->updateMailServiceConfiguration( 'apiKey', $apiKey );
					return MessageFactory::factory( 'rest' )->success( esc_html__( 'Your API key has been save.',
						'myshopkit' ) );
				}
				return MessageFactory::factory( 'rest' )
				                     ->error( $aResponseCheckApiKey['message'], $aResponseCheckApiKey['code'] );
			}
			return MessageFactory::factory( 'rest' )->error( 'Your api must not be empty.', 400 );
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckUserLoggedIn['message'], $aResponseCheckUserLoggedIn['code'] );
	}
	
	/**
	 * Get all lists
	 *
	 * @return WP_REST_Response
	 */
	public function getAllLists(): WP_REST_Response {
		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			$userID    = get_current_user_id();
			$aUserMeta = $this->getCurrentUserMeta( $userID );
			$apiKey    = $aUserMeta['apiKey'];
			try {
				$aLists = $this->connectMailChimp( $apiKey )->lists->getAllLists()->lists;
				return MessageFactory::factory( 'rest' )->success( esc_html__( 'This is your lists.', 'myshopkit' ),
					[
						'items' => $aLists,
					]
				);
			} catch ( Exception $oException ) {
				return MessageFactory::factory( 'rest' )
				                     ->error( esc_html__( 'There have been a critical error with your configurations. Please check again.',
					                     'myshopkit' ), $oException->getCode() );
			}
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckUserLoggedIn['message'], $aResponseCheckUserLoggedIn['code'] );
	}
	
	/**
	 * checking the Request is valid to save User listID
	 *
	 * @param WP_REST_Request $oRequest
	 *
	 * @return WP_REST_Response
	 */
	public function saveListIdToMailChimp( WP_REST_Request $oRequest ): WP_REST_Response {
		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			$userID        = get_current_user_id();
			$aResponseData = $this->getCurrentUserMeta( $userID );
			$apiKey        = $aResponseData['apiKey'] ?? '';
			if ( ! empty( $apiKey ) ) {
				$listID = $oRequest->get_param( 'listID' );
				if ( ! empty( $listID ) ) {
					$aResponseCheckListID = $this->checkIsUserListIdValid( $apiKey, $listID );
					if ( $aResponseCheckListID['status'] == 'success' ) {
						$this->updateMailServiceConfiguration( 'listID', $listID );
						return MessageFactory::factory( 'rest' )->success( esc_html__( 'Your list ID has been saved.',
							'myshopkit' ) );
					}
					return MessageFactory::factory( 'rest' )
					                     ->error( $aResponseCheckListID['message'], $aResponseCheckListID['code'] );
				}
				return MessageFactory::factory( 'rest' )->error( esc_html__( 'Your list ID must not be empty.',
					'myshopkit' ), 400 );
			}
			return MessageFactory::factory( 'rest' )->error( esc_html__( 'Please add your api key before process.',
				'myshopkit' ), 400 );
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckUserLoggedIn['message'], $aResponseCheckUserLoggedIn['code'] );
	}
	
	/**
	 * Subscribe User Email manually
	 *
	 * @param WP_REST_Request $oRequest
	 *
	 * @return WP_REST_Response
	 */
	public function subscribeEmailManually( WP_REST_Request $oRequest ): WP_REST_Response {
		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			$userID                   = get_current_user_id();
			$email                    = $oRequest->get_param( 'email' );
			$aResponseCheckEmailValid = $this->checkIsEmailValid( $email ?? '' );
			if ( $aResponseCheckEmailValid['status'] == 'success' ) {
				$aUserMeta = $this->getCurrentUserMeta( $userID );
				if ( $aUserMeta['status'] == 'enable' ) {
					try {
						$apiKey = $aUserMeta['apiKey'];
						$listID = $aUserMeta['listID'];
						$this->postSubscriberToMailChimp( $email, $apiKey, $listID );
						return MessageFactory::factory( 'rest' )
						                     ->success( esc_html__( 'Email is added to the list successfully',
							                     'myshopkit' ) );
					} catch ( Exception $oException ) {
						return MessageFactory::factory( 'rest' )
						                     ->success( esc_html__( 'Your email is already existed in this list.',
							                     'myshopkit' ) );
					}
				}
				return MessageFactory::factory( 'rest' )
				                     ->error( esc_html__( 'Service hasn\'t been active yet. Please active it before process.',
					                     'myshopkit' ), 400 );
			}
			return MessageFactory::factory( 'rest' )
			                     ->error( $aResponseCheckEmailValid['message'], $aResponseCheckEmailValid['code'] );
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckUserLoggedIn['message'], $aResponseCheckUserLoggedIn['code'] );
	}
	
	/**
	 * Subscribe User Email directly
	 *
	 * @param array $aInfo = array('email' => $email);
	 *
	 * @return WP_REST_Response
	 */
	public function subscribeEmailDirectly( array $aInfo ): WP_REST_Response {
		$aUserMeta = $this->getCurrentUserMeta( $aInfo['userID'] );
		if ( $aUserMeta['status'] == 'enable' ) {
			try {
				$apiKey = $aUserMeta['apiKey'];
				$listID = $aUserMeta['listID'];
				$this->postSubscriberToMailChimp( $aInfo['email'], $apiKey, $listID );
				return MessageFactory::factory( 'rest' )
				                     ->success( esc_html__( 'Email is added to the list successfully.', 'myshopkit' ) );
			} catch ( Exception $oException ) {
				return MessageFactory::factory( 'rest' )
				                     ->error( esc_html__( 'Your email is already existed in this list.', 'myshopkit' ),
					                     $oException->getCode() );
			}
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( esc_html__( 'Service hasn\'t been active yet. Please active it before process.',
			                     'myshopkit' ), 400 );
	}
	
	/**
	 * process update user email
	 *
	 *
	 * @param string $email
	 * @param string $apiKey
	 * @param string $listID
	 *
	 */
	public function postSubscriberToMailChimp( string $email, string $apiKey, string $listID ) {
		$this->connectMailChimp( $apiKey );
		self::$oClient->lists->addListMember( $listID, [
			'email_address' => $email,
			'status'        => 'subscribed',
		] );
	}
}