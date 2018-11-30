<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class PUM_RC_Triggers
 */
class PUM_RC_Triggers {

	/**
	 *
	 */
	public static function init() {
		add_filter( 'pum_registered_triggers', array( __CLASS__, 'register_triggers' ) );
		add_filter( 'pum_click_selector_presets', array( __CLASS__, 'click_selector_presets' ) );
	}

	/**
	 * @param array $triggers
	 *
	 * @return array
	 */
	public static function register_triggers( $triggers = array() ) {
		$post_types  = PUM_Admin_Helpers::post_type_dropdown_options( array( '_builtin' => false, 'publicly_queryable' => true ) );
		$post_labels = get_post_type_labels( get_post_type_object( 'post' ) );

		return array_merge_recursive( $triggers, array(
			'click_open' => array(
				'fields' => array(
					'general' => array(
						'rc_post_type' => array(
							'label'   => __( 'Target Links of Post Type', 'popup-maker-remote-content' ),
							'type'    => 'select',
							'std'     => '',
							'options' => array( '' => '', 'post' => $post_labels->name ) + $post_types,
							'desc'    => __( 'Used along with the Post Type method fore Remote Content Boxes', 'popup-maker-remote-content' ),
						),
					),
				),
			),

		) );
	}


	/**
	 * @param array $click_selector_presets
	 *
	 * @return array
	 */
	public static function click_selector_presets( $click_selector_presets = array() ) {
		return array_merge( $click_selector_presets, array(
			'a:internal' => __( 'Link: Internal URLs (same domain)', 'popup-maker-remote-content' ),
			'a:external' => __( 'Link: External URLs (different domain)', 'popup-maker-remote-content' ),
		) );
	}
}
