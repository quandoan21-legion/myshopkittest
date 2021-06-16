<?php

namespace MyShopKit\Shared\Validation;

use Exception;
use MyShopKit\Illuminate\Message\MessageFactory;
use Webmozart\Assert\Assert;

/**
 * Class Validation
 *
 * @template T
 */
class Validation {
	private static array  $aValidator    = [];
	private static array  $aData         = [];
	private static array  $aConditionals = [];
	private static string $message       = '';

	private static function maybeHasRequiredInRule( $aRules, $name, array $aData ): bool {
		$requiredPos = array_search( 'required', $aRules );
		if ( $requiredPos === false ) {
			return true;
		}

		return isset( $aData[ $name ] );
	}

	/**
	 * @throws Exception
	 */
	public static function allKeyExistsInArray( array $aValues, $aKeys ): bool {
		foreach ( $aKeys as $key ) {
			if ( ! isset( $aValues[ $key ] ) ) {
				throw new \Exception( sprintf( esc_html__( 'The key %s is required.', 'myshopkit' ), $key ) );
			}
		}

		return true;
	}

	/**
	 * @param $aValue ['username' => 'wiloke', 'id' => 1]
	 * @param $aRules ['username' => 'string', 'id' => 'int']
	 *
	 * @throws Exception
	 */
	public static function validArrayValue( $aValue, $aRules ): array {
		$aResponse = self::make( $aValue, $aRules );
		if ( $aResponse['status'] == 'error' ) {
			throw new \Exception( $aResponse['message'] );
		}

		return $aResponse;
	}

	private static function isWithoutCompareGroup( $method ) {
		$response = false;
		switch ( $method ) {
			case 'notEmpty':
			case 'isEmpty':
			case 'true':
			case 'false':
			case 'notFalse':
			case 'string':
			case 'numeric':
			case 'null':
			case 'email':
			case 'stringNotEmpty':
				$response = [ '\Webmozart\Assert\Assert', $method ];
				break;
			case 'json':
				$response = [ __CLASS__, 'isJSON' ];
				break;
		}

		return $response;
	}

	private static function renderString( $input ): string {
		return is_array( $input ) ? var_export( $input, true ) : $input;
	}

	/**
	 * @throws Exception
	 */
	public static function make( array $aData, array $aConditionals ): array {
		self::$aConditionals = $aConditionals;

		try {
			foreach ( self::$aConditionals as $dataKey => $aRules ) {
				if ( ! self::maybeHasRequiredInRule( $aRules, $dataKey, $aData ) ) {
					throw new \Exception( sprintf( esc_html__( 'The %s is required', 'myshopkit' ), $dataKey ) );
				}

				$value = $aData[ $dataKey ] ?? '';
				foreach ( $aRules as $rule ) {
					if ( $rule === 'required' ) {
						continue;
					}

					self::$message = sprintf(
						esc_html__( 'The %s key must be %s. The current value is %s ', 'myshopkit' ),
						self::renderString( $dataKey ),
						self::renderString( $rule ),
						self::renderString( $value )
					);

					if ( is_array( $rule ) ) {
						if ( ! isset( $rule['callback'] ) ) {
							throw new \Exception(
								sprintf( esc_html__( 'Sorry, but Rule %s does not exist', 'myshopkit' ), var_export(
									$rule, true ) )
							);
						}

						call_user_func( $rule['callback'], $rule['value'] ?? $value, $rule['compare'], self::$message );
					} else {
						if ( $callback = self::isWithoutCompareGroup( $rule ) ) {
							call_user_func( $callback, $value, self::$message );
						}
					}
				}
			}
		}
		catch ( \Exception $oException ) {
			return MessageFactory::factory()->error(
				$oException->getMessage(),
				400
			);
		}

		return MessageFactory::factory()->success(
			esc_html__( 'The data have been validated', 'myshopkit' ),
			$aData
		);
	}

	public static function registerValidator( $name, $validateFuncOrClass ): array {
		self::$aValidator[ $name ] = $validateFuncOrClass;

		return self::$aValidator;
	}

	/**
	 * @throws \Exception
	 */
	public function isJson( string $value ): bool {
		json_decode( $value );
		if ( json_last_error() === JSON_ERROR_NONE ) {
			return true;
		}

		throw new \Exception( esc_html__( 'The data is not json format', 'myshopkit' ) );
	}
}
