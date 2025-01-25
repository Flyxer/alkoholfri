<?php

namespace Kubio\Blocks;

use Kubio\Core\Layout\LayoutHelper;
use Kubio\Core\LodashBasic;
use Kubio\Core\Registry;

class PricingTableItemBlock extends ColumnBlock {
	const RIBBON              = 'ribbon';
	const RIBBON_WRAPPER      = 'ribbon-wrapper';
	const RIBBON_ICON         = 'ribbon-icon';
	const RIBBON_TEXT         = 'ribbon-text';
	const TRIANGLE_TOP        = 'triangle-top';
	const RIBBON_TEXT_OUTSIDE = 'ribbon-text-outside';

	public function mapPropsToElements() {
		//      $map = parent::mapPropsToElements();
		$ribbonAttribute = $this->getAttribute( 'ribbon' );
		$ribbonPosition  = $this->getProp( 'ribbon.position' );
		// @TODO Fix the column width values since the `parent` will calculate row values, but we need pricing-table
		$row_block          = Registry::getInstance()->getLastBlockOfName( 'kubio/pricing-table' );
		$columnWidthByMedia = $this->getStyleByMedia(
			'columnWidth',
			array(),
			array(
				'styledComponent' => self::CONTAINER,
				'local'           => true,
			)
		);
		$layoutByMedia      = $this->getPropByMedia( 'layout' );
		$rowLayoutByMedia   = $row_block->getPropByMedia( 'layout' );
		$columnWidth        = $columnWidthByMedia['desktop'];
		$layoutHelper       = new LayoutHelper( $layoutByMedia, $rowLayoutByMedia );

		$container_cls = LodashBasic::concat(
			$layoutHelper->getColumnLayoutClasses( $columnWidthByMedia ),
			$layoutHelper->getInheritedColumnVAlignClasses()
		);

		$equalWidth = LodashBasic::get( $rowLayoutByMedia, 'desktop.equalWidth', false );

		$align_cls = LodashBasic::concat(
			$layoutHelper->getColumnContentFlexBasis( $equalWidth, $columnWidth ),
			$layoutHelper->getSelfVAlignClasses()
		);

		$inner = $layoutHelper->getColumnInnerGapsClasses();

		$map[ self::CONTAINER ] = array( 'className' => $container_cls );
		$map[ self::INNER ]     = array( 'className' => $inner );
		$map[ self::ALIGN ]     = array( 'className' => $align_cls );

		$map[ self::RIBBON ] = array(
			'className' => array( 'price-ribbon--' . $ribbonPosition ),
		);

		$map[ self::RIBBON_ICON ] = array(
			'name' => $ribbonAttribute['icon'],
		);

		$map[ self::RIBBON_TEXT ] = array(
			'innerHTML' => wp_kses_post($ribbonAttribute['text']),
		);

		return $map;
	}

	public function computed() {
		$ribbonProp = $this->getProp( 'ribbon' );

		return array(
			'isRibbonText' => $ribbonProp['type'] === 'text',
			'isRibbonIcon' => $ribbonProp['type'] === 'icon',
		);
	}
}

Registry::registerBlock(
	__DIR__,
	PricingTableItemBlock::class,
	array(
		'metadata'        => '../../../column/block.json',
		'metadata_mixins' => array( './block.json' ),
	)
);
