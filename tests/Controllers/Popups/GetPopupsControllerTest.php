<?php


namespace MyShopKitTest\Controllers\Popups;


use MyShopKit\Popup\Services\Post\PopupQueryService;
use MyShopKitTest\CommonController;

class GetPopupsControllerTest extends CommonController {
	public static array $aIDs = [];

	private function getMyData(): array {
		return [
			[
				'title'  => 'Active',
				'status' => 'active',
				'config' => json_encode( [ 'config' => 1, 'goal' => 'okr' ] )
			],
			[
				'title'  => 'Deactive',
				'status' => 'deactive',
				'config' => json_encode( [ 'config' => 1, 'goal' => 'okr' ] )
			]
		];
	}

	public static function tearDownAfterClass() {
		foreach ( self::$aIDs as $id ) {
			wp_delete_post( $id, true );
		}
	}

	public function testPostStatusArgs() {
		$aResponse = ( new PopupQueryService() )->setRawArgs( [ 'status' => 'active' ] )->parseArgs()->getArgs();
		$this->assertEquals( 'publish', $aResponse['post_status'] );

		$aResponse = ( new PopupQueryService() )->setRawArgs( [ 'status' => 'deactive' ] )->parseArgs()->getArgs();
		$this->assertEquals( 'draft', $aResponse['post_status'] );
	}

	public function testLimitArgs() {
		$aResponse = ( new PopupQueryService() )->setRawArgs( [ 'limit' => 100 ] )->parseArgs()->getArgs();
		$this->assertEquals( 30, $aResponse['posts_per_page'] );

		$aResponse = ( new PopupQueryService() )->setRawArgs( [ 'limit' => 25 ] )->parseArgs()->getArgs();
		$this->assertEquals( 25, $aResponse['posts_per_page'] );
	}

	private function hasActivePopup( array $aItems, $title ) {
		foreach ( $aItems as $aItem ) {
			if ( $aItem['status'] == 'active' && $aItem['title'] == $title ) {
				return true;
			}
		}

		return false;
	}

	private function hasDeactivePopup( array $aItems, $title ) {
		foreach ( $aItems as $aItem ) {
			if ( $aItem['status'] == 'deactive' && $aItem['title'] == $title ) {
				return true;
			}
		}

		return false;
	}

	public function testGetPopups() {
		foreach ( $this->getMyData() as $aData ) {
			$aResponse = $this->setUserLogin( 'admin' )->restPOST( 'popups', $aData );
			if ( $aResponse['status'] == 'success' ) {
				self::$aIDs[] = $aResponse['data']['id'];
			}
		}

		$this->assertCount( 2, self::$aIDs );


		$aResponse = $this->setUserLogin( 'admin' )->restGET( 'popups', [ 'status' => 'deactive' ] );
		// Co 1 publish item
		$this->assertEquals( 'deactive', $aResponse['data']['items'][0]['status'] );
		$this->assertTrue( $this->hasDeactivePopup( $aResponse['data']['items'], $this->getMyData()[1]['title'] ) );

		$aResponse = $this->setUserLogin( 'admin' )->restGET( 'popups', [ 'status' => 'active' ] );
		$this->assertEquals( 'active', $aResponse['data']['items'][0]['status'] );
		$this->assertTrue( $this->hasActivePopup( $aResponse['data']['items'], $this->getMyData()[0]['title'] ) );
	}
}
