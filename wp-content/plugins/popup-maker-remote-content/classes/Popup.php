<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Popup
 */
class PUM_RC_Popup {

	/**
	 *
	 */
	public static function init() {
		add_filter( 'pum_popup_classes', array( __CLASS__, 'popup_classes' ), 10, 2 );
		add_filter( 'pum_popup_get_triggers', array( __CLASS__, 'get_triggers' ), 10, 2 );
	}

	/**
	 * @param array $classes
	 * @param int   $popup_id
	 *
	 * @return array
	 */
	public static function popup_classes( $classes = array(), $popup_id ) {

		$popup = pum_get_popup( $popup_id );

		// TODO Fill this in when done.
		if ( has_shortcode( $popup->post_content, 'pum_remote_content' ) ) {
			$classes['overlay'][] = 'remote-content';
			$classes['container'][] = 'remote-content';
		}

		return $classes;
	}

	/**
	 * @param string $post_type
	 *
	 * @return bool|int|null|string
	 */
	public static function get_click_trigger_post_type_regexs( $post_type = 'post' ) {
		global $wp_rewrite;

		if ( $post_type == 'post' ) {
			// Add Posts
			$rule          = $wp_rewrite->permalink_structure;
			$rewrite_rules = $wp_rewrite->generate_rewrite_rules( $rule );

		} else {
			// Doesn't exist.
			if ( empty( $wp_rewrite->extra_permastructs[ $post_type ] ) ) {
				return false;
			}

			$structure = $wp_rewrite->extra_permastructs[ $post_type ];

			if ( is_array( $structure ) ) {
				if ( count( $structure ) == 2 )
					$rewrite_rules = $wp_rewrite->generate_rewrite_rules( $structure[0], $structure[1] );
				else
					$rewrite_rules = $wp_rewrite->generate_rewrite_rules( $structure['struct'], $structure['ep_mask'], $structure['paged'], $structure['feed'], $structure['forcomments'], $structure['walk_dirs'], $structure['endpoints'] );
			} else {
				$rewrite_rules = $wp_rewrite->generate_rewrite_rules( $structure );
			}

			/**
			 * Filters rewrite rules used for individual permastructs.
			 *
			 * The dynamic portion of the hook name, `$permastructname`, refers
			 * to the name of the registered permastruct, e.g. 'post_tag' (tags),
			 * 'category' (categories), etc.
			 *
			 * @since 3.1.0
			 *
			 * @param array $rules The rewrite rules generated for the current permastruct.
			 */
			$rewrite_rules = apply_filters( "{$post_type}_rewrite_rules", $rewrite_rules );
		}


		// Filter out extended rules.
		$rewrite_rules = PUM_Utils_Array::remove_keys_containing( $rewrite_rules, array(
			'/trackback/',
			'/embed/',
			'/feed/',
			'/(feed|rdf|rss|rss2|atom)/',
			'/comment-page-',
			'+/attachment/',
			'/page/',
		) );

		// Get the highest priority rule that is left.
		return key( $rewrite_rules );
	}

	/**
	 * @param array $triggers
	 * @param       $popup_id
	 *
	 * @return array
	 */
	public static function get_triggers( $triggers = array(), $popup_id ) {

		foreach( $triggers as $key => $trigger ) {
			if ( $trigger['type'] == 'click_open' && ! empty( $trigger['settings']['rc_post_type'] )) {
				$triggers[ $key ]['settings']['rc_post_type_regex'] = self::get_click_trigger_post_type_regexs( $trigger['settings']['rc_post_type'] );
			}
		}

		return $triggers;
	}
}
