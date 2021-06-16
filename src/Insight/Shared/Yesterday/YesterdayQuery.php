<?php

namespace MyShopKit\Insight\Shared\Yesterday;


use MyShopKit\Insight\Shared\Query\QueryBuilder;
use MyShopKit\Insight\Shared\TraitJoinPost;

class YesterdayQuery extends QueryBuilder {
	use TraitJoinPost;

	public function select(): YesterdayQuery {
		$this->setWhat();
		$this->setWhere();
		$this->setJoin();

		return $this;
	}

	public function setWhat(): QueryBuilder {
		$this->aSelectWhat[] = "DATE(createdDate) as date, SUM(total) as summary";

		return $this;
	}

	public function setWhere(): QueryBuilder {
		$this->aWhere[] = "(DATE(createdDate) = DATE(CURDATE() - INTERVAL 1 DAY))";

		return $this;
	}
}
