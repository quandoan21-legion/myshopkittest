<?php


namespace MyShopKit\Insight\Shared\Yesterday;

use MyShopKit\Insight\Shared\Response\ReportResponse;

class YesterdayResponse extends ReportResponse
{

	private function getTimeline(): array {
		$summary = isset( $this->aRawData[0]) && isset( $this->aRawData[0]['summary'] ) ? (int) $this->aRawData[0]['summary']
			: 0;

		return [
			'timeline' => [],
			'summary' => $summary
		];
	}

	public function parseData(): array {
		return $this->getTimeline();
	}
}
