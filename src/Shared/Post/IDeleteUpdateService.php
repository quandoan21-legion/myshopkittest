<?php


namespace MyShopKit\Shared\Post;


interface IDeleteUpdateService {
	public function setID( $id ): self;

	public function isPostAuthor( $id ): bool;
}
