<?php
namespace Kubio\Blocks;
use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\Registry;
use Kubio\Core\StyleManager\DynamicStyles;
use Kubio\Core\Styles\FlexAlign;
use Kubio\Core\Utils;


class FlipBoxHover extends BlockContainerBase {

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

		$link           = $this->getAttribute( 'link' );
		$linkAttributes = Utils::getLinkAttributes( $link );
		$map            = array();

		$map['inner'] = array(
			'className' => $verticalAlignClasses,
		);

		if ( $linkAttributes['href'] != '' ) {
			$scriptData   = Utils::useJSComponentProps( 'link', $linkAttributes );
			$fancyboxData = array();
			if ( isset( $linkAttributes['data-default-type'] ) ) {
				$fancyboxData['data-default-type'] = esc_attr($linkAttributes['data-default-type']);
			}
			if ( isset( $linkAttributes['data-fancybox'] ) ) {
				$fancyboxData['data-fancybox'] = esc_attr($linkAttributes['data-fancybox']);
				$fancyboxData['href']          = esc_url($linkAttributes['href']);
			}

			$map['container'] = array_merge(
				array(
					'data-hover' => '',
				),
				$fancyboxData,
				$scriptData
			);
		} else {
			$map['container'] = array( 'data-hover' => '' );
		}

		return $map;
	}
}

Registry::registerBlock(
	__DIR__,
	FlipBoxHover::class
);
