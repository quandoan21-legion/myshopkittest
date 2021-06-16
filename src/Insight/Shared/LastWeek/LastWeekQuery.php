<?php

namespace MyShopKit\Insight\Shared\LastWeek;

use MyShopKit\Insight\Shared\Query\QueryBuilder;
use MyShopKit\Insight\Shared\TraitJoinPost;

class LastWeekQuery extends QueryBuilder {
	use TraitJoinPost;

	public function select(): QueryBuilder {
		$this->setWhat()->setWhere()->setJoin();
		$this->groupBy = "DATE(createdDate)";

		return $this;
	}

	public function setWhat(): QueryBuilder {
		$this->aSelectWhat[] = "DATE(createdDate) as date";
		$this->aSelectWhat[] = "SUM(total) as summary";

		return $this;
	}

	public function setWhere(): QueryBuilder {
		$this->aWhere[] = '(YEARWEEK(createdDate,7) = YEARWEEK(CURDATE(),7)-1)';

		return $this;
	}
}
