<?php

namespace MyShopKit\MailServices\General\Controllers;

use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\MailServices\Shared\TraitMailServicesValidation;
use WP_REST_Request;
use WP_REST_Response;

class GeneralMailServicesController {
	use TraitMailServicesValidation;

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'registerRouters' ] );
	}

	public function registerRouters() {
		register_rest_route(
			MYSHOPKIT_REST,
			'me/subscribe',
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'subscribeUserEmail' ],
					'permission_callback' => '__return_true',
				],
			]
		);
	}

	public function subscribeUserEmail( WP_REST_Request $oRequest ): WP_REST_Response {
		if ( ! is_email( $email = $oRequest->get_param( 'email' ) ) ) {
			return MessageFactory::factory( 'rest' )->error(
				esc_html__( 'Oops! The email is invalid, please re-check it.', 'myshopkit' ),
				400
			);
		}

		$aResponseCheckUserLoggedIn = $this->checkIsUserLoggedIn();
		if ( $aResponseCheckUserLoggedIn['status'] == 'success' ) {
			do_action(
				MYSHOPKIT_HOOK_PREFIX . 'after/subscribed',
				[
					'email'  => $email,
					'userID' => get_current_user_id()
				]
			);

			return MessageFactory::factory( 'rest' )
			                     ->success(
				                     esc_html__( 'Thank for your subscription! We promise won\'t spam you.',
					                     'myshopkit' )
			                     );
		}

		return MessageFactory::factory( 'rest' )
		                     ->error( $aResponseCheckUserLoggedIn['message'], $aResponseCheckUserLoggedIn['code'] );
	}
}
