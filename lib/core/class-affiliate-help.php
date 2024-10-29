<?php
/**
 * class-affiliate-help.php
 *
 * Copyright (c) 2015 "kento" Karim Rahimpur www.itthinx.com
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
 * @author itthinx
 * @package affiliate
 * @since 1.3.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Help sections.
 */
class Affiliate_Help {

	/**
	 * Adds the filter for contextual help.
	 */
	public static function init() {
		add_action( 'current_screen', array( __CLASS__, 'current_screen' ) );
	}

	/**
	 * Adds contextual help on our screens.
	 *
	 * @param WP_Screen $screen
	 */
	public static function current_screen( $screen ) {
		if ( $screen instanceof WP_Screen ) {
			switch( $screen->id ) {
				case 'toplevel_page_affiliate-admin' : // Affiliate
				case 'edit-affiliate_keyword' : // Keywords
				case 'affiliate_keyword' : // Edit Keyword
				case 'affiliate_page_affiliate-settings' : // Affiliate > Settings
					$screen->add_help_tab(
						array(
							'id'      => 'affiliate',
							'title'   => esc_html__( 'Affiliate', 'affiliate' ),
							'content' =>
								'<p>' .
								esc_html__( 'Thanks for using the Affiliate toolbox for Affiliate Marketers.', 'affiliate' ) .
								'</p>' .
								'<p>' .
								sprintf(
									esc_html__(
										'Please read the %sDocumentation%s if you need help on its usage.',
										'affiliate'
									),
									'<a href="https://docs.itthinx.com/document/affiliate/">',
									'</a>'
								) .
								'</p>'
						)
					);
					break;
			}
		}
	}
}
Affiliate_Help::init();
