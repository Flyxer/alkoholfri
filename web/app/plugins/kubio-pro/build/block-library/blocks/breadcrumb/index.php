<?php

namespace Kubio\Blocks;

use Kubio\Core\Blocks\BlockBase;
use Kubio\Core\Registry;
use WP_Query;
class BreadcrumbBlock extends BlockBase {
	public $modifiedFlags = array( 'is_page', 'is_home', 'is_single', 'is_singular' );
	public $backupFlags   = array();
	public $idCSS         = '';
	const OUTER           = 'outer';

	public function generateBreadcrumbCSSClass() {
		if ( ! isset( $this->idCSS ) || $this->idCSS === '' ) {
			if ( ! isset( $this->block_data['attrs']['kubio']['id'] ) ) {
				$this->block_data['attrs']['kubio']['id'] = rand( 20, 1000 );
			}
			$block_id    = $this->block_data['attrs']['kubio']['id'];
			$this->idCSS = 'breadcrumb-css-' . $block_id;
		}
	}

	public function serverSideRender() {
		$content = $this->getContent();

		return $content;
	}

	public function mapPropsToElements() {
		$content = $this->getContent();

		return array(
			self::OUTER => array(
				'innerHTML' => $content,
				'className' => $this->idCSS,
			),
		);
	}

	public function getContent() {
		$this->generateBreadcrumbCSSClass();

		ob_start();
		$this->printSeparatorInlineStyle();
		?>
		<div class="breadcrumb-items__wrapper <?php echo esc_attr( $this->idCSS ); ?>">
			<?php
				$this->maybePrintPrefix();
				$this->setupWpQuery();
				$this->printBreadcrumb();
				$this->resetWpQuery();
			?>
		</div>
		<?php
		$content = ob_get_clean();
		return $content;
	}

	public function printBreadcrumb() {
		$breadcrumb_before                = '<ol class="breadcrumb kubio-breadcrumb">';
		$breadcrumb_after                 = '</ol>';
		$breadcrumb_element_before        = '<li class="breadcrumb-item">';
		$breadcrumb_element_before_active = '<li class="breadcrumb-item current">';
		$breadcrumb_element_after         = '</li>';
		$breadcrumb_element_link_before   = '<a href="%s">';
		$breadcrumb_element_link_after    = '</a>';
		$breadcrumb_elements              = $this->getBreadcrumbElements();
		$output                           = '';
		$output                          .= $breadcrumb_before;
		if ( ! empty( $breadcrumb_elements ) ) {
			$i     = 1;
			$count = count( $breadcrumb_elements );

			foreach ( $breadcrumb_elements as $breadcrumb_element ) {
				$output .= ( $i === $count ) ? $breadcrumb_element_before_active : $breadcrumb_element_before;
				if ( ! empty( $breadcrumb_element['href'] ) ) {
					$output .= sprintf( $breadcrumb_element_link_before, esc_url($breadcrumb_element['href']) );
				} else {
					$output .= '<span>';
				}
				$output .= wp_kses_post($breadcrumb_element['text']);
				if ( ! empty( $breadcrumb_element['href'] ) ) {
					$output .= $breadcrumb_element_link_after;
				} else {
					$output .= '</span>';
				}
				$output .= $breadcrumb_element_after;
				$i++;
			}
		}
		$output .= $breadcrumb_after;
		echo $output;
	}

	public function getBreadcrumbElements() {
		global $wp_query, $post;
		$elements = array();
		$postId   = $this->getAttribute( 'postId' );
		$postType = $this->getAttribute( 'postType' );
		// will be used for blog posts, or blog archive pages
		$blogPageId  = get_option( 'page_for_posts' );
		$blogElement = array(
			'href' => get_permalink( $blogPageId ),
			'text' => get_the_title( $blogPageId ),
		);

		// The "root" element
		$homeAsIcon = ! ! $this->getAttribute( 'home.isIcon', 0 );
		$homeIcon   = $this->getAttribute( 'home.iconName', 'font-awesome/home' );
		if ( $homeAsIcon && is_string( $homeIcon ) ) {
			$iconFolderName = explode( '/', $homeIcon );
			$svg_file       = ( KUBIO_ROOT_DIR . 'static/icons/' . sanitize_file_name( $iconFolderName[0] ) . '/' . sanitize_file_name( $iconFolderName[1] ) . '.svg' );
			if ( file_exists( $svg_file ) ) {
				$svg = file_get_contents( $svg_file );
			}
		}
		$elements['home'] = array(
			'href' => home_url( '/' ),
			'text' => ( $homeAsIcon ? $svg : $this->getAttribute( 'home.label', __( 'Home', 'kubio' ) ) ),
		);

		// parent pages if it's a page
		if ( $wp_query->is_page ) {
			$ancestors = get_post_ancestors( $postId );
			if ( ! empty( $ancestors ) ) {
				$ancestors = array_reverse( $ancestors );
				foreach ( $ancestors as $ancestor ) {
					$elements[ 'pages-' . $ancestor ] = array(
						'href' => get_permalink( $ancestor ),
						'text' => get_the_title( $ancestor ),
					);
				}
			}
		}

		// if it's a post add the blog and the post
		if ( $wp_query->is_singular && ! is_front_page() ) {
			if ( ( ! empty( $postType ) && $postType === 'post' ) || $post->post_type === 'post' ) {
				$elements['blog'] = $blogElement;
			}
			$elements['active'] = array(
				'href' => '',
				'text' => get_the_title( $postId ),
			);
		}

		// if it's the blog page
		if ( $wp_query->is_posts_page ) {
			$elements['active'] = array(
				'href' => '',
				'text' => get_the_title( $blogPageId ),
			);
		}

		// tax
		if ( $wp_query->is_tax ) {
			$elements['active'] = array(
				'href' => '',
				'text' => single_term_title( '', false ),
			);
		}

		// category
		if ( $wp_query->is_category ) {
			$elements['blog']   = $blogElement;
			$elements['active'] = array(
				'href' => '',
				'text' => single_cat_title( '', false ),
			);
		}

		// tag
		if ( $wp_query->is_tag ) {
			$elements['active'] = array(
				'href' => '',
				'text' => single_tag_title( '', false ),
			);
		}

		// tag
		if ( $wp_query->is_date ) {
			$elements['blog']   = $blogElement;
			$elements['active'] = array(
				'href' => '',
				'text' => get_the_archive_title(),
			);
		}

		// custom post type
		if ( $wp_query->is_post_type_archive ) {
			$elements['active'] = array(
				'href' => '',
				'text' => get_the_archive_title(),
			);
		}

		if ( is_tax( 'post_format' ) ) {
			$elements['active'] = array(
				'href' => '',
				'text' => get_the_archive_title(),
			);
		}

		// author
		if ( $wp_query->is_author ) {
			$elements['active'] = array(
				'href' => '',
				'text' => get_the_author_meta( 'display_name' ),
			);
		}

		// search
		if ( $wp_query->is_search ) {
			$elements['active'] = array(
				'href' => '',
				// translators: %s - search term
				'text' => sprintf( __( 'Search Results for &#8220;%s&#8221;', 'kubio' ), get_search_query() ),
			);
		}

		// 404
		if ( $wp_query->is_404 ) {
			$elements['active'] = array(
				'href' => '',
				'text' => __( 'Page not found', 'kubio' ),
			);
		}
		return $elements;
	}

	public function setupWpQuery() {
		global $wp_query, $post;
		if ( $this->getAttribute( 'isEditor' ) ) {
			$postType    = $this->getAttribute( 'postType' );
			$postId      = $this->getAttribute( 'postId' );
			$wp_query    = new WP_Query(
				array(
					'post_type' => $postType,
					'p'         => $postId,
				)
			);
			$frontPageId = get_option( 'page_on_front' );
			if ( $frontPageId == $postId ) {
				$wp_query->is_home     = true;
				$wp_query->is_single   = false;
				$wp_query->is_singular = false;
			}
			if ( $postType === 'page' && ! $wp_query->is_home ) {
				$wp_query->is_page = true;
			}
		}
	}

	public function resetWpQuery() {
		global $wp_query, $post;
		if ( ! $this->getAttribute( 'isEditor' ) ) {
			//restore wp_query flags
			foreach ( $this->modifiedFlags as $flag ) {
				if ( ! empty( $wp_query->{$flag} ) && ! empty( $this->backupFlags[ $flag ] ) ) {
					$wp_query->{$flag} = $this->backupFlags[ $flag ];
				}
			}
		}
	}

	public function maybePrintPrefix() {
		if ( $this->getAttribute( 'usePrefix', '' ) ) {
			$prefix = kubio_wpml_get_translated_string($this->getAttribute( 'prefix', __( 'You are here:', 'kubio' ) ));
			$prefix = str_replace( ' ', '&nbsp;', $prefix );
			?>
				<span class="breadcrumb-items__prefix"><?php echo wp_kses_post( $prefix ); ?></span>
			<?php
		}
	}

	public function printSeparatorInlineStyle() {
		$separatorSymbol = urldecode( $this->getAttribute( 'separatorSymbol', '/' ) );
		?>
		<style type="text/css">
			/* breadcrumb separator symbol */
			.wp-block .breadcrumb-items__wrapper.<?php echo esc_attr( $this->idCSS ); ?> .kubio-breadcrumb > li + li:before {
				content: "<?php echo esc_html( $separatorSymbol ); ?>";
				white-space: pre;
			}
		</style>
		<?php
	}
}

Registry::registerBlock(
	__DIR__,
	BreadcrumbBlock::class
);

