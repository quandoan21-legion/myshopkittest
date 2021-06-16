<?php


namespace MyShopKit\Shared\Post\Query;


use MyShopKit\Shared\AutoPrefix;
use WP_Post;

class PostSkeleton {
	private WP_Post $oPost;
	protected array $aPluck
		= [
			'id',
			'title',
			'date',
			'config',
			'status',
			'views',
			'clicks',
			'subscribers',
			'rate',
			'goal'
		];

	private function sanitizePluck( $rawPluck ): array {
		$aPluck = is_array( $rawPluck ) ? $rawPluck : explode( ',', $rawPluck );

		return array_map( function ( $pluck ) {
			return trim( $pluck );
		}, $aPluck );
	}

	public function checkMethodExists( $pluck ): bool {
		$method = 'get' . ucfirst( $pluck );

		return method_exists( $this, $method );
	}

	public function getTitle(): string {
		return $this->oPost->post_title;
	}

	public function getStatus(): string {
		return ( $this->oPost->post_status == 'publish' ) ? 'active' : 'deactive';
	}

	/**
	 * @return false|string
	 */
	public function getDate() {
		return date( get_option( 'date_format' ), strtotime( $this->oPost->post_date ) );
	}

	public function getConfig(): string {
		return get_post_meta( $this->oPost->ID, AutoPrefix::namePrefix( 'config' ), true ) ?? '';
	}

	public function getId(): string {
		return (string) $this->oPost->ID;
	}

	public function setPost( WP_Post $oPost ): PostSkeleton {
		$this->oPost = $oPost;

		return $this;
	}

	public function getPostData( $pluck, array $aAdditionalInfo = [] ): array {
		$aData = [];

		if ( empty( $pluck ) ) {
			$aPluck = $this->aPluck;
		} else {
			$aPluck = $this->sanitizePluck( $pluck );
		}

		foreach ( $aPluck as $pluck ) {
			$method = 'get' . ucfirst( $pluck );
			if ( method_exists( $this, $method ) ) {
				$aData[ $pluck ] = call_user_func_array( [ $this, $method ], [ $aAdditionalInfo ] );
			}
		}

		return $aData;
	}
}
