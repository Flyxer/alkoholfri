<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Registry;
use Kubio\Core\Utils;
use Kubio\Core\Styles\FlexAlign;


/**
 * CarouselBlock Main component
 * Will hold the carousel item wrapper block and the carousel navigation (both arrows and dots)
 */
class CarouselBlock extends SliderBlock {
	const BLOCK_NAME = 'kubio/carousel';
}

Registry::registerBlock(
	__DIR__,
	CarouselBlock::class,
	array(
		'metadata'        => '../slider/blocks/slider/block.json',
		'metadata_mixins' => array( 'block.json' ),
	)
);


