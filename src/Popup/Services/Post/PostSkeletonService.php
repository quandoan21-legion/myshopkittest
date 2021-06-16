<?php

namespace MyShopKit\Popup\Services\Post;

use MyShopKit\Shared\Post\Query\PostSkeleton;

class PostSkeletonService extends PostSkeleton {
	public function getViews(): int {
		// loading
		return 11;
	}

	public function getClicks(): int {
		// loading
		return 11;
	}

	public function getSubscribers(): int {
		return 10;
	}

	public function getConversation(): int {
		return 10;
	}

	public function getGoal(): string {
		$aConfig = json_decode( $this->getConfig(), true );

		return $aConfig['goal'] ?? '';
	}
}
