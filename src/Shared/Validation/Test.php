<?php

namespace MyShopKit\Shared\Validation;

use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Shared\AutoPrefix;
use Webmozart\Assert\Assert;

class Test {
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'registerRouter' ] );
	}

	public function registerRouter() {
		register_rest_route( MYSHOPKIT_REST, 'test-validation', [
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'responseData' ]
			]
		] );
	}

	public function responseData() {
		return MessageFactory::factory( 'rest' )->response(
			Validation::make(
				[
					'id'       => 123,
					'username' => 'Wiloke',
					'timeline' => [
						'from' => 123,
						'to'   => 456
					],
					'status'   => 'errors'
				],
				[
					'id'       => [
						'string'
					],
					'username' => [
						'required'
					],
					'timeline' => [
						Rule::allKeyExistsInArray( [ 'from', 'to', 'summary' ] ),
						Rule::validArrayValue( [
							'from' => [ 'string' ],
							'to'   => [ 'string' ]
						] )
					],
					'status'   => [
						Rule::inArray( [ 'success', 'error' ] )
					]
				]
			)
		);
	}
}
