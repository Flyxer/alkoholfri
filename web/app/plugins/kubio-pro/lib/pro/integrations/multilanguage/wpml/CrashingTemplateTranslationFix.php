<?php


namespace Kubio\Wpml;

use Kubio\Core\Utils;

/**
 * For simple templates that don't have translatable content copy the template to bypass the wpml bug that crashes the translation
 * if you try to tranlsate a wp_template that has no translatable content
 */
class CrashingTemplateTranslationFix {

	public static $instance = null;
	const WPML_CHECK_FLAG = 'kubio_run_template_wpml_check';

	protected function __construct() {
		// When certain actions happen that add languages or add templates we must check all the templates
		add_action('wpml_update_active_languages', array($this, 'add_flag_for_checking_wpml_templates'));
		add_action('save_post_wp_template', array($this, 'add_flag_for_checking_wpml_templates'));
		add_action('after_switch_theme',  array($this, 'add_flag_for_checking_wpml_templates'));
		add_action('kubio/plugin_activated',  array($this, 'add_flag_for_checking_wpml_templates'));

		//check on admin visit for the admin flag
		add_action('admin_init', array($this, 'check_wpml_on_admin_visit'));
	}

	public function check_wpml_on_admin_visit() {
		if (is_user_logged_in() && current_user_can('manage_options') && !$this->isAjaxOrRestRequest()) {
			if (get_transient(static::WPML_CHECK_FLAG)) {
				$this->create_duplicate_translation_for_remaining_templates();
			}
		}
	}

	public function isAjaxOrRestRequest() {
		return wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST);
	}

	function add_flag_for_checking_wpml_templates() {
		if(!get_transient(static::WPML_CHECK_FLAG)) {
			set_transient(static::WPML_CHECK_FLAG, true);
		}
	}


	public function create_duplicate_translation_for_remaining_templates() {
		if(!kubio_wpml_is_active()) {
			return;
		}

		// Get the global WPML SitePress object
		global $sitepress;

		// Get all active languages (excluding the default language)
		$languages = $sitepress->get_active_languages();
		$default_language = apply_filters('wpml_default_language', NULL);
		$languages = array_filter($languages, function($lang) use($default_language) {
			return $lang['code'] !== $default_language;
		});

		if(count($languages)  === 0 || !$default_language) {
			return;
		}

		$stylesheet = get_stylesheet();
		$query      = new \WP_Query(
			array(
				'post_type'      => 'wp_template',
				'post_status'    => array( 'publish' ),
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => array( $stylesheet ),
					),
				),
			)
		);
		$templates          = $query->get_posts();
		if(count( $templates )=== 0 ) {
			return;
		}


		foreach ($templates as $template) {
			$template_id = $template->ID;


			// Loop through each language to check for translation
			foreach ($languages as $lang_code => $lang) {
				// Check if the current template has a translation in the current language
				$translation_id = apply_filters('wpml_object_id', $template_id, 'wp_template', false, $lang_code);

				//if it's already translated skip it.
				if ($translation_id) {
					continue;
				}

				$blocks = parse_blocks($template->post_content);


				$can_duplicate_template = true;

				//Only search for templates that have header, footer and post content. So they don't have any translatable content
				Utils::walkBlocks($blocks, function($block) use(&$can_duplicate_template) {
					$allowed_block_names = ['kubio/header','kubio/footer', 'core/post-content', 'kubio/section', 'kubio/row', 'kubio/column', null];
					if(!in_array($block['blockName'], $allowed_block_names)) {
						$can_duplicate_template = false;
					}
				});

				if(!$can_duplicate_template) {
					continue;
				}

				$sitepress->make_duplicate($template_id, $lang_code);
			}
		}

		delete_transient(static::WPML_CHECK_FLAG);
	}



	public static function load() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
