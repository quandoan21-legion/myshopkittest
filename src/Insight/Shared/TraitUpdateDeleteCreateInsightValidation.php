<?php


namespace MyShopKit\Insight\Shared;


use EBase\Shopify\LoginRegister\Models\CustomerShopModel;
use MyShopKit\Illuminate\Message\MessageFactory;

trait TraitUpdateDeleteCreateInsightValidation {
	/**
	 * Validate data trước khi thực hiện việc update hoặc create thống kê dữ liệu
	 *
	 * @param $postID
	 * @param bool $isRequiredToken
	 * @param string $expectedPostType
	 *
	 * @return array
	 */
	private function validateCreateOrUpdateInsight( $postID, bool $isRequiredToken = true, string $expectedPostType = ''
	): array {
		if ( ! $shopID = ebaseGetCurrentShopID( $isRequiredToken ) ) {
			return MessageFactory::factory()->error(
				esc_html__( 'Sorry, but the shop does not exist currently.', 'myshopkit' ), 404 );
		}

		if ($isRequiredToken) {
			if (CustomerShopModel::getCustomerIDByShopID( $shopID) != get_current_user_id()) {
				return MessageFactory::factory()->error(
					esc_html__( 'Sorry, You do not have permission this perform this action.', 'myshopkit' ), 404 );
			}
		}

		if ( get_post_status( $postID ) !== 'publish' ) {
			return MessageFactory::factory()->error(
				esc_html__( 'Sorry, the post doest not exist at the moment', 'myshopkit' ),
				404
			);
		}

		if ( ! empty( $expectedPostType ) && get_post_type( $postID ) !== $expectedPostType ) {
			return MessageFactory::factory()->error(
				sprintf( esc_html__( 'Sorry, the post type must be %s', 'myshopkit' ), $expectedPostType ),
				400
			);
		}

		return MessageFactory::factory()->success(
			esc_html__( 'The data has been validated', 'myshopkit' ),
			[
				'shopID' => $shopID,
				'postID' => $postID
			]
		);
	}
}
