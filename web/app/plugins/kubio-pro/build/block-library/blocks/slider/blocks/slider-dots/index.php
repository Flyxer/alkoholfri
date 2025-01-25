<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\Registry;
use Kubio\Core\Utils;
use Kubio\Core\Styles\FlexAlign;
use Kubio\Core\LodashBasic;

/**
 * SliderDotsBlock The slider navigation: DOTS
 */
class SliderDotsBlock extends BlockBase {

	const OUTER = 'outer';
	const INNER = 'inner';

	public function __construct( $block, $autoload = true ) {
		parent::__construct( $block, $autoload );
	}

	public function computed() {
		$parent_block = Registry::getInstance()->getLastBlockOfName( array( 'kubio/slider', 'kubio/carousel' ) );
		if ( null !== $parent_block ) {
			return array(
				'dotsEnabled' => $parent_block->getProp( 'navigation.dots.enabled' ),
			);
		} else {
			return array( 'dotsEnabled' => false );
		}
	}

	public function mapPropsToElements() {
		$verticalAlignByMedia   = $this->getPropByMedia( 'layout.verticalAlign' );
		$horizontalAlignByMedia = $this->getPropByMedia( 'layout.horizontalAlign' );
		$verticalAlignClasses   = FlexAlign::getVAlignClasses( $verticalAlignByMedia, array( 'self' => false ) );
		$horizontalAlignClasses = FlexAlign::getHAlignClasses( $horizontalAlignByMedia, array( 'self' => false ) );

		return array(
			self::OUTER => array( 'className' => LodashBasic::concat( $horizontalAlignClasses, $verticalAlignClasses ) ),
			self::INNER => array( 'className' => array() ),
		);
	}
}

Registry::registerBlock( __DIR__, SliderDotsBlock::class );
