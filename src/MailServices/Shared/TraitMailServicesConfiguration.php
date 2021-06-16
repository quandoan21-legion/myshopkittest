<?php

namespace MyShopKit\MailServices\Shared;

use MyShopKit\Shared\AutoPrefix;

trait TraitMailServicesConfiguration {
	/**
	 * @param string $field
	 * @param string $value
	 */
	protected function updateMailServiceConfiguration( string $field, string $value ) {
		$aUpdateInfo           = $this->getCurrentUserMeta( get_current_user_id() );
		$aUpdateInfo[ $field ] = $value;
		update_user_meta( get_current_user_id(), AutoPrefix::namePrefix( $this->key ), $aUpdateInfo );
	}
	
	/**
	 * @param $userID
	 *
	 * @return array
	 */
	protected function getCurrentUserMeta( $userID ): array {
		$aUserMeta = get_user_meta( $userID, AutoPrefix::namePrefix( $this->key ), TRUE );
		$aUserMeta = is_array( $aUserMeta ) ? $aUserMeta : [];
		return wp_parse_args( $aUserMeta, [
			'apiKey' => '',
			'listID' => '',
			'status' => 'active',
		] );
	}
	
}