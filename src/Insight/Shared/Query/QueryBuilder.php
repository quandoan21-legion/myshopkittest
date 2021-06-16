<?php

namespace MyShopKit\Insight\Shared\Query;


use MyShopKit\Insight\Shared\Response\ReportResponse;

abstract class QueryBuilder {
	protected string $tableName = '';
	protected string $join      = '';
	protected string $groupBy   = '';

	/**
	 * @var string[]
	 */
	protected array $aWhere = ["posts.post_status='publish'"];

	/**
	 * @var string[]
	 */
	protected array $aSelectWhat = [];

	private array $aDefaultResults
		= [
			[
				'date' => 0,
				'sum'  => 0
			]
		];

	private ?string $what;
	private ?string $where;
	private string  $postType;
	protected array  $aAdditional;


	public function setTable( $tableName ): QueryBuilder {
		$this->tableName = $tableName;

		return $this;
	}
    public function setPostID( $postID ): QueryBuilder {
	    if (!empty($postID)){
            $this->aWhere[] = "tblTarget.postID=".$postID."";
        }
        return $this;
    }
    public function setAdditional( array $aAdditional ): QueryBuilder {
        $this->aAdditional = $aAdditional;

        return $this;
    }

	public abstract function setWhat(): QueryBuilder;

	public abstract function setWhere(): QueryBuilder;

	public abstract function select(): QueryBuilder;

	public abstract function setJoin(): QueryBuilder;

	public function setPostType( string $postType ): QueryBuilder {
		global $wpdb;
		$this->postType = $postType;
		$this->aWhere[] = $wpdb->prepare(
			"posts.post_type=%s",
			$this->postType
		);

		return $this;
	}
	private function buildWhere(): ?string {
		if ( ! empty( $this->aWhere ) ) {
			$this->where = trim( implode( " AND ", $this->aWhere ), " AND " );
		}

		return $this->where;
	}

	private function buildWhat(): ?string {
		if ( ! empty( $this->aSelectWhat ) ) {
			$this->what = trim( implode( ", ", $this->aSelectWhat ), ", " );
		}

		return $this->what;
	}

	public function buildSql(): string {
		return trim(
			sprintf(
				"SELECT %s FROM %s %s %s %s",
				$this->buildWhat() ?? '*',                                                      // select what
				$this->tableName . " as tblTarget",                                             // join
				$this->join,                                                                    // on
				$this->buildWhere() ? " WHERE " . $this->where : '',
				$this->groupBy ? 'GROUP BY ' . $this->groupBy : ''
			)
		);
	}

	public function query( ReportResponse $oReportResponse ): array {
		global $wpdb;
		$aData = ! empty( $aResults = $wpdb->get_results( $this->buildSql(), ARRAY_A ) ) ? $aResults :
			$this->aDefaultResults;

		return $oReportResponse->setRawData( $aData )->parseData();
	}
}
