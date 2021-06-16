<?php


namespace MyShopKit\Popup\Services\Post;


use MyShopKit\Illuminate\Message\MessageFactory;
use MyShopKit\Shared\AutoPrefix;
use MyShopKit\Shared\Post\Query\IQueryPost;
use MyShopKit\Shared\Post\Query\PostSkeleton;
use MyShopKit\Shared\Post\Query\QueryPost;

class PopupQueryService extends QueryPost implements IQueryPost {
	public function getPostType(): string {
		$this->postType = AutoPrefix::namePrefix( 'popup' );

		return $this->postType;
	}

	public function parseArgs(): IQueryPost {
		$this->aArgs              = $this->commonParseArgs();
		$this->aArgs['post_type'] = $this->getPostType();

		return $this;
	}
}
