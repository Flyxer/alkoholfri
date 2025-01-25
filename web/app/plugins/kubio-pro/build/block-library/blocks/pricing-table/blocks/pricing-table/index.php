<?php

namespace Kubio\Blocks;

use Kubio\Core\Registry;

class PricingTableBlock extends RowBlock {

	public function mapPropsToElements() {
		$map = parent::mapPropsToElements();

		return $map;
	}
}

Registry::registerBlock(
	__DIR__,
	PricingTableBlock::class,
	array(
		'metadata'        => '../../../row/block.json',
		'metadata_mixins' => array( './block.json' ),
	)
);
