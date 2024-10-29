<?php
/**
 * class-affiliate-content.php
 *
 * Copyright (c) 2014 "kento" Karim Rahimpur www.itthinx.com
 *
 * This code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This header and all notices must be kept intact.
 *
 * @author Karim Rahimpur
 * @package affiliate
 * @since 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content transformer.
 */
class Affiliate_Content {

	/**
	 * Filter priority for the_excerpt hook.
	 *
	 * @var int
	 */
	const THE_EXCERPT_FILTER_PRIORITY = 99999;

	/**
	 * Filter priority for the_content hook.
	 *
	 * @var int
	 */
	const THE_CONTENT_FILTER_PRIORITY = 99999;

	/**
	 * Setup.
	 */
	public static function init() {
		// transform excerpts
		if ( apply_filters( 'affiliate_filter_the_excerpt', true ) ) {
			add_filter( 'the_excerpt', array(__CLASS__, 'the_excerpt' ), self::THE_EXCERPT_FILTER_PRIORITY );
		}
		// transform post contents
		if ( apply_filters( 'affiliate_filter_the_content', true ) ) {
			add_filter( 'the_content', array(__CLASS__, 'the_content' ), self::THE_CONTENT_FILTER_PRIORITY );
		}
	}

	/**
	 * Excerpt filter, hooked on the_excerpt, invokes the transformer.
	 *
	 * @param string $content
	 *
	 * @return string filtered content
	 */
	public static function the_excerpt( $content ) {
		if ( $post = get_post() ) {
			if ( Affiliate::post_type_enabled( $post->post_type ) ) {
				$content = apply_filters( 'affiliate_post_the_excerpt', self::transform( $content, $post ), $post );
			}
		}
		return $content;
	}

	/**
	 * Content filter, hooked on the_content, invokes the transformer.
	 *
	 * @param string $content
	 *
	 * @return string filtered content
	 */
	public static function the_content( $content ) {
		if ( $post = get_post() ) {
			if ( Affiliate::post_type_enabled( $post->post_type ) ) {
				$content = apply_filters( 'affiliate_post_the_content', self::transform( $content, $post ), $post );
			}
		}
		return $content;
	}

	/**
	 * Content transformer.
	 *
	 * @param string $content
	 * @param WP_Post $post
	 *
	 * @return string transformed content
	 */
	public static function transform( &$content, &$post ) {
		require_once AFFILIATE_CORE_LIB . '/class-affiliate-content-builder.php';
		$builder = new Affiliate_Content_Builder( $content );
		$content = $builder->get_content();
		return $content;
	}
}
add_action( 'init', array( 'Affiliate_Content', 'init' ) );
