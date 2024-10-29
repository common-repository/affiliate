<?php
/**
 * class-affiliate-admin.php
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
 * Affiliate plugin administration.
 */
class Affiliate_Admin {

	/**
	 * Determines the position of the Affiliate menu.
	 *
	 * @var string
	 */
	const MENU_POSITION = '31.713';

	/**
	 * Required capability to access the administrative sections.
	 *
	 * @var string
	 */
	const ADMIN_CAPABILITY = 'manage_options';

	/**
	 * Administration setup.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_filter( 'plugin_action_links_'. plugin_basename( AFFILIATE_PLUGIN_FILE ), array( __CLASS__, 'admin_settings_link' ) );
	}

	/**
	 * Admin init, hooked on admin_init.
	 */
	public static function admin_init() {
		wp_register_style( 'affiliate_admin', AFFILIATE_PLUGIN_URL . 'css/admin.css', array(), AFFILIATE_PLUGIN_VERSION );
	}

	/**
	 * Loads styles for the admin section.
	 */
	public static function admin_print_styles() {
		wp_enqueue_style( 'affiliate_admin' );
	}

	/**
	 * Add a menu item to the Appearance menu.
	 */
	public static function admin_menu() {

		$pages = array();

		// Affiliate menu and main menu item
		$page = add_menu_page(
			__( 'Affiliate', 'affiliate' ),
			__( 'Affiliate', 'affiliate' ),
			self::ADMIN_CAPABILITY,
			'affiliate-admin',
			array( __CLASS__, 'affiliate_admin_section' ),
			AFFILIATE_PLUGIN_URL . '/images/affiliate.png',
			self::MENU_POSITION
		);
		$pages[] = $page;
		add_action( 'admin_print_styles-' . $page, array( __CLASS__, 'admin_print_styles' ) );

		// Keywords menu item
		$affiliate_keyword_cpt = get_post_type_object( 'affiliate_keyword' );
		add_submenu_page(
			'affiliate-admin',
			$affiliate_keyword_cpt->labels->name,
			$affiliate_keyword_cpt->labels->all_items,
			$affiliate_keyword_cpt->cap->edit_posts,
			"edit.php?post_type=affiliate_keyword"
		);

// 		remove_submenu_page( 'affiliate-admin', 'affiliate-admin' );

		// @todo Links menu item
// 		$affiliate_link_cpt = get_post_type_object( 'affiliate_link' );
// 		add_submenu_page(
// 			'affiliate-admin',
// 			$affiliate_link_cpt->labels->name,
// 			$affiliate_link_cpt->labels->all_items,
// 			$affiliate_link_cpt->cap->edit_posts,
// 			"edit.php?post_type=affiliate_link"
// 		);

		// Settings menu item
		$page = add_submenu_page(
			'affiliate-admin',
			__( 'Settings', 'affiliate' ),
			__( 'Settings', 'affiliate' ),
			self::ADMIN_CAPABILITY,
			'affiliate-settings',
			array( __CLASS__, 'affiliate_settings_section' )
		);
		add_action( 'admin_print_styles-' . $page, array( __CLASS__, 'admin_print_styles' ) );
	}

	/**
	 * Affiliate administration screen.
	 */
	public static function affiliate_admin_section() {

		if ( !current_user_can( self::ADMIN_CAPABILITY ) ) {
			wp_die( __( 'Access denied.', 'affiliate' ) );
		}

		$output = '';

		$output .= '<div class="affiliate-admin">';

		$output .= '<h1>';
		$output .= __( 'Affiliate', 'affiliate' );
		$output .= '</h1>';

		$output .= '<h2>';
		$output .= __( 'Keywords', 'affiliate' );
		$output .= '</h2>';

		$n = 0;
		$keywords = get_posts( array(
			'numberposts'      => -1,
			'post_type'        => 'affiliate_keyword',
			'meta_key'         => 'enabled',
			'meta_value'       => 'yes',
			'suppress_filters' => false,
			'fields'           => 'ids'
		) );
		if ( $keywords ) {
			$n = count( $keywords );
		}
		unset( $keywords );
		$output .= '<div class="keyword-summary">';
		$output .= '<p>';
		$output .= sprintf( esc_html( _n( 'There is one active keyword.', 'There are %d active keywords.', $n, 'affiliate' ) ), $n );
		$output .= '</p>';
		$output .= '</div>';

		$output .= '<p>';
		$output .= esc_html__( 'Keywords can be automatically replaced with links.', 'affiliate' );
		$output .= ' ';
		$output .= esc_html__( 'Wherever those keywords appear in the page content, the system can replace them.', 'affiliate' );
		$output .= ' ';
		$output .= esc_html__( 'You must enable keyword substitution for the desired post types.', 'affiliate' );
		$output .= ' ';
		$output .= sprintf(
			esc_html__( 'Keywords are substituted only on post types enabled in the %s.', 'affiliate' ),
			sprintf(
				'<a href="%s">%s</a>',
				esc_attr( get_admin_url( null, 'admin.php?page=affiliate-settings' ) ),
				esc_html( __( 'Settings', 'affiliate' ) )
			)
		);
		$output .= '</p>';
		$output .= '<p>';
		$output .= sprintf(
			esc_html__( 'Visit the %sDocumentation%s pages for detailed information.', 'affiliate' ),
			'<a href="https://docs.itthinx.com/document/affiliate/">',
			'</a>'
		);
		$output .= '</p>';

		$output .= '</div>';

		echo $output;
	}

	/**
	 * Settings screen.
	 */
	public static function affiliate_settings_section() {

		if ( !current_user_can( self::ADMIN_CAPABILITY ) ) {
			wp_die( esc_html__( 'Access denied.', 'affiliate' ) );
		}

		// handle save
		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST['affiliate-settings'], 'save' ) ) {
				$post_types_option = Affiliate::get_post_types();
				$post_types = get_post_types( array( 'public' => true ) );
				$selected_post_types = is_array( $_POST['post-type'] ) ? $_POST['post-type'] : array();
				foreach( $post_types as $post_type ) {
					$post_types_option[$post_type] = in_array( $post_type, $selected_post_types );
				}
				Affiliate::set_post_types( $post_types_option );
			}
		}

		// build settings
		$output = '';
		$output .= '<div class="affiliate-settings">';
		$output .= '<h1>';
		$output .= esc_html__( 'Settings', 'affiliate' );
		$output .= '</h1>';
		$output .= '<form action="" name="options" method="post">';
		$output .= '<div>';
		$output .= '<h2>' . esc_html__( 'Post Types', 'affiliate' ) . '</h2>';
		$output .= '<p class="description">' .  esc_html__( 'Enable keyword substitution for these post types.', 'affiliate' ) . '</p>';
		$post_types_option = Affiliate::get_post_types();
		$post_types = get_post_types( array( 'public' => true ) );
		$output .= '<ul>';
		foreach( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			$output .= '<li>';
			$output .= '<label>';
			$label = $post_type;
			$labels = isset( $post_type_object->labels ) ? $post_type_object->labels : null;
			if ( ( $labels !== null ) && isset( $labels->singular_name ) ) {
				$label = __( $labels->singular_name ); // output is escaped below
			}
			$checked = ( isset( $post_types_option[$post_type] ) && $post_types_option[$post_type] ) ? ' checked="checked" ' : '';
			$output .= '<input name="post-type[]" type="checkbox" value="' . esc_attr( $post_type ) . '" ' . $checked . '/>';
			$output .= esc_html( $label );
			$output .= '</label>';
			$output .= '</li>';
		}
		$output .= '<ul>';
		$output .= '<p class="description">';
		$output .= esc_html__( 'Keywords are substituted with affiliate links on enabled post types only.', 'affiliate' );
		$output .= '</p>';

		$output .= wp_nonce_field( 'save', 'affiliate-settings', true, false );
		$output .= sprintf( '<input class="button button-primary" type="submit" name="submit" value="%s"/>',  esc_attr( __( 'Save', 'affiliate' ) ) );
		$output .= '</div>';
		$output .= '</form>';
		$output .= '</div>';

		// render settings
		echo $output;
	}

	/**
	 * Adds plugin links.
	 *
	 * @param array $links
	 * @param array $links with additional links
	 *
	 * @return array of links
	 */
	public static function admin_settings_link( $links ) {
		if ( current_user_can( self::ADMIN_CAPABILITY ) ) {
			$links = array(
				'<a href="' . get_admin_url( null, 'admin.php?page=affiliate-admin' ) . '">' . esc_html__( 'Affiliate', 'affiliate' ) . '</a>',
				'<a href="' . get_admin_url( null, 'admin.php?page=affiliate-settings' ) . '">' . esc_html__( 'Settings', 'affiliate' ) . '</a>'
			) + $links;
		}
		return $links;
	}
}
add_action( 'init', array( 'Affiliate_Admin', 'init' ) );
