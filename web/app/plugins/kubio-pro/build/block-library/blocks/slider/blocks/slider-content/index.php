<?php

namespace Kubio\Blocks;

use Kubio\Core\Registry;
use Kubio\Core\LodashBasic;
use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Layout\LayoutHelper;

/**
 * SliderContentBlock Wrapper block that holds all the slider items
 */
class SliderContentBlock extends BlockBase {

	const OUTER = 'outer';
	const INNER = 'inner';

	public function __construct( $block, $autoload = true ) {
		parent::__construct( $block, $autoload );
	}

	public function mapPropsToElements() {
		$parent        = Registry::getInstance()->getParentBlock();
		$layoutByMedia = $parent->getPropByMedia( 'layout' );
		$layoutHelper  = new LayoutHelper( $layoutByMedia );
		$contentAlign  = $layoutHelper->getRowAlignClasses();

		return array(
			self::OUTER => array(
				'className' => LodashBasic::concat( array() ),
			),
			self::INNER => array(
				'className' => LodashBasic::concat(
					$contentAlign
				),
			),
		);
	}
}

Registry::registerBlock( __DIR__, SliderContentBlock::class );
