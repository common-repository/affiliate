<?php
/**
 * class-affiliate-builder.php
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
 * @since 1.1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builder class, used to do content replacements.
 */
class Affiliate_Content_Builder {

	/**
	 * Content holder.
	 * @var string
	 */
	private $content = null;

	/**
	 * Holds keyword tuples.
	 * @var array
	 */
	private $keywords = null;

	/**
	 * The current keyword tuple for replacement.
	 * @var array
	 */
	private $current_keyword = null;

	/**
	 * Constructor for specific content taking on replacements for
	 * published keywords.
	 *
	 * @param string $content
	 */
	public function __construct( $content ) {
		$this->content = $content;
		$this->keywords = array();
		$ids = get_posts( array(
			'numberposts'      => -1,
			'post_type'        => 'affiliate_keyword',
			'meta_key'         => 'enabled',
			'meta_value'       => 'yes',
			'suppress_filters' => false,
			'fields'           => 'ids'
		) );
		foreach( $ids as $id ) {
			$url = get_post_meta( $id, 'url', true );
			if ( !empty( $url ) ) {
				if ( $keyword = get_post( $id ) ) {
					$search = $keyword->post_title;
					$match_case = get_post_meta( $id, 'match_case', true ) == 'yes';
					$boundary   = get_post_meta( $id, 'boundary', true );
					$boundary   = empty( $boundary ) || $boundary == 'yes';
					$nofollow   = get_post_meta( $id, 'nofollow', true ) == 'yes';
					if ( $match_case ) {
						$case = '';
					} else {
						$case = 'i';
					}
					$this->keywords[] = array(
						'url'      => $url,
						'search'   => $search,
						'case'     => $case,
						'rel'      => $nofollow ? 'nofollow' : '',
						'boundary' => $boundary
					);
					unset( $keyword );
				}
			}
		}
	}

	/**
	 * Transforms the content using keyword replacement.
	 *
	 * @return string
	 */
	public function get_content() {

		$charset = get_bloginfo( 'charset' );
		$d = new DOMDocument( '1.0', $charset );

		// Important to have the right encoding. Either like below or using
		// sprintf( '<meta http-equiv="Content-Type", content="text/html; charset=%s">', $charset );
		$prefix = sprintf( '<?xml version="1.0" encoding="%s"><html><body><div>', $charset );
		$suffix = '</div></body></html>';

		if ( !empty( $this->keywords ) ) {
			foreach( $this->keywords as $keyword ) {

				// we need to reconstruct the document after each replacement round
				@$d->loadHTML( $prefix . $this->content . $suffix );
				if ( $keyword['case'] == 'i' ? stripos( $this->content, $keyword['search'] ) : strpos( $this->content, $keyword['search'] ) ) {
					$this->current_keyword = $keyword;
					$this->traverse( $d );
					$output = $d->saveHTML();
					$open = mb_stripos( $output, $prefix );
					$close = mb_stripos( $output, $suffix );
					$output = mb_substr( $output, $open + strlen( $prefix ), $close - $open - strlen( $prefix ) );
					$this->content = html_entity_decode( $output, ENT_QUOTES, $charset );
				}
			}
		}
		return $this->content;
	}

	/**
	 * Node traversal and content replacement.
	 *
	 * @param DOMNode $DOMNode
	 */
	private function traverse( $DOMNode ) {
		if( $DOMNode->hasChildNodes() ){
			foreach ( $DOMNode->childNodes as $DOMElement ) {
				$nodeName = $DOMNode->nodeName;
				$nodeType = $DOMNode->nodeType;
				if ( $nodeType == XML_ELEMENT_NODE && $nodeName == 'a' ) {
					// skip links so that we don't place a link inside a link
				} else {
					$this->traverse( $DOMElement );
				}
			}
		} else {
			$nodeName = $DOMNode->nodeName;
			$nodeType = $DOMNode->nodeType;
			if ( $nodeType == XML_TEXT_NODE ) {
				if ( !empty( $this->current_keyword ) ) {
					$boundary = $this->current_keyword['boundary'] ? '\b' : '';
					$DOMNode->nodeValue = mb_ereg_replace(
						$boundary . $this->current_keyword['search'] . $boundary,
						sprintf(
							'<a href="%s" %s>\\0</a>',
							esc_attr( $this->current_keyword['url'] ),
							!empty( $this->current_keyword['rel'] ) ? ' rel="' . esc_attr( $this->current_keyword['rel'] ) . '" ' : ''
						),
						$DOMNode->nodeValue,
						"msr" . $this->current_keyword['case']
					);
				}
			}
		}
	}

}
