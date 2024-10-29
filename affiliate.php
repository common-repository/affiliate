<?php
/**
 * affiliate.php
 *
 * Copyright (c) 2014-2023 "kento" Karim Rahimpur www.itthinx.com
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
 *
 * Plugin Name: Affiliate
 * Plugin URI: https://www.itthinx.com/plugins/affiliate/
 * Description: The Affiliate plugin is a toolbox for Affiliate Marketers.
 * Version: 1.12.0
 * Author: itthinx
 * Author URI: https://www.itthinx.com
 * Donate-Link: https://www.itthinx.com/shop/
 * License: GPLv3
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
define( 'AFFILIATE_PLUGIN_VERSION', '1.12.0' );
define( 'AFFILIATE_PLUGIN_NAME', 'affiliate' );
define( 'AFFILIATE_PLUGIN_DOMAIN', 'affiliate' );
define( 'AFFILIATE_PLUGIN_FILE', __FILE__ );
define( 'AFFILIATE_PLUGIN_BASENAME', plugin_basename( AFFILIATE_PLUGIN_FILE ) );
define( 'AFFILIATE_PLUGIN_URL', plugin_dir_url( AFFILIATE_PLUGIN_FILE ) );
if ( !defined( 'AFFILIATE_CORE_DIR' ) ) {
	define( 'AFFILIATE_CORE_DIR', WP_PLUGIN_DIR . '/affiliate' );
}
if ( !defined( 'AFFILIATE_CORE_LIB' ) ) {
	define( 'AFFILIATE_CORE_LIB', AFFILIATE_CORE_DIR . '/lib/core' );
}
if ( !defined( 'AFFILIATE_CORE_URL' ) ) {
	define( 'AFFILIATE_CORE_URL', WP_PLUGIN_URL . '/affiliate' );
}	
require_once( AFFILIATE_CORE_LIB . '/class-affiliate.php');
