<?php

add_action('admin_init', function(){
	kubio_polylang_translate_frontpage_if_empty();

	foreach ( WP_TEMPLATE_TYPES as $type ) {
		kubio_polylang_check_templates_for_translation($type);
	}

});

// Make templates and template parts translatable
function kubio_add_cpt_to_pll($post_types, $is_settings)
{
	if (kubio_polylang_is_active() && !$is_settings) {
		$post_types['wp_template'] = 'wp_template';
		$post_types['wp_template_part'] = 'wp_template_part';
	}
	return $post_types;
}
add_filter('pll_get_post_types', 'kubio_add_cpt_to_pll', 10, 2);


function kubio_polylang_check_templates_for_translation($post_type = 'wp_template'){
	$default_lang_id = pll_default_language('term_id');
	$language_ids = array_diff(
		pll_languages_list(array(
			'fields' => 'term_id'
		)),
		array(
			$default_lang_id
		)
	);

	$q = new WP_Query(array(
		'post_status' => 'publish',
		'post_type' => $post_type,
		'tax_query' => array(
			array(
				'taxonomy' => 'language',
				'field'    => 'term_id',
				'terms'    => $default_lang_id,
			)
		),
		"posts_per_page" => -1
	));

	$templates = [];

	foreach ($q->posts as $template){
		foreach ($language_ids as $lang_id){
			$language_query = array(
				'taxonomy' => 'language',
				'field'    => 'term_id',
				'terms'    => $lang_id,
			);

			$q = new WP_Query( array(
				"post_status"   => 'publish',
				'post_type'     => $post_type,
				'post_name__in' => [ "{$template->post_name}-" . get_term( $lang_id )->slug ],
				'tax_query'     => array(
					$language_query
				),
				'cache_results' => false
			) );

			if ( ! count( $q->posts ) ) {
				//kubio_polylang_translate_page_template( null, get_term( $lang_id )->slug, $template );
				if ( isset( $templates[ $template->ID ] ) ) {
					$templates[ $template->ID ][] = $lang_id;
				} else {
					$templates[ $template->ID ] = [$lang_id];
				}
			}
		}
	}

	if ( count( $templates ) ) {
		$need_translation = \Kubio\Flags::getSetting( 'pll_templates_need_translation', [] );
		$need_translation[$post_type] = $templates;

		\Kubio\Flags::setSetting( 'pll_templates_need_translation', $need_translation );
	}
}
add_action('admin_init', 'kubio_polylang_check_templates_need_translation');

add_action('admin_notices', function(){
	if ( ! kubio_polylang_is_active() ) {
		return;
	}

	$need_translation = \Kubio\Flags::getSetting('pll_templates_need_translation');
	if ( ! $need_translation ) {
		return;
	}

	ob_start();

	foreach ( WP_TEMPLATE_TYPES as $type ) {
		if ( ! isset( $need_translation[ $type ] ) ) {
			continue;
		}
		$templates = $need_translation[ $type ];
		$slug      = 'template';
		if ( $type === 'wp_template_part' ) {
			$slug = 'template part';
		}

		?>
		<div class="error">
			<?php

			if ( count( $templates ) > 1 ) {
				echo sprintf( "<p>Multiple %ss need translation (%s). ", $slug, count( $templates ) );
				echo sprintf( "<a href='#' class='kubio-polylang-quick-translate' type='%s'>Quick translate</a></p>", $type );
			} else {
				echo sprintf( "<p>One %s needs translation.", $slug );;
			}

			?>
		</div>

		<?php
	}

	?>

	<script type="text/javascript">
		document.addEventListener( 'DOMContentLoaded', () => {
			const buttons = document.querySelectorAll('.kubio-polylang-quick-translate');
			if (buttons) {
				buttons.forEach(btn => {
					const type = btn.getAttribute('type');

					btn.addEventListener('click', async (e) => {
						e.preventDefault();

						await wp.apiFetch({
							path: '/kubio/v1/polylang/quick-translate',
							method: 'POST',
							data: {
								postType: type,
								//_wpnonce: window.kubioUtilsData?.multilanguage?.polylang_add_translation_nonce,
							},
						});

						window.location.reload();
					})
				})
			}
		})
	</script>


	<?php


	echo ob_get_clean();

});

/**
 * Assures the right template in case page has default template
 * @param $query_result
 * @param $query
 * @param $template_type
 *
 * @return array|mixed
 */
function kubio_polylang_get_block_templates_by_language($query_result, $query, $template_type){
	if ( ! kubio_polylang_is_active() ) {
		return $query_result;
	}

	if ( $template_type !== 'wp_template' ) {
		return $query_result;
	}

	$slugs = \IlluminateAgnostic\Arr\Support\Arr::get($query, 'slug__in', []);
	$post_id = null;

	if ( count( $slugs ) ) {
		foreach ( $slugs as $slug ) {
			preg_match( '/-\d+$/', $slug, $matches );
			if ( $matches ) {
				$post_id = intval( $matches[0] ) * - 1;
				break;
			}
		}
	}

	if ( !$post_id ) {
		return $query_result;
	}

	$default_lang = pll_default_language();
	$post_lang = pll_get_post_language( $post_id );
	if ( $default_lang !== $post_lang ) {
		foreach ( $slugs as $key => $slug ) {
			if ( ! preg_match( "/-{$post_lang}$/", $slug ) ) {
				$slugs[ $key ] = sprintf( "%s-%s", $slug, $post_lang );
			}
		}
	}

	$terms = get_the_terms( $post_id, 'language' );
	if ( ! count( $terms ) ) {
		return $query_result;
	}
	$language_query = array(
		'taxonomy' => 'language',
		'field'    => 'term_taxonomy_id',
		'terms'    => $terms[0]->term_taxonomy_id,
	);
	$wp_query_args = array(
		'post_status'         => array('publish' ),
		'post_type'           => 'wp_template',
		'posts_per_page'      => -1,
		'no_found_rows'       => true,
		'lazy_load_term_meta' => false,
		'tax_query'           => array(
			array(
				'taxonomy' => 'wp_theme',
				'field'    => 'name',
				'terms'    => get_stylesheet(),
			),
			$language_query
		),
		'post_name__in' => $slugs
	);
	$template_query = new WP_Query( $wp_query_args );

	// Get default language template if no translated template found
	if ( ! $template_query->posts ) {
		$pll_posts = pll_get_post_translations( $post_id );
		$terms     = get_the_terms( $pll_posts[ $default_lang ], 'language' );

		if ( ! count( $terms ) ) {
			return $query_result;
		}

		\IlluminateAgnostic\Arr\Support\Arr::set($wp_query_args, 'tax_query.1.terms', $terms[0]->term_taxonomy_id);
		$wp_query_args['post_name__in'] = $query['slug__in'];
		$template_query = new WP_Query( $wp_query_args );
	}

	$templates = [];

	foreach ( $template_query->posts as $post ) {
		$templates[] = _build_block_template_result_from_post( $post );
	}

	return $templates;
}
add_filter('get_block_templates', "kubio_polylang_get_block_templates_by_language", 10, 3);

function kubio_frontend_polylang_selector_html()
{

	ob_start();
	?>

	<div id="kubio-language-selector">
		<div class="block-editor-block-list__layout">
			<?php echo kubio_get_polylang_selector_html(); ?>
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

/**
 * Editor Polylang
 * @return false|string
 */
function kubio_get_polylang_selector_html($is_editor = false)
{
	$ml = \Kubio\Flags::getSetting('multilanguage', array(
		"displayAs" => 'flags',
		'showFlags' => true,
		'showNames' => true
	));

	/**
	 * NOTES
	 * data-kubio - is required for styling purposes
	 */
	ob_start();
	?>
	<div class="kubio-language-selector" data-kubio="kubio/language-selector">
		<?php pll_the_languages(
			array(
				'dropdown' => $ml['displayAs'] === 'dropdown',
				"show_flags" => $is_editor || $ml['showFlags'],
				"show_names" => $is_editor || $ml['showNames'],
				'hide_current' => false,
				//'hide_if_no_translation' => true
			)
		) ?>
	</div>
	<?php

	$content = ob_get_clean();
	return $content;
}

function kubio_polylang_translate_frontpage_if_empty(){
	if ( ! kubio_polylang_is_active() ) {
		return;
	}

	$default_lang = pll_default_language();
	$fp_id = get_option('page_on_front');
	$translations = pll_get_post_translations($fp_id);
	unset($translations[$default_lang]);
	$fp = get_post($fp_id);

	foreach ( $translations as $post_id ) {
		$post = get_post( $post_id );
		if ( $post->post_content === '' && $fp->post_content !== '' ) {
			wp_update_post( array(
				'ID'           => $post_id,
				'post_content' => $fp->post_content
			) );
		}
	}
}

function kubio_multilanguage_copy_from_source()
{
	if ( ! kubio_polylang_is_active() ) {
		return;
	}

	global $post;

	$sourceID = isset($_REQUEST['from_post']) ? absint($_REQUEST['from_post']) : null;
	$new_lang = isset($_REQUEST['new_lang']) ? $_REQUEST['new_lang'] : null;
	$slug = pll_default_language('slug');
	$defaultID = pll_get_post($post->ID, $slug);
	$defaultID = $defaultID ? $defaultID : $sourceID;

	if ($post->ID == $defaultID) {
		return;
	}
	$post_data = get_post($defaultID);
	if (!$post_data->post_content || !$new_lang) {
		return;
	}

	?>
	<script>
		function kubio_multilanguage_copy_from_source() {
			var data = {
				action: 'kubio_copy_from_source',
				source_post: <?php echo wp_json_encode($defaultID); ?>,
				destination_post: <?php echo wp_json_encode($post->ID); ?>,
				new_lang: <?php echo wp_json_encode($new_lang); ?>,
				_wpnonce: '<?php echo wp_create_nonce('kubio-copy-from-source-nonce')//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
			};

			var response = confirm("<?php echo __('Current content will be replaced with the content from the primary language. Are you sure ?', 'kubio') //phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction ?>");
			if (response) {
				jQuery.post(ajaxurl, data).done(function (response) {
					setTimeout(function () {
						if (response?.edit_url) {
							window.location.replace(response.edit_url)
						}
					}, 500);
				});
			}
		}
	</script>
	<a href="#" onclick="kubio_multilanguage_copy_from_source()" class="button" style="margin-top: 20px">
		<span class="dashicons dashicons-admin-page"
			  style="padding-top:4px;"></span><?php _e('Copy from primary language', 'kubio') //phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction
		?></a>
	<?php
}
add_action('pll_before_post_translations', 'kubio_multilanguage_copy_from_source');

/**
 * On click "Copy from primary language"
 * TODO: Now, we copy content from currently editing post. Maybe we should copy content from the post with DEFAULT LANGUAGE (Applicable in case of more than 2 languages)
 */
add_action('wp_ajax_kubio_copy_from_source', function () {
	check_ajax_referer('kubio-copy-from-source-nonce', '_wpnonce');

	$source_post_id = isset($_REQUEST['source_post']) ? absint($_REQUEST['source_post']) : null;
	$destination_post_id = isset($_REQUEST['destination_post']) ? absint($_REQUEST['destination_post']) : null;
	$new_lang = isset($_REQUEST['new_lang']) ? $_REQUEST['new_lang'] : null;

	if ($source_post_id) {
		$post_data = get_post($source_post_id);
		$new_post_data = get_post($destination_post_id);

		if ($post_data->post_content !== $new_post_data->post_content) {
			$new_post_data->post_content = $post_data->post_content;
			$new_post_data->post_title = $post_data->post_title . " (" . strtoupper($new_lang) . ")";
			$new_post_data->post_status = 'publish';
			$new_post_data->post_date_gmt = $new_post_data->post_date;

			wp_update_post($new_post_data);

			// Add new translation to post translations
			$languages = pll_get_post_translations($source_post_id);
			$languages[$new_lang] = $new_post_data->ID;
			pll_save_post_translations($languages);
		}
	}

	wp_send_json(array(
		'edit_url' => htmlspecialchars_decode(get_edit_post_link($destination_post_id))
	));
});

/**
 * Remove relation of translation for deleted post
 * @param $post_id
 * @return void
 */
function kubio_remove_translation_link($post_id)
{
	if ( ! kubio_polylang_is_active() ) {
		return;
	}

	$languages = pll_get_post_translations($post_id);
	$languages = array_filter($languages, function ($lang_post_id) use ($post_id) {
		return $lang_post_id !== $post_id;
	});
	pll_save_post_translations($languages);

}
add_action('wp_trash_post', "kubio_remove_translation_link", 10, 3);

/**
 * Duplicate page translation
 * @param $post_id
 * @param $new_lang
 * @return int|void|WP_Error
 */
function kubio_polylang_translate_page($post_id, $new_lang)
{
	if ( ! kubio_polylang_is_active() ) {
		return;
	}

	$slug = pll_default_language();
	$default_post_id = pll_get_post($post_id, $slug);
	$default_post = get_post($default_post_id);

	// Prepare post to duplicate
	unset($default_post->ID);
	unset($default_post->guid);
	$post_array = $default_post->to_array();

	$new_post_id = wp_insert_post(
		array_merge(
			$post_array,
			array(
				'post_title' => sprintf("%s %s", $default_post->post_title, strtoupper($new_lang)),
				'post_name' => sprintf("%s-%s", $default_post->post_name, $new_lang),
			)
		)
	);

	// Add new translation to post translations
	$languages = pll_get_post_translations($post_id);
	$languages[$new_lang] = $new_post_id;
	pll_set_post_language($new_post_id, $new_lang);
	pll_save_post_translations($languages);

	return $new_post_id;
}

/**
 * Translate template from page or object
 * @param $post_id
 * @param $new_lang
 * @param $template WP_Post|int|null
 *
 * @return array|null
 */
function kubio_polylang_translate_page_template($post_id, $new_lang, $template = null)
{
	if ( ! kubio_polylang_is_active() ) {
		return null;
	}

	if ( ! $template ) {
		$template_slug = get_post_meta( $post_id, '_wp_page_template', true );
		if ( $template_slug === 'default' ) {
			return null;
		}

		$q = new WP_Query( array(
			'post_type'   => 'wp_template',
			'post_status' => 'publish',
			'name'        => $template_slug
		) );

		if ( count( $q->posts ) ) {
			$template = $q->posts[0];
		} else {
			return null;
		}
	} else if ( gettype( $template ) === 'integer' ) {
		$template = get_post( $template );
	}

	$template_array = $template->to_array();
	unset( $template_array['ID'] );
	unset( $template_array['guid'] );

	$new_slug     = sprintf( "%s-%s", $template_array['post_name'], $new_lang );
	$q = new WP_Query( array(
		'post_type'   => $template->post_type,
		'post_status' => 'publish',
		'name'        => $new_slug
	) );

	if ( count($q->posts) ) {
		return array(
			'id'   => $q->posts[0]->ID,
			'slug' => $q->posts[0]->post_name
		);
	}

	$theme           = get_stylesheet();
	$new_template_id = wp_insert_post(
		array_merge(
			$template_array,
			array(
				'post_title' => sprintf( "%s %s", $template_array['post_title'], strtoupper( $new_lang ) ),
				'post_name'  => $new_slug,
				'tax_input'  => array(
					'wp_theme' => array( $theme ),
				),
			)
		)
	);

	// Add new translation to template translations
	$languages = pll_get_post_translations($template->ID);
	$languages[$new_lang] = $new_template_id;
	pll_set_post_language($new_template_id, $new_lang);
	pll_save_post_translations($languages);

	return array(
		'id' => $new_template_id,
		'slug' => $new_slug
	);
}

/**
 * Duplicate template-parts translation from page
 * @param $post_id
 * @param $new_lang
 * @return void
 */
function kubio_polylang_translate_page_template_parts($post_id, $new_lang)
{
	if ( ! kubio_polylang_is_active() ) {
		return null;
	}

	$theme = get_stylesheet();
	$post = get_post($post_id);
	$url = add_query_arg('_wp-find-template', "true", $post->guid);

	$res = wp_remote_get($url);
	$data = wp_remote_retrieve_body($res);
	$body = json_decode($data);

	$blocks = parse_blocks($body->data->content);

	foreach ($blocks as $block) {
		// Not kubio block
		if (strpos($block['blockName'], 'kubio') === false) {
			continue;
		}

		$block_parts = explode('/', $block['blockName']);
		$template_part_slug = array_pop($block_parts);

		$q = new WP_Query(array(
			'post_type' => 'wp_template_part',
			'post_status' => 'publish',
			'name' => $template_part_slug
		));
		if ( $q->posts ) {
			$template_part = $q->posts[0];
		} else {
			continue;
		}

		$new_template_part_slug = sprintf("%s-%s", $template_part_slug, $new_lang);
		$q = new WP_Query(array(
			'post_type' => 'wp_template_part',
			'post_status' => 'publish',
			'name' => $new_template_part_slug
		));

		// Template-part for new language already exists
		if ( count( $q->posts ) ) {
			continue;
		}


		$part_array = $template_part->to_array();
		unset($part_array['ID']);

		$new_part_id = wp_insert_post(
			array_merge(
				$part_array,
				array(
					'post_title' => sprintf("%s %s", $template_part->post_title, strtoupper($new_lang)),
					'post_name' => $new_template_part_slug,
					'tax_input' => array(
						'wp_theme' => array($theme),
					),
				)
			)
		);

		// Add new translation to template-part translations
		$languages = pll_get_post_translations($template_part->ID);
		$languages[$new_lang] = $new_part_id;
		pll_set_post_language($new_part_id, $new_lang);
		pll_save_post_translations($languages);
	}
}
