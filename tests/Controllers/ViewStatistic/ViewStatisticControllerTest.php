<?php

namespace MyShopKitTest\Controllers\ViewStatistic;

use MyShopKit\Shared\AutoPrefix;
use MyShopKitTest\CommonController;

class ViewStatisticControllerTest extends CommonController {
    public static $aIDs=[];
	protected array $aDataPopup
		= [
			'post_title'   => 'test1',
			'post_content' => 'test1',
			'post_status'  => 'publish',
		];
    public static function tearDownAfterClass() {
        foreach ( self::$aIDs as $id ) {
            wp_delete_post( $id, true );
        }
    }

    public function getPopupID(): int {
		$this->aDataPopup['post_type'] = AutoPrefix::namePrefix( 'popup' );
		$result                        = wp_insert_post( $this->aDataPopup );

		return ! empty( $result ) ? $result : 0;
	}

	public function testCreateClickAPI(): int {
		$postID    = $this->getPopupID();
		self::$aIDs[]=$postID;
		$aResponse = $this->setUserLogin( 'admin' )->restApi( 'insights/popups/views/'.$postID, 'put');

		$this->assertTrue(isset( $aResponse['data']));
		$this->assertIsString( $aResponse['data']['id'] );

		return $postID;
	}

	/**
	 * @depends  testCreateClickAPI
	 */
	public function testUpdateClickAPI( $postID ): int {
		$aResponse = $this->setUserLogin( 'admin' )->restApi( 'insights/popups/views/'.$postID, 'PUT');
		$this->assertTrue(isset( $aResponse['data']));
		$this->assertIsString( $aResponse['data']['id'] );

		return $postID;
	}

	/**
	 * @depends  testUpdateClickAPI
	 */
	public function testGetClicks( $postID ): int {
		$aResponse = $this->setUserLogin( 'admin' )->restApi( 'insights/popups/views', 'get', [
			'filter' => 'thisWeek'
		] );
		$this->assertCount( 7, $aResponse['data']['timeline'] );
		$this->assertIsInt( $aResponse['data']['summary'] );
		$this->assertEquals( 'view', $aResponse['data']['type'] );

		return $postID;
	}

	/**
	 * @depends  testGetClicks
	 */
	public function testGetClickWithPopup( $postID ): int {
		$aResponse = $this->setUserLogin( 'admin' )->restApi( 'insights/popups/views/' . $postID, 'get', [
			'filter' => 'thisWeek'
		] );
		$this->assertCount( 7, $aResponse['data']['timeline'] );
		$this->assertIsInt( $aResponse['data']['summary'] );
		$this->assertEquals( 'view', $aResponse['data']['type'] );

		return $postID;
	}

}
