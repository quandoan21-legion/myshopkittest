<?php


namespace MyShopKit\Popup\Services\Post;


use Exception;
use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Shared\Post\IDeleteUpdateService;
use MyShopKit\Shared\Post\IService;
use MyShopKit\Shared\Post\TraitIsPostAuthor;
use MyShopKit\Shared\Post\TraitMaybeAssertion;
use MyShopKit\Shared\Post\TraitMaybeSanitizeCallback;

class UpdatePostService extends PostService implements IService, IDeleteUpdateService {
	use TraitDefinePostFields;
	use TraitMaybeAssertion;
	use TraitMaybeSanitizeCallback;
	use TraitIsPostAuthor;

	private $postID;

	public function setID( $id ): self
    {
        $this->postID = $id;

        return $this;
    }

	/**
	 * @throws Exception
	 */
	public function validateFields(): IService {
		if ( empty( $this->postID ) ) {
			throw new \Exception( esc_html__( 'The ID is required.', 'myshopkit' ) );
		}

		$this->isPostAuthor( $this->postID );
		foreach ( $this->defineFields() as $friendlyKey => $aField ) {
			if ( isset( $aField['isReadOnly'] ) || ! isset( $this->aRawData[ $friendlyKey ] ) ||
			     ! isset( $this->aRawData[ $friendlyKey ] ) ) {
				continue;
			} else {
				$value = $this->aRawData[ $friendlyKey ];
				// Kiem tha du lieu co dung voi format
				$aAssertionResponse = $this->maybeAssert( $aField, $value );
				if ( $aAssertionResponse['status'] === 'error' ) {
					throw new \Exception( $aAssertionResponse['message'] );
				}

				$this->aData[ $aField['key'] ] = $this->maybeSanitizeCallback( $aField, $value );
			}
		}

		$this->aData['ID'] = $this->postID;

		return $this;
	}

	public function performSaveData(): array {
		try {
		    $this->validateFields();
			$id = wp_update_post( $this->aData );
			if ( is_wp_error( $id ) ) {
				return MessageFactory::factory()->error( $id->get_error_message(), $id->get_error_code() );
			}

			return MessageFactory::factory()->success(
				esc_html__( 'Congrats! The popup has been updated successfully.', 'myshopkit' ),
				[
					'id' => $id
				]
			);
		}
		catch ( \Exception $oException ) {
			return MessageFactory::factory()->error( $oException->getMessage(), $oException->getCode() );
		}
	}

	/**
	 * @param array $aRawData
	 *
	 * @return IService
	 */
	public function setRawData( array $aRawData ): IService {
		$this->aRawData = $aRawData;

		return $this;
	}
}
