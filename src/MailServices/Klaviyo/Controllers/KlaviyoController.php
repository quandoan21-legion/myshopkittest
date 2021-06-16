<?php

namespace MyShopKit\MailServices\Klaviyo\Controllers;

use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Klaviyo;
use Klaviyo\Klaviyo as KlaviyoApi;
use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\MailServices\Shared\TraitGenerateRestEndpoint;
use MyShopKit\MailServices\Shared\TraitMailServicesConfiguration;
use MyShopKit\MailServices\Shared\TraitMailServicesValidation;
use WP_REST_Request;
use WP_REST_Response;

class KlaviyoController {
	use TraitGenerateRestEndpoint;
	use TraitMailServicesConfiguration;
	use TraitMailServicesValidation;
	
	protected static ?Klaviyo $oClient = NULL;
	public string $key = 'klaviyo_info';
	public string $mailService = 'klaviyo';
	
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
					'callback'            => [ $this, 'saveApiKey' ],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'PATCH',
					'callback'            => [ $this, 'changeServiceStatus' ],
					'permission_callback' => '__return_true',
				],
			]
		);
		register_rest_route(
			MYSHOPKIT_REST,
			$this->getSaveListIdEndPoint( $this->mailService ),
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'getAllLists' ],
					'permission_callback' => '__return_true',
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'saveListId' ],
					'permission_callback' => '__return_true',
				],
			]
		);
	}
	
	public function connectKlaviyo( string $privateApiKey, string $publicApiKey ): Klaviyo {
		if ( self::$oClient == NULL ) {
			$oClient = new KlaviyoApi( $privateApiKey, $publicApiKey );
			return self::$oClient = $oClient;
		}
		return self::$oClient;
	}
	
	public function checkIsUserApiKeyValid( ?string $privateApiKey, ?string $publicApiKey ): array {
		if ( ! empty( $privateApiKey ) && ! empty( $publicApiKey ) ) {
			try {
				$oClient = new KlaviyoApi( $privateApiKey, $publicApiKey );
				$oClient->lists->getLists();
				$this->connectKlaviyo( $privateApiKey, $publicApiKey );
				return MessageFactory::factory()->success( 'OK' );
			} catch ( KlaviyoException $oException ) {
				return MessageFactory::factory()
				                     ->error( esc_html__( 'Oops! Look like your api keys are invalid. Please check again.', 'myshopkit' ),
					                     $oException->getCode() );
			}
		}
		return MessageFactory::factory()->error( esc_html__( 'Oops! Look like your hasn\'t insert your api keys yet. Please check again.', 'myshopkit' ), 400 );
	}
	
	public function checkIsUserListIdValid( ?string $privateApiKey, ?string $publicApiKey, ?string $listID ): array {
		if ( ! empty( $privateApiKey ) && ! empty( $publicApiKey ) ) {
			if ( ! empty( $listID ) ) {
				try {
					$this->connectKlaviyo( $privateApiKey, $publicApiKey );
					self::$oClient->lists->getListById( $listID );
					return MessageFactory::factory()
					                     ->success( 'Ok' );
				} catch ( KlaviyoException $oException ) {
					return MessageFactory::factory()
					                     ->error( esc_html__( 'Your list Id is invalid', 'myshopkit' ), $oException->getCode() );
				}
			}
			return MessageFactory::factory()->error( esc_html__( 'Oops! Look like your hasn\'t insert your list ID yet. Please check again.', 'myshopkit' ), 400 );
		}
		return MessageFactory::factory()->error( esc_html__( 'Oops! Look like your hasn\'t insert your api keys yet. Please check again.', 'myshopkit' ), 400 );
	}
	
	public function saveApiKey( WP_REST_Request $oRequest ): WP_REST_Response {
		$privateApiKey               = $oRequest->get_param( 'privateApiKey' );
		$publicApiKey                = $oRequest->get_param( 'publicApiKey' );
		$aResponseCheckIsApiKeyValid = $this->checkIsUserApiKeyValid( $privateApiKey, $publicApiKey );
		if ( $aResponseCheckIsApiKeyValid['status'] == 'success' ) {
			$aResponseCheckIsUserLoggedIn = $this->checkIsUserLoggedIn();
			if ( $aResponseCheckIsUserLoggedIn['status'] == 'success' ) {
				$aApiKey = [
					'publicApiKey'  => $publicApiKey,
					'privateApiKey' => $privateApiKey,
				];
				foreach ( $aApiKey as $keyName => $apiKey ) {
					$this->updateMailServiceConfiguration( $keyName, $apiKey );
				}
				return MessageFactory::factory( 'rest' )->success( esc_html__( 'Your API key has been save.',
					'myshopkit' ) );
			}
			return MessageFactory::factory( 'rest' )
			                     ->error( $aResponseCheckIsUserLoggedIn['message'], $aResponseCheckIsUserLoggedIn['code'] );
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckIsApiKeyValid['message'], $aResponseCheckIsApiKeyValid['code'] );
		
	}
	
	public function getAllLists(): WP_REST_Response {
		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			$aUserMeta     = $this->getCurrentUserMeta( get_current_user_id() );
			$publicApiKey  = $aUserMeta['publicApiKey'];
			$privateApiKey = $aUserMeta['privateApiKey'];
			try {
				$aLists         = $this->connectKlaviyo( $privateApiKey, $publicApiKey )->lists->getLists();
				$aFilteredLists = array_map( function ( $aLists ) {
					return [
						'value' => $aLists['list_name'],
						'id'    => $aLists['list_id'],
					];
				}, $aLists );
				return MessageFactory::factory( 'rest' )->success( esc_html__( 'This is your lists.', 'myshopkit' ),
					[
						'items' => $aFilteredLists,
					]
				);
			} catch ( KlaviyoException $oException ) {
				return MessageFactory::factory( 'rest' )
				                     ->error( esc_html__( 'There have been a critical error with your configurations. Please check again.',
					                     'myshopkit' ), $oException->getCode() );
			}
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckUserLoggedIn['message'], $aResponseCheckUserLoggedIn['code'] );
	}
	
	public function changeServiceStatus( WP_REST_Request $oRequest ): WP_REST_Response {
		$status = $oRequest->get_param( 'status' );
		if ( empty( $status ) || ( $status !== 'active' && $status !== 'deactive' ) ) {
			return MessageFactory::factory( 'rest' )->error( esc_html__( 'We have some problems processing your request. PLease check again.', 'myshopkit' ), 400 );
		}
		$aResponseCheckIsUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckIsUserLoggedIn['status'] == 'success' ) {
			$this->updateMailServiceConfiguration( 'status', $status );
			return MessageFactory::factory( 'rest' )->success( esc_html__( 'Your service has been changed', 'myshopkit' ) );
		}
		return MessageFactory::factory( 'rest' )->error( $aResponseCheckIsUserLoggedIn['message'], $aResponseCheckIsUserLoggedIn['code'] );
	}
	
	public function saveListId( WP_REST_Request $oRequest ): WP_REST_Response {
		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			$aUserMeta                   = $this->getCurrentUserMeta( get_current_user_id() );
			$publicApiKey                = $aUserMeta['publicApiKey'];
			$privateApiKey               = $aUserMeta['privateApiKey'];
			$aResponseCheckIsApiKeyValid = $this->checkIsUserApiKeyValid( $privateApiKey, $publicApiKey );
			if ( $aResponseCheckIsApiKeyValid['status'] == 'success' ) {
				$listID                          = $oRequest->get_param( 'listID' );
				$aResponseCheckUserListIdIsValid = $this->checkIsUserListIdValid( $privateApiKey, $publicApiKey, $listID );
				if ( $aResponseCheckUserListIdIsValid['status'] == 'success' ) {
					$this->updateMailServiceConfiguration( 'listID', $listID );
					return MessageFactory::factory( 'rest' )->success( esc_html__( 'Your list Id has been saved successfully', 'myshopkit' ) );
				}
				return MessageFactory::factory( 'rest' )
				                     ->error( $aResponseCheckUserListIdIsValid['message'], $aResponseCheckUserListIdIsValid['code'] );
			}
			return MessageFactory::factory( 'rest' )
			                     ->error( $aResponseCheckIsApiKeyValid['message'], $aResponseCheckIsApiKeyValid['code'] );
		}
		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckUserLoggedIn['message'], $aResponseCheckUserLoggedIn['code'] );
	}
	
}