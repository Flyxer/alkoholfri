<?php

namespace Kubio\Blocks;

use Kubio\Core\Registry;
/**
 * CarouselItemBlock The block that holds the content for each slide
 */
class CarouselItemBlock extends SliderItemBlock {
	const BLOCK_NAME = 'kubio/carousel-item';
}

Registry::registerBlock(
	__DIR__,
	CarouselItemBlock::class,
	array(
		'metadata'        => '../../../slider/blocks/slider-item/block.json',
		'metadata_mixins' => array( 'block.json' ),
	)
);

