<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\LodashBasic;
use Kubio\Core\Registry;
use Kubio\Core\Styles\FlexAlign;
use Kubio\Core\Utils;
use Kubio\Core\StyleManager\DynamicStyles;

class FlipBoxBlock extends BlockContainerBase {
	
	const CONTAINER = 'container';
	const NORMAL = 'normal';
	const HOVER = 'hover';



	public function mapPropsToElements() {
		$hoverEffect = $this->getProp( 'hover.effect' );
		$scriptData  = Utils::useJSComponentProps(
			'flipbox',
			array(
				'hoverEffect' => $this->generateHoverEffect( $hoverEffect['type'], $hoverEffect['direction'] ),
				'inEditor'    => false,
			)
		);

		$verticalAlignByMedia = $this->getPropByMedia( 'verticalAlign' );
		$verticalAlignClasses = FlexAlign::getVAlignClasses( $verticalAlignByMedia );

		$map              = array();
		$map['container'] = array_merge( array( 'className' => $verticalAlignClasses ), $scriptData );
		$map['inner']     = array( 'data-inner' => '' );

		return $map;
	}

	private function generateHoverEffect( $animationType, $animationDirection ) {
		return $animationType . $animationDirection;
	}
}


Registry::registerBlock( __DIR__, FlipBoxBlock::class );



