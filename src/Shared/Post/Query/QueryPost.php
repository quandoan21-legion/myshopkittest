<?php


namespace MyShopKit\Shared\Post\Query;


use MyShopKit\Illuminate\Message\MessageFactory;

class QueryPost {
	protected array      $aArgs    = [];
	private array        $aRawArgs = [];
	protected string     $postType = '';
	protected ?\WP_Query $oQuery;

	private function defineArgs(): array {
		return [
			'ids'    => 0,
			'id'     => 0,
			'limit'  => 10,
			'paged'  => 1,
			'author' => 0,
			's'      => '',
			'status' => 'any'
		];
	}

	public function setRawArgs( array $aRawArgs ): IQueryPost {
		$this->aRawArgs = $aRawArgs;

		return $this;
	}

	public function commonParseArgs(): array {
		$this->aArgs = shortcode_atts( $this->defineArgs(), $this->aRawArgs );

		if ( isset( $this->aArgs['status'] ) && ! empty( $this->aArgs['status'] ) ) {
			if ( $this->aArgs['status'] != 'any' ) {
				$this->aArgs['post_status'] = $this->aArgs['status'] == 'active' ? 'publish' : 'draft';
			}
			unset( $this->aArgs['status'] );
		} else {
			$this->aArgs['post_status'] = 'any';
		}

		if ( isset( $this->aArgs['limit'] ) && $this->aArgs['limit'] <= 30 ) {
			$this->aArgs['posts_per_page'] = $this->aArgs['limit'];
			unset( $this->aArgs['limit'] );
		} else {
			$this->aArgs['posts_per_page'] = 30;
		}

		if ( isset( $aRawArgs['page'] ) && $aRawArgs['page'] ) {
			$this->aArgs['paged'] = $aRawArgs['page'];
		} else {
			$this->aArgs['paged'] = 1;
		}

		if ( empty( $this->aArgs['s'] ) ) {
			unset( $this->aArgs['s'] );
		}

		if (!empty( $this->aArgs['ids'])) {
			$this->aArgs['post__in'] = explode( ',', $this->aArgs['ids']);
		} else if (!empty( $this->aArgs['id'])) {
			$this->aArgs['p'] = $this->aArgs['id'];
		}

		unset( $this->aArgs['ids']);
		unset( $this->aArgs['id']);

		return $this->aArgs;
	}

	/**
	 * @param PostSkeleton $oPostSkeleton
	 * @param string $pluck
	 * @param bool $isSingle
	 *
	 * @return array
	 */
	public function query( PostSkeleton $oPostSkeleton, string $pluck = '', bool $isSingle = false ): array {
		$this->oQuery = new \WP_Query( $this->aArgs );

		$aResponse['maxPages'] = 0;

		if ( ! $this->oQuery->have_posts() ) {
			wp_reset_postdata();
			return MessageFactory::factory()->success(
				esc_html__( 'We found no items', 'myshopkit' ),
				$aResponse
			);
		}

		$aItems = [];
		while ( $this->oQuery->have_posts() ) {
			$this->oQuery->the_post();

			$aItems[] = $oPostSkeleton->setPost( $this->oQuery->post )->getPostData( $pluck );
		}

		$aResponse['maxPages'] = $this->oQuery->max_num_pages;
		if ( !$isSingle ) {
			$aResponse['items'] = $aItems;
		} else {
			$aResponse = array_merge( $aItems[0], $aResponse );
		}

		if ($isSingle) {
			unset( $aResponse['maxPages']);
		}

		return MessageFactory::factory()->success(
			sprintf( esc_html__( 'We found %s items', 'myshopkit' ), count( $aItems ) ),
			$aResponse
		);
	}

	public function getArgs(): array {
		return $this->aArgs;
	}
}
