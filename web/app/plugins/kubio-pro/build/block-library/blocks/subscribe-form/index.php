<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\LodashBasic;
use Kubio\Core\Registry;
use Kubio\Core\Styles\Utils as StylesUtils;
use Kubio\Core\Utils;

class SubscribeFormBlock extends BlockBase {

	const CONTAINER   = 'container';
	const PLACEHOLDER = 'placeholder';

	public function computed() {
		$shortcode = $this->getAttribute( 'shortcode' );
		return array(
			'renderContainer'   => ! ! $shortcode,
			'renderPlaceholder' => ! $shortcode,
		);
	}

	public function mapPropsToElements() {
		$shortcode   = wp_kses_post( $this->getAttribute( 'shortcode' ) );
		$content     = null;
		$placeholder = null;
		if ( $shortcode ) {
			$content = \kubio_newsletter_shortcode( $this->getShortcodeAttributes() );
		} else {
			$placeholder = Utils::getEmptyShortcodePlaceholder();
		}

		$layoutClasses = array();
		if ( ! $this->getAttribute( 'useShortcodeLayout' ) ) {
			$layoutClasses = $this->getLayoutClasses();
		} else {
			$layoutClasses = array( 'kubio-newsletter--shortcode-layout' );
			$alignByMedia  = $this->getPropByMedia( 'form.submitButton.align' );
			$layoutClasses = array_merge( $layoutClasses, $this->getSubmitButtonAlignClasses( $alignByMedia ) );
		}
		return array(
			self::CONTAINER   => array(
				'innerHTML' => $content,
				'className' => $layoutClasses,
			),
			self::PLACEHOLDER => array(
				'innerHTML' => $placeholder,
			),
		);
	}

	public function getShortcodeAttributes() {
		$formData = $this->getProp( 'form' );
		$atts     = array(
			'shortcode'               => $this->getAttribute( 'shortcode' ),
			'use_shortcode_layout'    => $this->getAttribute( 'useShortcodeLayout' ) ? 1 : 0,
			'email_label'             => LodashBasic::get( $formData, 'email.label' ),
			'email_placeholder'       => LodashBasic::get( $formData, 'email.placeholder' ),
			'submit_button_label'     => LodashBasic::get( $formData, 'submitButton.label' ),
			'submit_button_use_icon'  => LodashBasic::get( $formData, 'submitButton.icon.enabled' ) ? 1 : 0,
			'submit_button_icon_name' => LodashBasic::get( $formData, 'submitButton.icon.name' ),
			'submit_button_position'  => LodashBasic::get( $formData, 'submitButton.position' ),
			'use_agree_terms'         => LodashBasic::get( $formData, 'agreeTerms.enabled' ) ? 1 : 0,
			'agree_terms_label'       => LodashBasic::get( $formData, 'agreeTerms.label' ),
			'decode_data'             => 0,

		);
		return $atts;
	}

	public function getLayoutClasses() {
		$positionByMedia   = $this->getPropByMedia( 'form.submitButton.position' );
		$emailWidthByMedia = $this->getPropByMedia( 'form.email.widthType' );

		$submitButtonPositionClasses = $this->getFormLayoutClasses(
			$positionByMedia
		);
		$emailWidthTypeClasses       = $this->getEmailWidthTypeClasses(
			$emailWidthByMedia
		);

		return array_merge( $submitButtonPositionClasses, $emailWidthTypeClasses );
	}

	public function getFormLayoutClasses( $valueByMedia ) {
		return $this->getClassesWithGridByMedia( 'submit-button--', $valueByMedia );
	}
	public function getSubmitButtonAlignClasses( $valueByMedia ) {
		return $this->getClassesWithGridByMedia( 'submit-button-align--', $valueByMedia );
	}
	public function getEmailWidthTypeClasses( $valueByMedia ) {
		return $this->getClassesWithGridByMedia(
			'kubio-newsletter-email--',
			$valueByMedia
		);
	}
	public function getClassesWithGridByMedia( $baseClass, $positionByMedia ) {
		$classes = array();
		foreach ( $positionByMedia as $media => $position ) {
			$layoutClass = sprintf( '%s%s', $baseClass, $position );
			$gridPrefix  = StylesUtils::getMediaPrefix( $media );
			$prefix      = $gridPrefix ? '-' . $gridPrefix : '';
			$classes[]   = sprintf( '%s%s', $layoutClass, $prefix );
		}
		return $classes;
	}

}

Registry::registerBlock(
	__DIR__,
	SubscribeFormBlock::class
);

