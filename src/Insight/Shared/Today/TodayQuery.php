<?php

namespace MyShopKit\Insight\Shared\Today;

use MyShopKit\Insight\Shared\Query\QueryBuilder;
use MyShopKit\Insight\Shared\TraitJoinPost;


class TodayQuery extends QueryBuilder {
	use TraitJoinPost;

	public function select(): TodayQuery {
		$this->setWhat()->setJoin()->setWhere();
		$this->groupBy = "DATE(createdDate)";

		return $this;
	}

	public function setWhat(): QueryBuilder {
		$this->aSelectWhat = [ "DATE(createdDate) as date", "SUM(total) as summary" ];

		return $this;
	}

	public function setWhere(): QueryBuilder {
		$this->aWhere[] = "(DATE(createdDate) = CURDATE())";

		return $this;
	}
}
