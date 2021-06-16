<?php


namespace MyShopKit\Shared\Post;


use MyShopKit\Illuminate\Message\MessageFactory;

trait TraitIsPostAuthor {

	/**
	 * @throws \Exception
	 */
	public function isPostExists( $id ): bool {
		if ( ! get_post_status( $id ) ) {
			throw new \Exception( esc_html__( 'Sorry,the popup is not exists in database', 'myshopkit' ) );
		}

		return true;
	}

	/**
	 * @throws \Exception
	 */
	public function isPostAuthor( $id ): bool {
		$this->isPostExists( $id );

		if ( current_user_can( 'administrator' ) ) {
			return true;
		}

		if ( get_post_field( 'post_author', $id ) != get_current_user_id() ) {
			throw new \Exception( esc_html__( 'Sorry,you are not the author of this the popup',
				'myshopkit' ) );
		}

		return true;
	}
}
