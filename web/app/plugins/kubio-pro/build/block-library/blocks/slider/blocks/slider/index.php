<?php

namespace Kubio\Blocks;

use Kubio\AssetsDependencyInjector;
use Kubio\Core\Blocks\BlockContainerBase;
use Kubio\Core\Registry;
use Kubio\Core\Utils;
use Kubio\Core\StyleManager\DynamicStyles;


/**
 * SliderBlock Main component
 * Will hold the slider item wrapper block and the slider navigation (both arrows and dots)
 */
class SliderBlock extends BlockContainerBase {
	const BLOCK_NAME         = 'kubio/slider';
	const OUTER              = 'outer';
	const CONTAINER          = 'container';






	public function mapPropsToElements() {
		$slider          = $this->getProp( 'slider' );
		$slides_per_view = $this->getPropByMedia( 'slider.slidesPerView' );

		$options = array(
			'swiperSelector' => '.swiper-container',
			'slideSelector'  => '.swiper-slide',
			'slidesPerView'  => $slides_per_view['desktop'],
			'effect'         => $this->getProp( 'transition.effect' ),
			'loop'           => $slider['loop'],
			'speed'          => (int) $this->getProp( 'transition.speed' ),
			'navigation'     => (bool) $this->getProp( 'navigation.arrows.enabled' ),
			'pagination'     => (bool) $this->getProp( 'navigation.dots.enabled' ),
			'breakpoints'    => array(
				767  => array(
					'slidesPerView' => (int) $slides_per_view['mobile'],
				),
				1023 => array(
					'slidesPerView' => (int) $slides_per_view['tablet'],
				),
				1024 => array(
					'slidesPerView' => (int) $slides_per_view['desktop'],
				),
			),
		);

		// AUTOPLAY
		if ( true === $slider['autoPlay']['enabled'] ) {
			$options['autoplay']            = array();
			$options['autoplay']['enabled'] = true;
			$options['autoplay']['delay']   = (int) $slider['autoPlay']['speed'];

			if ( 'right' === $slider['autoPlay']['direction'] ) {
				$options['autoplay']['reverseDirection'] = true;
			}
			$options['autoplay']['disableOnInteraction'] = false;
			$options['autoplay']['pauseOnMouseEnter']    = (bool) $slider['pauseOnHover'];

			if ( false === $slider['loop'] ) {
				$options['autoplay']['stopOnLastSlide'] = true;
			}
		} else {
			$options['autoplay'] = false;
		}

		// LOOP
		if ( true === $slider['loop'] ) {
			$options['loopAdditionalSlides'] = 20;
		}

		// EFFECTS
		if ( $options['effect'] === 'coverflow' ) {
			$options['coverflowEffect'] = array(
				'slideShadows' => false,
				'rotate'       => (int) $this->getProp( 'transition.coverflow.rotate' ),
				'stretch'      => (int) $this->getProp( 'transition.coverflow.stretch' ),
				'depth'        => (int) $this->getProp( 'transition.coverflow.depth' ),
				'modifier'     => (int) $this->getProp( 'transition.coverflow.modifier' ),
			);
		}

		// NAVIGATION
		if ( $options['navigation'] ) {
			$options['navigation']           = array();
			$options['navigation']['nextEl'] = '.swiper-button-next';
			$options['navigation']['prevEl'] = '.swiper-button-prev';
		}

		// PAGINATION
		if ( $options['pagination'] ) {
			$options['pagination']              = array();
			$options['pagination']['el']        = '.swiper-pagination';
			$options['pagination']['type']      = 'bullets';
			$options['pagination']['clickable'] = true;
		}

		$scriptData = Utils::useJSComponentProps(
			'slider',
			$options
		);

		$outerClasses = array();
		if ( $this->getProp( 'kenBurns.enabled' ) ) {
			array_push( $outerClasses, 'ken-burns-effect' );
		}

		AssetsDependencyInjector::injectKubioFrontendStyleDependencies( 'swiper' );
		AssetsDependencyInjector::injectKubioScriptDependencies( 'swiper' );

		return array(
			self::OUTER => array_merge( array( 'className' => $outerClasses ), $scriptData ),
		);
	}
}

Registry::registerBlock( __DIR__, SliderBlock::class );
