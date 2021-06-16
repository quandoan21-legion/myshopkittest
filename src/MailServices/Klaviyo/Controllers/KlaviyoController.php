<?php

namespace MyShopKit\MailServices\Klaviyo\Controllers;

use Klaviyo\Exception\KlaviyoException;
use Klaviyo\Klaviyo;
use Klaviyo\Klaviyo as KlaviyoApi;
use Klaviyo\Model\ProfileModel as KlaviyoProfile;
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
			]
		);
		register_rest_route(
			MYSHOPKIT_REST,
			$this->getChangeServiceStatusEndPoint( $this->mailService ),
			[
				[
					'methods'             => 'PUT',
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
		if ( empty( $status ) || ! in_array( $status, [ 'deactive', 'active' ] ) ) {
			return MessageFactory::factory( 'rest' )->error( esc_html__( 'Please active or deactive the service.', 'myshopkit' ), 400 );
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
	
	public function subscribeEmailManually( WP_REST_Request $oRequest ): WP_REST_Response {
		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			$userID                   = get_current_user_id();
			$email                    = $oRequest->get_param( 'email' );
			$aResponseCheckEmailValid = $this->checkIsEmailValid( $email ?? '' );
			if ( $aResponseCheckEmailValid['status'] == 'success' ) {
				$aUserMeta = $this->getCurrentUserMeta( $userID );
				if ( $aUserMeta['status'] == 'active' ) {
					$publicApiKey              = $aUserMeta['publicApiKey'];
					$privateApiKey             = $aUserMeta['privateApiKey'];
					$aResponseCheckApiKeyValid = $this->checkIsUserApiKeyValid( $privateApiKey, $publicApiKey );
					if ( $aResponseCheckApiKeyValid['status'] == 'success' ) {
						$listID                    = $aUserMeta['listID'];
						$aResponseCheckListIdValid = $this->checkIsUserListIdValid( $privateApiKey, $publicApiKey, $listID );
						if ( $aResponseCheckListIdValid['status'] == 'success' ) {
							$this->connectKlaviyo( $privateApiKey, $publicApiKey );
							try {
								$this->connectKlaviyo( $privateApiKey, $publicApiKey );
								$addProfile = [
									new KlaviyoProfile(
										[
											'$email' => $email,
										]
									),
								];
								$this->postEmail( $addProfile, $listID );
								return MessageFactory::factory( 'rest' )
								                     ->success( esc_html__( 'Email is added to the list successfully',
									                     'myshopkit' ) );
							} catch ( KlaviyoException $oException ) {
								return MessageFactory::factory( 'rest' )
								                     ->success( $oException->getMessage() );
							}
						}
						return MessageFactory::factory( 'rest' )
						                     ->error( $aResponseCheckListIdValid['message'], $aResponseCheckListIdValid['code'] );
					}
					return MessageFactory::factory( 'rest' )
					                     ->error( $aResponseCheckApiKeyValid['message'], $aResponseCheckApiKeyValid['code'] );
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
		$aUserMeta = $this->getCurrentUserMeta( get_current_user_id() );
		if ( $aUserMeta['status'] == 'active' ) {
			$publicApiKey              = $aUserMeta['publicApiKey'];
			$privateApiKey             = $aUserMeta['privateApiKey'];
			$aResponseCheckApiKeyValid = $this->checkIsUserApiKeyValid( $privateApiKey, $publicApiKey );
			if ( $aResponseCheckApiKeyValid['status'] == 'success' ) {
				$listID                    = $aUserMeta['listID'];
				$aResponseCheckListIdValid = $this->checkIsUserListIdValid( $privateApiKey, $publicApiKey, $listID );
				if ( $aResponseCheckListIdValid['status'] == 'success' ) {
					$this->connectKlaviyo( $privateApiKey, $publicApiKey );
					try {
						$this->connectKlaviyo( $privateApiKey, $publicApiKey );
						$email = $aInfo['email'];
						$addProfile = [
							new KlaviyoProfile(
								[
									'$email' => $email,
								]
							),
						];
						$this->postEmail( $addProfile, $listID );
						return MessageFactory::factory( 'rest' )
						                     ->success( esc_html__( 'Email is added to the list successfully',
							                     'myshopkit' ) );
					} catch ( KlaviyoException $oException ) {
						return MessageFactory::factory( 'rest' )
						                     ->success( $oException->getMessage() );
					}
				}
				return MessageFactory::factory( 'rest' )
				                     ->error( $aResponseCheckListIdValid['message'], $aResponseCheckListIdValid['code'] );
			}
			return MessageFactory::factory( 'rest' )
			                     ->error( $aResponseCheckApiKeyValid['message'], $aResponseCheckApiKeyValid['code'] );
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
	public function postEmail( array $addProfile, string $listID ) {
		self::$oClient->lists->addMembersToList( $listID, $addProfile );
	}
	
}