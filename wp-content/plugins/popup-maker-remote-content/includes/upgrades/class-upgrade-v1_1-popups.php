<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements a batch processor for migrating existing popups to new data structure.
 *
 * @since 1.1.0
 *
 * @see   PUM_Abstract_Upgrade_Popups
 */
class PUM_RC_Upgrade_v1_1_Popups extends PUM_Abstract_Upgrade_Popups {

	/**
	 * Batch process ID.
	 *
	 * @var    string
	 */
	public $batch_id = 'rc-v1_1-popups';

	/**
	 * Only load popups with specific meta keys.r
	 *
	 * @return array
	 */
	public function custom_query_args() {
		return array(
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     => 'popup_remote_content_enabled',
					'compare' => 'EXISTS',
				),
			),
		);
	}

	/**
	 * Strips out prefixes.
	 *
	 * @param PUM_Model_Popup $popup
	 *
	 * @return array
	 */
	public function get_old_meta( $popup ) {
		$defaults = array(
			'enabled'       => null,
			'type'          => 'loadselector',
			'function_name' => '',
			'css_selector'  => '',
			'loading_icon'  => 'lines-1',
			'defaults_set'  => true,
		);

		$data = array();

		foreach ( $defaults as $key => $value ) {
			$old_value    = $popup->get_meta( 'popup_remote_content_' . $key );
			$data[ $key ] = ! empty( $old_value ) ? $old_value : $value;
		}

		return $data;
	}

	/**
	 * @param $content
	 *
	 * @return array
	 */
	public function get_shortcodes_from_content( $content ) {
		$pattern    = get_shortcode_regex();
		$shortcodes = array();
		if ( preg_match_all( '/' . $pattern . '/s', $content, $matches ) ) {
			foreach ( $matches[0] as $key => $value ) {
				$shortcodes[ $key ] = array(
					'full_text' => $value,
					'tag'       => $matches[2][ $key ],
					'atts'      => shortcode_parse_atts( $matches[3][ $key ] ),
					'content'   => $matches[5][ $key ],
				);

				if ( ! empty( $shortcodes[ $key ]['atts'] ) ) {
					foreach ( $shortcodes[ $key ]['atts'] as $attr_name => $attr_value ) {
						// Filter numeric keys as they are valueless/truthy attributes.
						if ( is_numeric( $attr_name ) ) {
							$shortcodes[ $key ]['atts'][ $attr_value ] = true;
							unset( $shortcodes[ $key ]['atts'] );
						}
					}
				}
			}
		}

		return $shortcodes;
	}

	public function remap_icon( $icon = 'lines-1' ) {
		$remapped = array(
			'lines-1'   => 'lukehass/load1',
			'dots-1'    => 'lukehass/load2',
			'circles-1' => 'lukehass/load3',
			'circles-2' => 'lukehass/load4',
			'circles-3' => 'lukehass/load5',
			'circles-4' => 'lukehass/load6',
			'circles-5' => 'lukehass/load7',
			'circles-6' => 'lukehass/load8',
		);

		return isset( $remapped[ $icon ] ) ? $remapped[ $icon ] : 'lukehass/load1';
	}

	/**
	 * Process needed upgrades on each popup.
	 *
	 * @param int $popup_id
	 */
	public function process_popup( $popup_id = 0 ) {

		$popup = pum_get_popup( $popup_id );

		$rc = $this->get_old_meta( $popup );

		if ( ! $rc || empty( $rc['enabled'] ) || ! $rc['enabled'] ) {
			return;
		}

		// $rc['type'] == 'loadselector' -> 'load'
		if ( $rc['type'] == 'loadselector' ) {
			$rc['type'] = 'load';
		}

		// $rc['type'] -> $shortcode['method']
		$shortcode_atts = array(
			'method'       => $rc['type'] == 'loadselector' ? 'load' : $rc['type'],
			'loading_icon' => $this->remap_icon( $rc['loading_icon'] ),
		);

		switch ( $shortcode_atts['method'] ) {
			case  'ajax':
				$shortcode_atts['ajax_function_name'] = $rc['function_name'];
				break;
			case 'load':
				$shortcode_atts['load_css_selector'] = $rc['css_selector'];
				break;
		}

		$atts = '';

		foreach( $shortcode_atts as $key => $val ) {
			$atts .= " $key='$val'";
		}

		$shortcode = "[pum_remote_content $atts]";
		$popup->post_content .= $shortcode;

		$popup->save();

		$this->clean_up_old_meta( $popup_id );
	}

	/**
	 * @param int $popup_id
	 */
	public function clean_up_old_meta( $popup_id = 0 ) {
		global $wpdb;

		$meta_keys = implode( "','", array(
			'popup_remote_content_enabled',
			'popup_remote_content_type',
			'popup_remote_content_function_name',
			'popup_remote_content_css_selector',
			'popup_remote_content_loading_icon',
			'popup_remote_content_defaults_set',
		) );

		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE post_id = " . (int) $popup_id . " AND meta_key IN('$meta_keys');" );
	}


	/**
	 *
	 */
	public function finish() {
	}
}
