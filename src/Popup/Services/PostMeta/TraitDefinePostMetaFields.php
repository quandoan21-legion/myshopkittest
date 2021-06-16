<?php


namespace MyShopKit\Popup\Services\PostMeta;


trait TraitDefinePostMetaFields {
	protected array $aFields = [];

	public function defineFields(): array {
		$this->aFields = [
			'config' => [
				'key'              => 'config',
				'assert'           => [
					'callbackFunc' => 'isJson'
				],
				'sanitizeCallback' => 'sanitize_text_field'
			]
		];

		return $this->aFields;
	}
}
