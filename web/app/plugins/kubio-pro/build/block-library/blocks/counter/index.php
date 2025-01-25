<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Registry;
use Kubio\Core\Utils as CoreUtils;

class CounterBlock extends BlockBase {
	const OUTER                  = 'outer';
	const COUNTER                = 'counter';
	const COUNTER_CONTAINER      = 'counterContainer';
	const NUMBER                 = 'number';
	const TITLE                  = 'title';
	const ICON                   = 'icon';
	const PROGRESS_BAR_CONTAINER = 'progressBarContainer';
	const PROGRESS_BAR           = 'progressBar';
	const TITLE_COUNTER_BAR      = 'titleCounterBar';

	public function computed() {
		$type            = $this->getProp( 'counterType' );
		$counterPosition = $this->getProp( 'counterPosition' );

		return array(
			'color'                => $this->getProp( 'colorIn' ),
			'showIcon'             => filter_var( $this->getProp( 'icon.enabled' ), FILTER_VALIDATE_BOOLEAN ), // Returns TRUE for "1", "true", "on" and "yes"
			'showNumber'           => 'number' === $type,
			'showBar'              => 'bar' === $type,
			'showCircle'           => 'circle' === $type,
			'showCounterWithTitle' => 'same' === $counterPosition,
			'showCounterInside'    => 'inside' === $counterPosition || 'hide' === $counterPosition,
		);
	}

	public function mapPropsToElements() {
		$type            = $this->getProp( 'counterType' );
		$titlePosition   = $this->getProp( 'titlePosition' );
		$counterPosition = $this->getProp( 'counterPosition' );
		$progress        = $this->getAttribute( 'progress' );

		//icon name should be a attribute so when multiple counters are linked you can change the icons individually.
		$iconName = $this->getAttribute( 'iconName' );
		$title    = $this->getAttribute( 'title' );

		$width             = $this->getProp( 'width' );
		$height            = $this->getProp( 'height' );
		$animationDuration = $this->getProp( 'animationDuration' );
		$color             = $this->getProp( 'colorIn' );
		$colorOut          = $this->getProp( 'colorOut' );

		$circleProps = array(
			'progress'  => $progress['value'],
			'size'      => $width['value'],
			'fill'      => array(
				'color' => $color,
			),
			'emptyFill' => $colorOut,
			'animation' => array(
				'duration' => (int) $animationDuration['value'] * 1000,
			),
		);

		$titlePositionClass = sprintf( 'kubio-counter-title--%s', $titlePosition );
		$typeClass          = sprintf( 'kubio-counter-type--%s', $type );

		$counterClassName      = array();
		$jsCounterProps        = array();
		$outerClassnames       = array( $titlePositionClass, $typeClass );
		$counterContainerStyle = array();

		if ( $type === 'number' ) {
			$startValue = $this->getAttribute( 'start' );
			$endValue   = $this->getAttribute( 'final' );
		} else {
			$startValue = 0;
			$endValue   = $this->getAttribute( 'amount' );
		}

		switch ( $type ) {
			case 'circle':
				$jsCounterProps['circle'] = $circleProps;
				break;
			case 'bar':
				$counterContainerStyle = array(
					'width' => esc_attr($width['value']) . esc_attr($width['unit']),
				);

				$outerClassnames[] = sprintf( 'kubio-bar-counter-position--%s', $counterPosition );

				if ( $counterPosition === 'hide' ) {
					array_push( $counterClassName, 'd-none' );
				}

				break;
			default:
				break;
		}

		$jsCounterProps = array_merge(
			array(
				'countup'         => 'true',
				'type'            => $type,
				'min'             => $startValue,
				'max'             => $endValue,
				'duration'        => $animationDuration['value'],
				'decimals'        => '0', // unhandled yet.
				'separator'       => $this->getSeparatorSign(),
				'prefix'          => $this->getAttribute( 'prefix', '' ),
				'suffix'          => $this->getAttribute( 'suffix', '' ),
				'titlePosition'   => $titlePosition,
				'counterPosition' => $counterPosition,
			),
			$jsCounterProps
		);

		$jsCounterProps = CoreUtils::useJSComponentProps( 'counter', $jsCounterProps );

		return array(
			self::OUTER                  => array(
				'className' => $outerClassnames,
			),

			self::ICON                   => array( 'name' => $iconName ),

			self::TITLE                  => array(
				'innerHTML' => wp_kses_post($title),
			),

			self::COUNTER_CONTAINER      => array(
				'style' => $counterContainerStyle,
			),

			self::COUNTER                => array_merge(
				array(
					'innerHTML' => wp_kses_post($startValue),
					'className' => $counterClassName,
				),
				$jsCounterProps
			),

			self::PROGRESS_BAR_CONTAINER => array(
				'style' => array(
					'background-color' => esc_attr($colorOut),
					'height'           => esc_attr($height['value']) . 'px',
				),
			),

			self::PROGRESS_BAR           => array(
				'aria-valuemin' => esc_attr($startValue),
				'aria-valuemax' => esc_attr($progress['value']),
				'style'         => array(
					'background-color'   => esc_attr($color),
					'width'              => esc_attr($progress['value']) . '%',
					'animation-duration' => esc_attr($animationDuration['value']) . 's',
				),
			),

		);
	}


	/**
	 * Get the Separator sign based on the `separator` attribute value mapped with the keys from config.js
	 *
	 * @return string
	 */
	private function getSeparatorSign() {
		$sign = '';

		$separator = $this->getAttribute( 'separator' );

		switch ( $separator ) {
			case 'comma':
				$sign = ',';
				break;
			case 'dot':
				$sign = '.';
				break;
			case 'space':
				$sign = ' ';
				break;
			default:
				break;
		}

		return $sign;
	}
}

Registry::registerBlock( __DIR__, CounterBlock::class );
