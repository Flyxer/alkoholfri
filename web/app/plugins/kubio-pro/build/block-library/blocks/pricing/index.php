<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\Registry;
use Kubio\Core\Styles\FlexAlign;
use IlluminateAgnostic\Str\Support\Arr;

class PricingBlock extends BlockContainerBase {

	private $preserveWhiteSpace = array();
	private $currencyFormat     = 'formatOne';
	const CONTAINER             = 'container';


	const CURRENT_PRICE   = 'currentPrice';
	const CURRENT_DECIMAL = 'currentDecimal';
	const CURRENT_SYMBOL  = 'currentSymbol';


	const ORIGINAL_PRICE         = 'originalPrice';
	const ORIGINAL_PRICE_DECIMAL = 'originalPriceDecimal';
	const ORIGINAL_PRICE_SYMBOL  = 'originalPriceSymbol';
	const ORIGINAL_PRICE_INNER   = 'originalPriceInner';

	public function mapPropsToElements() {
		$this->preserveWhiteSpace = $this->getAttribute( 'preserveSpace' );
		$this->currencyFormat     = $this->getAttribute( 'currencyFormat' );

		$currentPrice  = $this->formatPrice( $this->getAttribute( 'currentPrice' ) );
		$originalPrice = $this->formatPrice( $this->getAttribute( 'originalPrice', 'original' ) );

		return array(
			self::ORIGINAL_PRICE         => array(
				'className' => FlexAlign::getVAlignClasses( $this->getPropByMedia( 'verticalAlign.original.price' ), array( 'self' => true ) ),
			),

			self::ORIGINAL_PRICE_INNER   => array(
				'innerHTML' => wp_kses_post($originalPrice['value']),
			),

			self::ORIGINAL_PRICE_DECIMAL => array(
				'innerHTML' => wp_kses_post($originalPrice['decimal']),
				'className' => FlexAlign::getVAlignClasses( $this->getPropByMedia( 'verticalAlign.original.decimal' ), array( 'self' => true ) ),
			),

			self::ORIGINAL_PRICE_SYMBOL  => array(
				'innerHTML' => wp_kses_post($this->getCurrentSymbol()),
				'className' => FlexAlign::getVAlignClasses( $this->getPropByMedia( 'verticalAlign.original.symbol' ), array( 'self' => true ) ),
			),

			self::CURRENT_SYMBOL         => array(
				'innerHTML' => wp_kses_post($this->getCurrentSymbol()),
				'className' => FlexAlign::getVAlignClasses( $this->getPropByMedia( 'verticalAlign.current.symbol' ), array( 'self' => true ) ),
			),
			self::CURRENT_PRICE          => array( 'innerHTML' => wp_kses_post($currentPrice['value']) ),
			self::CURRENT_DECIMAL        => array(
				'innerHTML' => wp_kses_post($currentPrice['decimal']),
				'className' => FlexAlign::getVAlignClasses( $this->getPropByMedia( 'verticalAlign.current.decimal' ), array( 'self' => true ) ),
			),
		);
	}

	public function computed() {
		return array(
			'sale' => $this->getAttribute( 'sale' ),
		);
	}

	public function preserveWhiteSpaces( $price ) {
		return str_replace( ' ', '&nbsp;', $price );
	}

	private function getCurrentSymbol() {
		$currentSymbol       = $this->getAttribute( 'currentSymbol' );
		$currentSymbolCustom = $this->getAttribute( 'customSymbol' );

		return $this->formatSymbol( $currentSymbol, $currentSymbolCustom );
	}

	private function formatSymbol( $symbol, $custom = '' ) {
		switch ( $symbol ) {
			case 'dollar':
				return '$';
			case 'euro':
				return '€';
			case 'pound':
				return '£';
			case 'custom':
				return $custom;
			default:
				return '$';
		}
	}//-formatSymbol()

	private function formatPrice( $price, $path = 'current' ) {
		$delimiter = '.';
		$decimal   = '';
		$value     = '';

		if ( isset( $this->preserveWhiteSpace[ $path ] ) ) {
			$price = $this->preserveWhiteSpaces( $price );
		}

		$procPrice     = explode( $delimiter, $price );
		$main_value    = Arr::get( $procPrice, 0 );
		$decimal_value = Arr::get( $procPrice, 1 );
		if ( $this->currencyFormat === 'formatOne' ) {
			if ( $decimal_value ) {
				$decimal = '.' . $decimal_value;
			}
			$value = preg_replace( '/\B(?=(\d{3})+(?!\d))/', ',', $main_value );
		} else {
			if ( $decimal_value ) {
				$decimal = ',' . $decimal_value;
			}
			$value = preg_replace( '/\B(?=(\d{3})+(?!\d))/', '.', $main_value );
		}

		return array(
			'value'   => $value,
			'decimal' => $decimal,
		);
	}
}

Registry::registerBlock(
	__DIR__,
	PricingBlock::class
);
