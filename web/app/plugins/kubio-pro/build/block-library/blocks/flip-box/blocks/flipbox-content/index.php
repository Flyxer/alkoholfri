<?php
namespace Kubio\Blocks;
use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\Registry;
use Kubio\Core\StyleManager\DynamicStyles;
use Kubio\Core\Styles\FlexAlign;

class FlipBoxContent extends BlockContainerBase {

	const VSPACE = 'v-space';

	public function mapDynamicStyleToElements() {
		$dynamicStyles                 = array();
		$spaceByMedia                  = $this->getPropByMedia(
			'vSpace',
			array()
		);
		$dynamicStyles[ self::VSPACE ] = DynamicStyles::vSpace( $spaceByMedia );

		return $dynamicStyles;
	}

	public function mapPropsToElements() {
		$verticalAlignByMedia = $this->getPropByMedia( 'layout.verticalAlign' );
		$verticalAlignClasses = FlexAlign::getVAlignClasses( $verticalAlignByMedia, array( 'self' => true ) );

		$map              = array();
		$map['inner']     = array( 'className' => $verticalAlignClasses );
		$map['container'] = array( 'data-normal' => '' );

		return $map;
	}
}

Registry::registerBlock(
	__DIR__,
	FlipBoxContent::class
);
