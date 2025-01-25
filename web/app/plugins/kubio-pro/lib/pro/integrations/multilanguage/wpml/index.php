<?php


function kubio_wpml_html() {
	//  if (kubio_is_page_preview()) {
	//      return;
	//  }

	if ( kubio_wpml_is_active() ) {
		echo kubio_frontend_wpml_selector_html();
	}
}

if ( is_admin() ) {
	//add_action('admin_footer', 'kubio_multilanguage_html');
} else {
	add_action( 'wp_footer', 'kubio_wpml_html' );
}

// Include WPML language-switchers styles in editor
add_action(
	'kubio/editor/enqueue_assets',
	function () {
		if ( ! kubio_wpml_is_active() ) {
			return;
		}
		$styles_to_copy = array(
			'legacy-dropdown',
			'legacy-dropdown-click',
			'legacy-list-horizontal',
			'legacy-list-vertical',
			'legacy-post-translations',
			'menu-item',
		);
		$base_dir       = plugins_url( 'sitepress-multilingual-cms/templates/language-switchers' );

		foreach ( $styles_to_copy as $style ) {
			$style_entry = "{$base_dir}/{$style}/style.min.css";
			$handle      = "kubio-copy-of-wpml-{$style}";
			wp_enqueue_style( $handle, $style_entry );
		}
	}
);

function kubio_frontend_wpml_selector_html() {
	ob_start();
	?>

	<div id="kubio-language-selector">
		<div class="block-editor-block-list__layout">
			<?php echo kubio_get_wpml_selector_html(); ?>
		</div>
	</div>

	<script type="text/javascript">
		const selector = document.getElementById("lang_choice_1");
		if (selector) {
			selector.value = window.location.href;
		}
	</script>
	<?php

	$content = ob_get_clean();
	return $content;
}

function kubio_wpml_get_post_translations( $post_id ): array {
	if ( ! $post_id ) {
		return array();
	}

	$element_type = apply_filters( 'wpml_element_type', get_post_type( $post_id ) );
	$trid         = apply_filters( 'wpml_element_trid', null, $post_id, $element_type );

	return (array) apply_filters( 'wpml_get_element_translations', array(), $trid, $element_type );
}

/**
 * Editor WPML
 * @return false|string
 */
function kubio_get_wpml_selector_html( $is_editor = false ) {

	ob_start();
	?>
	<div class="kubio-language-selector" data-kubio="kubio/language-selector">
		<?php
		do_action(
			'wpml_language_switcher',
			array(
				'flags'      => true,
				'translated' => 0,
				'native'     => 1,
			)
		);
		?>
	</div>

	<?php

	$content = ob_get_clean();
	return $content;
}



/**
 * Fallback logic if the template is not translated use the default one without language logic
 */
add_filter(
	'get_block_templates',
	function( $query_result, $query, $template_type ) {
		//return $query_result;
		if ( ! kubio_wpml_is_active() || $template_type !== 'wp_template' || isset( $query['kubio_tried_wpml_template'] ) ) {
			return $query_result;
		}
		//  $is_page_query = in_array('page', LodashBasic::get($query, 'slug__in', []));
		//  if(!$is_page_query) {
		//      return $query_result;
		//  }
		if ( empty( $query_result ) ) {

			global $wpml_query_filter;
			$merged_query = array_merge( array( 'kubio_tried_wpml_template' => true ), $query );

			$should_add_filter_back = false;
			if ( has_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ) ) ) {
				$should_add_filter_back = true;
				remove_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10 );
				remove_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10 );
			}

			$templates = get_block_templates( $merged_query, $template_type );
			if ( $should_add_filter_back ) {
				add_filter( 'posts_join', array( $wpml_query_filter, 'posts_join_filter' ), 10, 2 );
				add_filter( 'posts_where', array( $wpml_query_filter, 'posts_where_filter' ), 10, 2 );
			}
			return $templates;
		}

		return $query_result;
	},
	10,
	3
);



//register block attribute defaults strings as single string.
//This is needed for translating attributes default in certain blocks as wpml does not detect them
add_action(
	'init',
	function() {
		if ( ! kubio_wpml_is_active() ) {
			return;
		}

		$block_defaults = require_once __DIR__ . '/block-defaults-strings.php';
		foreach ( $block_defaults as $block_class => $strings_to_translate ) {
			foreach ( $strings_to_translate as $string_to_translate ) {
				do_action( 'wpml_register_single_string', KUBIO_WPML_BLOCK_DEFAULTS_ID, $string_to_translate, $string_to_translate );
			}
		}
	}
);




require_once __DIR__ . '/CrashingTemplateTranslationFix.php';

Kubio\Wpml\CrashingTemplateTranslationFix::load();
