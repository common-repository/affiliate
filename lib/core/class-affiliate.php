<?php
/**
 * class-affiliate.php
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
 * Affiliate plugin controller.
 */
class Affiliate {

	public static $admin_messages = array();

	/**
	 * Plugin setup procedure.
	 */
	public static function boot() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		load_plugin_textdomain( 'affiliate', null, AFFILIATE_PLUGIN_NAME . '/languages' );
		if ( function_exists( 'mb_strlen' ) ) {
			require_once AFFILIATE_CORE_LIB . '/class-affiliate-admin.php';
			require_once AFFILIATE_CORE_LIB . '/class-affiliate-content.php';
			require_once AFFILIATE_CORE_LIB . '/class-affiliate-keyword.php';
			require_once AFFILIATE_CORE_LIB . '/class-affiliate-help.php';
		} else {
			self::$admin_messages[] =
				'<div class="error" style="font-size: 24px; line-height: 36px; padding: 2em;">' .
				sprintf(
					__( '%sAffiliate%s requires the Multibyte String %smbstring%s PHP extension.', 'affiliate' ),
					'<strong><a href="https://docs.itthinx.com/document/affiliate/" target="_blank">',
					'</a></strong>',
					'<a href="https://www.php.net/manual/en/mbstring.installation.php" target="_blank">',
					'</a>'
				) .
				' ' .
				__( 'Please ask your website administrator to enable this extension.', 'affiliate' ) .
				'</div>';
		}
	}

	/**
	 * Renders accumulated admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}

	/**
	 * Returns the post types that we should handle.
	 *
	 * @access private
	 *
	 * @return array
	 */
	public static function get_post_types() {
		$post_types = get_option( 'affiliate-post-types', null );
		if ( $post_types === null ) {
			add_option( 'affiliate-post-types', array( 'post' => true, 'page' => true ), '', 'no' );
			$post_types = get_option( 'affiliate-post-types', array( 'post' => true, 'page' => true ) );
		}
		return $post_types;
	}

	/**
	 * Determines the post types we should handle.
	 *
	 * @access private
	 *
	 * @param array $post_types maps string (post type) => boolean
	 */
	public static function set_post_types( $post_types ) {
		update_option( 'affiliate-post-types', $post_types );
	}

	/**
	 * Whether keyword substitution for the post type is enabled.
	 *
	 * @access private
	 *
	 * @param string $post_type
	 *
	 * @return boolean
	 */
	public static function post_type_enabled( $post_type ) {
		$post_types = self::get_post_types();
		return isset( $post_types[$post_type] ) && $post_types[$post_type];
	}

}
Affiliate::boot();
