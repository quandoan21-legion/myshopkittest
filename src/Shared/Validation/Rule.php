<?php

namespace MyShopKit\Shared\Validation;

use Webmozart\Assert\Assert;

class Rule {
	public static array $aArray = [];

	public static function inArray( array $aArray ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'inArray' ],
			'compare'  => $aArray
		];
	}

	/**
	 * Kiem tra gia tri cua mang. Vi du co mang ['timeline' => ['from' => 1, 'to' => 2]], ham nay giup kiem value cua
	 * timeline
	 *
	 * @param array $aValueTypes
	 *
	 * @return array
	 */
	public static function validArrayValue( array $aValueTypes ): array {
		return [
			'callback' => [ 'MyShopKit\Shared\Validation\Validation', 'validArrayValue' ],
			'compare'  => $aValueTypes
		];
	}

	/**
	 * Kiem tra de chac chan rang tat ca key ton tai trong array
	 *
	 * @param array $aKeys
	 *
	 * @return array
	 */
	public static function allKeyExistsInArray( array $aKeys ): array {
		return [
			'callback' => [ '\MyShopKit\Shared\Validation\Validation', 'allKeyExistsInArray' ],
			'compare'  => $aKeys
		];
	}

	public static function eq( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'eq' ],
			'compare'  => $value
		];
	}

	public static function same( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'same' ],
			'compare'  => $value
		];
	}

	public static function notEq( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'notEq' ],
			'compare'  => $value
		];
	}

	public static function greaterThan( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'greaterThan' ],
			'compare'  => $value
		];
	}

	public static function greaterThanEq( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'greaterThanEq' ],
			'compare'  => $value
		];
	}

	public static function lessThan( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'lessThan' ],
			'compare'  => $value
		];
	}

	public static function lessThanEq( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'lessThanEq' ],
			'compare'  => $value
		];
	}

	public static function count( $value ): array {
		return [
			'callback' => [ '\Webmozart\Assert\Assert', 'count' ],
			'compare'  => $value
		];
	}
}
