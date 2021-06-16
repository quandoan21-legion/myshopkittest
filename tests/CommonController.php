<?php

namespace MyShopKitTest;


use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CommonController extends TestCase
{
	use HTTP;

	protected array $aCreatedAccountIds;

	public function setUp()
	{
		parent::setUp(); // TODO: Change the autogenerated stub

		$this->createApplicationPassword();
		$this->configureAPI();
	}

	public function setPostToTest($postId)
	{
		update_post_meta($postId, 'wiloke_cli_test', 'yes');

		return $this;
	}

	public function deleteAllTest(
		$postType = 'any',
		$aPostStatus = ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash']
	)
	{
		$query = new \WP_Query([
			'post_type'      => $postType,
			'post_status'    => $aPostStatus,
			'meta_query'     => [
				[
					'key'     => 'wiloke_cli_test',
					'value'   => 'yes',
					'compare' => '=',
				],
			],
			'posts_per_page' => -1
		]);

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				wp_delete_post($query->post->ID, true);
			}
		}
		wp_reset_postdata();

		return $this;
	}

	protected function createApplicationPassword()
	{
		$phpUnitTest = dirname(plugin_dir_path(__FILE__)) . '/phpunit.xml';
		$content = file_get_contents($phpUnitTest);

		if (strpos($content, 'ADMIN_AUTH_PASS_VALUE') !== false) {
			$aResponse = \WP_Application_Passwords::create_new_application_password($this->getAdminId(), [
				'name' => 'My App'
			]);
			$content = str_replace('ADMIN_AUTH_PASS_VALUE', $aResponse[0], $content);
			$status = file_put_contents($phpUnitTest, $content);
			if ($status) {
				global $aWILOKEGLOBAL;
				$aWILOKEGLOBAL['ADMIN_AUTH_PASS'] = $aResponse[0];
			} else {
				throw new \RuntimeException('We could not replace ADMIN_AUTH_PASS_VALUE');
			}
		}
	}

	/**
	 * @param $object
	 * @param $methodName
	 * @param array $aParams
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function invokeMethod($object, $methodName, array $aParams = [])
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $aParams);
	}

	public function setPrivateProperty($object, $propertyName, $params)
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getProperty($propertyName);
		$method->setAccessible(true);

		$method->setValue($object, $params);
	}

	public function getPrivateProperty($object, $propertyName)
	{
		$reflection = new \ReflectionClass(get_class($object));
		$method = $reflection->getProperty($propertyName);
		$method->setAccessible(true);

		return $method->getValue($object);
	}

	/**
	 * @params array{username: string, password: string, roles: array}
	 * @param array $aUser
	 *
	 * @return bool
	 */
	public function createUser(array $aUser): bool
	{
		if (username_exists($aUser['username'])) {
			$oUser = get_user_by('login', $aUser['username']);
			wp_set_password($aUser['password'], $oUser->ID);
			$aApplication = \WP_Application_Passwords::create_new_application_password($oUser->ID, ['name' => 'Test']);

			$this->addAccounts(
				$aUser['username'],
				[
					'username' => $aUser['username'],
					'password' => $aUser['password'],
					'auth'     => $aApplication[0]
				]
			);

			unset($aUser['username']);
			unset($aUser['password']);
			unset($aUser['auth']);

			if (!empty($aUser)) {
				$aUser['ID'] = $oUser->ID;
				wp_update_user($aUser);
			}

			$this->aCreatedAccountIds[] = $oUser->ID;

			$userId = $oUser->ID;
		} else {
			$userId = wp_create_user($aUser['username'], $aUser['password']);
			if (empty($userId) || is_wp_error($userId)) {
				$aUser['ID'] = $userId;
				unset($aUser['username']);
				unset($aUser['password']);

				wp_update_user($aUser);

				$aApplication = \WP_Application_Passwords::create_new_application_password($userId, ['name' => uniqid('test')]);

				$this->addAccounts($aUser['username'], [
					'username' => $aUser['username'],
					'password' => $aUser['password'],
					'auth'     => $aApplication[0]
				]);

				$this->aCreatedAccountIds[] = $userId;
				$oUser = new \WP_User($userId);
			} else {
				$oUser = new \WP_User($userId);
			}
		}

		if (!empty($userId) && !is_wp_error($userId)) {
			update_user_meta($oUser->ID, 'wilcity_confirmed', true);
			$oUser->remove_role('subscriber');

			if (isset($aUser['roles'])) {
				$aRoles = is_array($aUser['roles']) ? $aUser['roles'] : explode(',', $aUser['roles']);

				foreach ($aRoles as $role) {
					$oUser->add_role($role);
				}
			}

		}
		return false;
	}
}
