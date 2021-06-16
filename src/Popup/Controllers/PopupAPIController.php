<?php


namespace MyShopKit\Popup\Controllers;


use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Popup\Services\Post\CreatePostService;
use MyShopKit\Popup\Services\Post\DeletePostServiceDelete;
use MyShopKit\Popup\Services\Post\PopupQueryService;
use MyShopKit\Popup\Services\Post\PostSkeletonService;
use MyShopKit\Popup\Services\Post\UpdatePostService;
use MyShopKit\Popup\Services\PostMeta\AddPostMetaService;
use MyShopKit\Popup\Services\PostMeta\UpdatePostMetaService;
use MyShopKit\Shared\Message\MessageDefinition;

use WP_REST_Request;

class PopupAPIController {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'registerRouters' ] );
	}

	public function registerRouters() {
		register_rest_route( MYSHOPKIT_REST, 'popups',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'createPopup' ],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'getPopups' ],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'deletePopups' ],
					'permission_callback' => '__return_true'
				],
			]
		);

		register_rest_route( MYSHOPKIT_REST, 'popups/(?P<id>(\d+))',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'getPopup' ],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'PUT',
					'callback'            => [ $this, 'updatePopup' ],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'PATCH',
					'callback'            => [ $this, 'updatePopup' ],
					'permission_callback' => '__return_true'
				],
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'deletePopup' ],
					'permission_callback' => '__return_true'
				]
			]
		);
		register_rest_route( MYSHOPKIT_REST, 'popups/(?P<id>(\d+))/(?P<pluck>(\w+))',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'getSinglePluck' ],
					'permission_callback' => '__return_true'
				]
			]
		);
	}

	public function getPopups( WP_REST_Request $oRequest ) {
		if ( ! is_user_logged_in() ) {
			return MessageFactory::factory( 'rest' )
			                     ->error( MessageDefinition::youMustLogin(), 401 );
		}

		$aData  = $oRequest->get_params();
		$aPluck = $aData['pluck'] ?? '';
		unset( $aData['pluck'] );

		$aResponse = ( new PopupQueryService() )->setRawArgs(
			array_merge(
				$aData,
				[
					'author' => get_current_user_id()
				]
			)
		)->parseArgs()->query( new PostSkeletonService(), $aPluck );

		if ( $aResponse['status'] === 'error' ) {
			return MessageFactory::factory( 'rest' )->error(
				esc_html__( 'Sorry, We could not find your popups', 'myshopkit' ),
				$aResponse['code']
			);
		}

		return MessageFactory::factory()->success( $aResponse['message'], $aResponse['data'] );
	}

	public function getPopup( WP_REST_Request $oRequest ) {
		$aParams = $oRequest->get_params();
		$pluck   = $aParams['pluck'] ?? '';
		unset( $aParams['pluck'] );

		$aResponse = ( new PopupQueryService() )->setRawArgs( $aParams )->parseArgs()
		                                        ->query( new PostSkeletonService(), $pluck, true );

		if ( $aResponse['status'] == 'success' ) {
			return MessageFactory::factory( 'rest' )->success( $aResponse['message'], $aResponse['data'] );
		} else {
			return MessageFactory::factory( 'rest' )->error( $aResponse['message'], $aResponse['code'] );
		}
	}

	public function getSinglePluck( WP_REST_Request $oRequest ) {
		$aParams = $oRequest->get_params();
		$pluck   = $aParams['pluck'];
		unset( $aParams['pluck'] );

		$aResponse = ( new PopupQueryService() )->setRawArgs( $aParams )->parseArgs()
		                                        ->query( new PostSkeletonService(), $pluck, true );

		if ( $aResponse['status'] == 'success' ) {
			if (isset( $aResponse['data'][$pluck])) {
				return MessageFactory::factory( 'rest' )->success(
					$aResponse['message'],
					$aResponse['data'][$pluck]
				);
			}

			return MessageFactory::factory( 'rest' )->success(
				$aResponse['message']
			);
		} else {
			return MessageFactory::factory( 'rest' )->error( $aResponse['message'], $aResponse['code'] );
		}
	}

	public function deletePopup( WP_REST_Request $oRequest ) {
		$postID        = (int) $oRequest->get_param( 'id' );
		$aPostResponse = ( new DeletePostServiceDelete() )->setID( $postID )->delete();
		if ( $aPostResponse['status'] == 'error' ) {
			return MessageFactory::factory( 'rest' )->error( $aPostResponse['message'], $aPostResponse['code'] );
		}

		return MessageFactory::factory( 'rest' )->success(
			$aPostResponse['message'],
			[
				'id' => $aPostResponse['data']['id']
			]
		);
	}

	public function deletePopups( WP_REST_Request $oRequest ) {
		$aPostIDs            = explode( ',', $oRequest->get_param( 'ids' ) );
		$aListOfErrors       = [];
		$aListOfSuccess      = [];
		$oDeletePostServices = new DeletePostServiceDelete();

		foreach ( $aPostIDs as $postID ) {
			$aDeleteResponse = $oDeletePostServices->setID( $postID )->delete();
			if ( $aDeleteResponse['status'] === 'error' ) {
				$aListOfErrors[] = $postID;
			} else {
				$aListOfSuccess[] = $postID;
			}
		}

		if ( ! empty( $aListOfSuccess ) ) {
			return MessageFactory::factory( 'rest' )->success(
				esc_html__( 'Congrats, the popups has been deleted.', 'myshopkit' ),
				[
					'id' => implode( ',', $aListOfSuccess )
				]
			);
		}

		if ( count( $aListOfErrors ) == count( $aPostIDs ) ) {
			return MessageFactory::factory( 'rest' )
			                     ->error(
				                     sprintf(
					                     esc_html__( 'We could not delete the popups that have the following ids %s',
						                     'myshopkit' ),
					                     implode( ",", $aListOfErrors )
				                     ),
				                     401
			                     );
		}

		return MessageFactory::factory( 'rest' )
		                     ->success(
			                     sprintf(
				                     esc_html__( 'The following ids have been deleted %s. We could not delete the the following ids %s',
					                     'myshopkit' ),
				                     implode( ',', $aListOfSuccess ), implode( ',', $aListOfErrors )
			                     )
		                     );
	}

	public function createPopup( WP_REST_Request $oRequest ) {
		if ( empty( get_current_user_id() ) ) {
			return MessageFactory::factory( 'rest' )
			                     ->error( MessageDefinition::youMustLogin(), 401 );
		}

		$aPostResponse = ( new CreatePostService() )->setRawData( $oRequest->get_params() )
		                                            ->performSaveData();

		if ( $aPostResponse['status'] == 'error' ) {
			return MessageFactory::factory( 'rest' )->error( $aPostResponse['message'], $aPostResponse['code'] );
		}

		$aResponse = ( new AddPostMetaService() )->setID( $aPostResponse['data']['id'] )->addPostMeta(
			$oRequest->get_params() );

		if ( $aResponse['status'] == 'error' ) {
			return MessageFactory::factory( 'rest' )->error( $aResponse['message'], $aResponse['code'] );
		}

		return MessageFactory::factory( 'rest' )->success( $aPostResponse['message'],
			[
				'id' => $aPostResponse['data']['id']
			] );
	}

	public function updatePopup( WP_REST_Request $oRequest ) {
		$postID        = $oRequest->get_param( 'id' );
		$aPostResponse = ( new UpdatePostService() )
			->setID( $postID )
			->setRawData( $oRequest->get_params() )
			->performSaveData();

		if ( $aPostResponse['status'] == 'error' ) {
			return MessageFactory::factory( 'rest' )->error( $aPostResponse['message'], $aPostResponse['code'] );
		}

		if ( $aPostResponse['status'] == 'success' ) {
			$aResponse = ( new UpdatePostMetaService() )
				->setID( $aPostResponse['data']['id'] )
				->updatePostMeta( $oRequest->get_params() );

			if ( $aResponse['status'] == 'error' ) {
				return MessageFactory::factory( 'rest' )->error( $aResponse['message'], $aResponse['code'] );
			}
		}

		return MessageFactory::factory( 'rest' )
		                     ->success( $aPostResponse['message'],
			                     [
				                     'id' => (string) $aPostResponse['data']['id']
			                     ]
		                     );
	}
}
