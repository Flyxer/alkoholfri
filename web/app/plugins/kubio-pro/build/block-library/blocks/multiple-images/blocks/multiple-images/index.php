<?php
namespace Kubio\Blocks;
use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Registry;

class MultipleImagesBlock extends BlockBase {

	const OUTER = 'outer';
	const INNER = 'inner';

	public function mapPropsToElements() {
	}
}
Registry::registerBlock(
	__DIR__,
	MultipleImagesBlock::class,
	array(
		'metadata' => 'block.json',
	)
);
