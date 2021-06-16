<?php

namespace MyShopKitTest\ts\MailServices\Shared;

use MyShopKit\MailServices\Shared\TraitMailServicesConfiguration;
use MyShopKitTest\CommonController;

class TraitMailServicesConfigurationTest extends CommonController {
	use TraitMailServicesConfiguration;
	
	public string $key = 'mailchimp_info';
	public string $arrayKey = 'hdkjawhdkjahwkdj';
	public string $arrayVal = 'enable';
	
	public function testUpdateMailServiceConfiguration() {
		wp_set_current_user( NULL, 'admin' );
		$userID = get_current_user_id();
		$this->updateMailServiceConfiguration( $this->arrayKey, $this->arrayVal );
		$aSavedArray    = $this->getCurrentUserMeta( $userID );
		$savedArrayName = array_key_exists( $this->arrayKey, $aSavedArray );
		$this->assertTrue( $savedArrayName );
	}
	
	/**
	 * @depends testUpdateMailServiceConfiguration
	 */
	public function testGetCurrentUserMeta() {
		wp_set_current_user( NULL, 'admin' );
		$userID = get_current_user_id();
		$aResponse = $this->getCurrentUserMeta( $userID );
		$this->assertArrayHasKey( 'apiKey', $aResponse );
		$this->assertArrayHasKey( 'listID', $aResponse );
		$this->assertArrayHasKey( 'status', $aResponse );
	}
}