<?php
namespace Kubio\Blocks;
use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Registry;

class MultipleImagesPictureBlock extends ImageBlock {
}




Registry::registerBlock(
	__DIR__,
	MultipleImagesPictureBlock::class,
	array(
		'metadata'        => '../../../image/block.json',
		'metadata_mixins' => array( 'block.json' ),
	)
);

