<?php

namespace MyShopKitTest\MailServices\MailChimp\Controllers;

<<<<<<< HEAD
<<<<<<< HEAD
use MyShopKit\MailServices\MailChimp\Controllers\MailChimpController;
use MyShopKit\MailServices\Shared\TraitMailServicesConfiguration;
=======
use MyShopKit\MailServices\MailChimp\Controllers\MailChimpController;
>>>>>>> 48c6133 (add php unit test (unfinished))
use MyShopKitTest\CommonController;
use WP_REST_Request;

class MailChimpControllerTest extends CommonController {
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
	use TraitMailServicesConfiguration;
	
	public string $key = 'mailchimp_info';
	public string $listID = '6b6867d38e';
	public string $apiKey = 'd2f65d9a3686f6a08e6fesahdagwhjdghjagdjhwagjdhgwjh94c9f30f3e7-us1';
	public string $arrayKey = 'hdkjawhdkjahwkdj';
=======
	public string $listID = '6b6867d38e';
	public string $apiKey = 'd2f65d9a3686f6a08e6fe94c9f30f3e7-us1';
	public string $email = 'qdaosdasdawdwdn21@gmail.com';
	public string $arrayKey = 'status';
>>>>>>> d7d14db (added shared action hook)
	public string $arrayVal = 'enable';

//  Please enter the correct api key and list id of your mailchimp account to make some of theses tests work properly
//  Your correct api key
	public string $userMailChimpApiKey = 'd2f65d9a3686f6a08e6fe94c9f30f3e7-us1';
//	Your correct list id if you don't haven't create a list yet please go to mailchimp.com to create a new list
	public string $userMailChimpListID = '6b6867d38e';
<<<<<<< HEAD
	
	public function generateRandomEmail(): string {
		$prefix = uniqid( 'TestMailChimp' );
		return $prefix . '@gmail.com';
		
=======

	public function testUpdateMailChimpInfo() {
		$userID = 1;
		$this->setUserLogin( 'admin' );
		( new MailChimpController() )->updateMailChimpInfo( $this->arrayKey, $this->arrayVal );
		$aSavedArray    = ( new MailChimpController() )->getUserCurrentMailChimpMeta( $userID );
		$savedArrayName = array_key_exists( $this->arrayKey, $aSavedArray );
		$this->assertTrue( $savedArrayName );
>>>>>>> d7d14db (added shared action hook)
	}
	
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
	public function testPostApiKeyToMailChimp() {
		wp_set_current_user( NULL, 'admin' );
		$userID       = get_current_user_id();
		$oFakeRequest = new WP_REST_Request();
		$oFakeRequest->set_method( 'POST' );
		$oFakeRequest->set_param( 'apikey', $this->apiKey );
		$oMock = $this->getMockBuilder( 'MyShopKit\MailServices\MailChimp\Controllers\MailChimpController' )->setMethods( [
			'checkIsUserApiKeyValid',
			'checkIsUserLoggedIn',
		] )->getMock();
		$oMock->expects( $this->any() )->method( 'checkIsUserLoggedIn' )->will( $this->returnValue( [
			'status' => 'success',
			'msg'    => 'okokok',
		] ) );
		$oMock->expects( $this->any() )->method( 'checkIsUserApiKeyValid' )->will( $this->returnValue( [
			'status' => 'success',
			'msg'    => 'okokok',
		] ) );
		$oMock->saveApiKeyToMailChimp( $oFakeRequest );
		$aSavedUserCurrentMailChimpMeta = $this->getCurrentUserMeta( $userID );
		$aSavedApiKey                   = $aSavedUserCurrentMailChimpMeta['apiKey'];
		$this->assertEquals( $aSavedApiKey, $this->apiKey );
//		return to correct apiKey
		$oReturnRequest = new WP_REST_Request();
		$oReturnRequest->set_method( 'POST' );
		$oReturnRequest->set_param( 'apikey', $this->userMailChimpApiKey );
		$oMock->saveApiKeyToMailChimp( $oReturnRequest );
	}
	
	/**
	 * @depends testPostApiKeyToMailChimp
	 */
	public function testPostListIdToMailChimp() {
		wp_set_current_user( NULL, 'admin' );
		$userID       = get_current_user_id();
		$oFakeRequest = new WP_REST_Request();
		$oFakeRequest->set_method( 'POST' );
		$oFakeRequest->set_param( 'listID', $this->listID );
		$oMock = $this->getMockBuilder( 'MyShopKit\MailServices\MailChimp\Controllers\MailChimpController' )->setMethods( [
			'checkIsUserListIdValid',
			'checkIsUserLoggedIn',
		] )->getMock();
		$oMock->expects( $this->any() )->method( 'checkIsUserLoggedIn' )->will( $this->returnValue( [
			'status' => 'success',
			'msg'    => 'okokok',
		] ) );
		$oMock->expects( $this->any() )->method( 'checkIsUserListIdValid' )->will( $this->returnValue( [
			'status' => 'success',
			'msg'    => 'OK',
		] ) );
<<<<<<< HEAD
		$oMock->saveListIdToMailChimp( $oFakeRequest );
		$aSavedUserCurrentMailChimpMeta = $this->getCurrentUserMeta( $userID );
=======
		$oMock->postUserListIdToMailChimp( $oFakeRequest );

		$aSavedUserCurrentMailChimpMeta = ( new MailChimpController() )->getCurrentUserMeta( $userID );
>>>>>>> e4fba95 (added requirements)
		$aSavedListID                   = $aSavedUserCurrentMailChimpMeta['listID'];
		$this->assertEquals( $aSavedListID, $this->listID );
//		return to correct listID
		$oCorrectRequest = new WP_REST_Request();
		$oCorrectRequest->set_method( 'POST' );
		$oCorrectRequest->set_param( 'listID', $this->userMailChimpListID );
		$oMock->saveListIdToMailChimp( $oCorrectRequest );
	}
	
	/**
	 * @depends testPostListIdToMailChimp
	 */
<<<<<<< HEAD
	public function testPostSubscriberToMailChimp() {
		$email = $this->generateRandomEmail();
		( new MailChimpController() )->postSubscriberToMailChimp( $email, $this->userMailChimpApiKey, $this->userMailChimpListID );
		$oMailChimpInfo    = ( new MailChimpController() )->connectMailChimp( $this->userMailChimpApiKey );
		$response          = $oMailChimpInfo->lists->getListMember( $this->userMailChimpListID, md5( $email ) );
		$savedEmailAddress = $response->email_address;
		$this->assertEquals( $savedEmailAddress, $email );
	}
	
	/**
	 * @depends testPostListIdToMailChimp
	 */
	public function testGetAllLists() {
		$oMock = $this->getMockBuilder( 'MyShopKit\MailServices\MailChimp\Controllers\MailChimpController' )->setMethods( [
			'checkIsUserLoggedIn',
		] )->getMock();
		$oMock->expects( $this->any() )->method( 'checkIsUserLoggedIn' )->will( $this->returnValue( [
			'status' => 'success',
			'msg'    => 'okokok',
		] ) );
		$alistsInfo = $oMock->getAllLists()->data;
		$this->assertArrayHasKey( 'items', $alistsInfo );
=======
use MyShopKit\MailServices\MailChimp\Controllers\MailChimpController as MailChimpController;
use MyShopKitTest\CommonController;

class MailChimpControllerTest extends CommonController {

	public function testReturnObjectOrNot() {
		$mailChimpController = new MailChimpController();
<<<<<<< HEAD
		$oConnectMailChimp   = $mailChimpController->connectMailChimp( 'd2f65d9a3686f6a08e6fe94c9f30f3e7-us1' );
		$oEmailLists         = $mailChimpController->getEmailLists();
		$oGetUserEmailLists  = $mailChimpController->saveUserInfo( 1, 'd2f65d9a3686f6a08e6fe94c9f30f3e7-us1/', '12asdawd3123123' );
//		$this->assertIsObject( $oGetUserEmailLists );
		var_dump($oGetUserEmailLists->myshopkit_mailchimp_infos);die;
=======

		$oConnectMailChimp = $mailChimpController->connectMailChimp( 'd2f65d9a3686f6a08e6fe94c9f30f3e7-us1' );
		$getAllLists       = $mailChimpController->getAllLists();
>>>>>>> 6ca012b (Added requirements)

		$this->assertIsObject( $getAllLists );
		$this->assertIsObject( $oConnectMailChimp );
<<<<<<< HEAD
		$this->assertIsObject( $oEmailLists );
	}

	public function testReturnArrayOrNot() {
		$mailChimpController = new MailChimpController();
		$result = $mailChimpController->addMemberToList('6b6867d38e', 'qdoan21$gmail.com');
		$this->assertIsArray(
			$result,
		);
>>>>>>> 7530414 (Added add email to list in mail chimp function)
=======
	}
=======
	public
	function testSaveEmail() {
		$oFakeRequest = new WP_REST_Request();
		$oFakeRequest->set_method( 'POST' );
		$oFakeRequest->set_param( 'email', $this->email );

		$oMock = $this->getMockBuilder( 'MyShopKit\MailServices\MailChimp\Controllers\MailChimpController' )->setMethods( [ 'getUserCurrentMailChimpMeta' ] )->getMock();
		$oMock->expects( $this->any() )->method( 'getUserCurrentMailChimpMeta' )->will( $this->returnValue(
			[
				'apiKey' => $this->userMailChimpApiKey,
				'listID' => $this->userMailChimpListID,
				'status' => 'enable'
			]
		) );
		$oMock->saveMemberEmail( $oFakeRequest );
>>>>>>> d7d14db (added shared action hook)

	public function testReturnArrayOrNot() {
		$mailChimpController = new MailChimpController();
	}

	public function testReturnStringOrNot() {
		$mailChimpController = new MailChimpController();
		$server              = $mailChimpController->getServer( 'd2f65d9a3686f6a08e6fe94c9f30f3e7-us1' );

		$this->assertIsString( $server );
>>>>>>> 6ca012b (Added requirements)
	}
=======
=======
	public string $listID = '6b6867d38e';
	public string $apiKey = 'd2f65d9a3686f6a08e6fe94c9f30f3e7-us1';
	public string $email = 'qdaosdasdawdwdn21@gmail.com';

>>>>>>> 77aff75 (added phpunit tests (finished))

	public function testSaveApiKey() {
		$userID                         = get_user_by( 'login', 'admin' )->ID;
		$this->setUserLogin( 'admin' )->restApi( 'me/mailchimp/setup/', 'POST', [ 'apikey' => $this->apiKey ] );
		$aSavedUserCurrentMailChimpMeta = ( new MailChimpController() )->getUserCurrentMailChimpMeta( $userID );
		$aSavedApiKey                   = $aSavedUserCurrentMailChimpMeta['apiKey'];
		var_dump($aSavedApiKey);die;

		$this->assertEquals( $aSavedApiKey, $this->apiKey );
	}


	/**
	 * @depends testSaveApiKey
	 */
	public function testSaveListId() {
		$userID = get_user_by( 'login', 'admin' )->ID;
		$this->setUserLogin( 'admin' )->restApi( 'me/mailchimp/lists', 'POST', [ 'listID' => $this->listID ] );
		$aSavedUserCurrentMailChimpMeta = ( new MailChimpController() )->getUserCurrentMailChimpMeta( $userID );
		$aSavedListId                   = $aSavedUserCurrentMailChimpMeta['listID'];

		$this->assertEquals( $aSavedListId, $this->listID );
	}

	/**
	 * @depends testSaveListId
	 */
	public function testSaveMemberEmail() {
		$this->setUserLogin( 'admin' )->restApi( 'mailchimp/lists/members/', 'POST', [ 'email' => $this->email ] );
		$response   = ( new MailChimpController() )->connectMailChimp( $this->apiKey )->lists->getListMember( $this->listID, md5( $this->email ) );
		$savedEmail = $response->email_address;

		$this->assertEquals( $savedEmail, $this->email );
	}

>>>>>>> 48c6133 (add php unit test (unfinished))
}
