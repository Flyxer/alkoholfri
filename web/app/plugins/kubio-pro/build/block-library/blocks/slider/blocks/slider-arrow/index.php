<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\Registry;
use Kubio\Core\Utils;
use Kubio\Core\Styles\FlexAlign;

/**
 * SliderArrowBlock The slider navigation: ARROW
 */
class SliderArrowBlock extends BlockBase {

	const OUTER = 'outer';
	const INNER = 'inner';
	const ICON  = 'icon';

	public function __construct( $block, $autoload = true ) {
		parent::__construct( $block, $autoload );
	}

	public function computed() {
		$parent_block = Registry::getInstance()->getLastBlockOfName( array( 'kubio/slider', 'kubio/carousel' ) );

		if ( null !== $parent_block ) {
			return array(
				'arrowsEnabled' => $parent_block->getProp( 'navigation.arrows.enabled' ),
			);
		} else {
			return array( 'arrowsEnabled' => false );
		}
	}

	public function mapPropsToElements() {
		$direction = $this->getAttribute( 'direction' );

		$verticalAlignByMedia = $this->getPropByMedia( 'verticalAlign' );
		$verticalAlignClasses = FlexAlign::getVAlignClasses( $verticalAlignByMedia, array( 'self' => false ) );

		$horizontalAlignByMedia = $this->getPropByMedia( 'horizontalAlign' );
		$horizontalAlignClasses = FlexAlign::getHAlignClasses( $horizontalAlignByMedia, array( 'self' => false ) );

		return array(
			self::OUTER => array( 'className' => implode( ' ', array_merge( $verticalAlignClasses, $horizontalAlignClasses ) ) ),
			self::INNER => array(
				'className' => 'swiper-button-' . $direction,
			),
		);
	}
}


Registry::registerBlock( __DIR__, SliderArrowBlock::class );
