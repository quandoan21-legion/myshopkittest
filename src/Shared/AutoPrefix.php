<?php

namespace MyShopKit\Shared;

class AutoPrefix {
	public static function namePrefix( $name ) {
		return strpos( $name, MYSHOPKIT_PREFIX ) === 0 ? $name : MYSHOPKIT_PREFIX . $name;
	}
}
