<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\Registry;
use Kubio\Core\Utils;
use Kubio\Core\Styles\FlexAlign;
use Kubio\Core\Layout\LayoutHelper;
use Kubio\Core\StyleManager\DynamicStyles;

/**
 * SliderItemBlock The block that holds the content for each slide
 */
class SliderItemBlock extends BlockContainerBase {
	const BLOCK_NAME = 'kubio/slider-item';

	const OUTER  = 'outer';
	const INNER  = 'inner';
	const ALIGN  = 'align';
	const VSPACE = 'v-space';

	public function mapDynamicStyleToElements() {
		$dynamicStyles            = array();
		$spaceByMedia             = $this->getPropByMedia(
			'vSpace',
			array()
		);
		$dynamicStyles[ self::VSPACE ] = DynamicStyles::vSpace( $spaceByMedia );
		return $dynamicStyles;
	}

	public function mapPropsToElements() {
		$verticalAlignByMedia = $this->getPropByMedia( 'layout.verticalAlign' );

		$itemType = SliderBlock::BLOCK_NAME;
		if ( CarouselItemBlock::BLOCK_NAME === $this->block_type->name ) {
			$itemType = CarouselBlock::BLOCK_NAME;
		}
		$mainBlock         = Registry::getInstance()->getLastBlockOfName( $itemType );
		$mainLayoutByMedia = $mainBlock->getPropByMedia( 'layout' );
		$mainlayoutHelper  = new LayoutHelper( $mainLayoutByMedia );

		// Element classes
		$outer_classes = array_merge( $mainlayoutHelper->getRowGapClasses() );
		$inner_classes = array_merge( $mainlayoutHelper->getColumnInnerGapsClasses() );
		$align_classes = array_merge(
			array( 'h-y-container' ),
			FlexAlign::getVAlignClasses( $verticalAlignByMedia, array( 'self' => true ) )
		);

		$outer = array(
			'className' => $outer_classes,
		);
		$inner = array(
			'className' => $inner_classes,
		);
		$align = array(
			'className' => $align_classes,
		);

		// Add LINK info
		$link           = $this->getAttribute( 'link' );
		$linkAttributes = Utils::getLinkAttributes( $link );

		if ( $linkAttributes['href'] !== '' ) {
			$scriptData   = Utils::useJSComponentProps( 'link', $linkAttributes );
			$fancyboxData = array();
			if ( isset( $linkAttributes['data-default-type'] ) ) {
				$fancyboxData['data-default-type'] = esc_attr($linkAttributes['data-default-type']);
			}
			if ( isset( $linkAttributes['data-fancybox'] ) ) {
				$fancyboxData['data-fancybox'] = esc_attr($linkAttributes['data-fancybox']);
				$fancyboxData['href']          = esc_url($linkAttributes['href']);
			}

			$outer = array_merge(
				$outer,
				$fancyboxData,
				$scriptData
			);
		}

		return array(
			self::OUTER => $outer,
			self::INNER => $inner,
			self::ALIGN => $align,
		);
	}
}

Registry::registerBlock( __DIR__, SliderItemBlock::class );
