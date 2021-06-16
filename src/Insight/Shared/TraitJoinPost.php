<?php


namespace MyShopKit\Insight\Shared;


use MyShopKit\Insight\Shared\Query\QueryBuilder;

trait TraitJoinPost {
	public function setJoin(): QueryBuilder {
		global $wpdb;
		$this->join = " JOIN " . $wpdb->posts . " as posts ON (posts.ID = tblTarget.postID)";

		return $this;
	}
}
