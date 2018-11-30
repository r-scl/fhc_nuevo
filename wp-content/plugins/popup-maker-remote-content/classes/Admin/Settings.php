<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Admin_Settings
 */
class PUM_RC_Admin_Settings {

	/**
	 *
	 */
	public static function init() {
		add_filter( 'pum_settings_tab_sections', array( __CLASS__, 'tab_sections' ) );
		add_filter( 'pum_settings_fields', array( __CLASS__, 'fields' ) );
		add_filter( 'pum_sanitize_settings', array( __CLASS__, 'sanitize' ) );
	}

	public static function sanitize_custom_icon_html( $html ) {
		$html = html_entity_decode( $html );

		$allowed_html = array(
			'div'  => array(
				'id'    => array(),
				'class' => array(),
			),
			'img'  => array(
				'alt'      => true,
				'align'    => true,
				'border'   => true,
				'height'   => true,
				'hspace'   => true,
				'longdesc' => true,
				'vspace'   => true,
				'src'      => true,
				'usemap'   => true,
				'width'    => true,
				'class'    => true,
				'id'       => true,
				'style'    => true,
				'title'    => true,
				'role'     => true,
			),
			'svg'  => array(
				'xmlns'       => true,
				'xmlns:xlink' => true,
				'class'       => true,
				'style'       => true,
				'id'          => true,
				'viewbox'     => true,
				'd'           => true,
				'x'           => true,
				'y'           => true,
				'viewBox'     => true,
				'xml:space'   => true,
				'version'     => true,
				'fill' => true,
				'stroke' => true,
			),
			'path' => array(
				'xmlns'       => true,
				'xmlns:xlink' => true,
				'class'       => true,
				'style'       => true,
				'id'          => true,
				'viewbox'     => true,
				'd'           => true,
				'x'           => true,
				'y'           => true,
				'viewBox'     => true,
				'xml:space'   => true,
				'version'     => true,
				'fill' => true,
				'stroke' => true,
			),
		);

		return wp_kses( $html, $allowed_html );
	}

	/**
	 * @param array $settings
	 *
	 * @return array
	 */
	public static function sanitize( $settings = array() ) {
		if ( ! empty( $settings['rc_custom_icon_1_html'] ) ) {
			$settings['rc_custom_icon_1_html'] = self::sanitize_custom_icon_html( $settings['rc_custom_icon_1_html'] );
			$settings['rc_custom_icon_2_html'] = self::sanitize_custom_icon_html( $settings['rc_custom_icon_2_html'] );
			$settings['rc_custom_icon_3_html'] = self::sanitize_custom_icon_html( $settings['rc_custom_icon_3_html'] );
			$settings['rc_custom_icon_1_css']  = stripslashes( sanitize_textarea_field( $settings['rc_custom_icon_1_css'] ) );
			$settings['rc_custom_icon_2_css']  = stripslashes( sanitize_textarea_field( $settings['rc_custom_icon_2_css'] ) );
			$settings['rc_custom_icon_3_css']  = stripslashes( sanitize_textarea_field( $settings['rc_custom_icon_3_css'] ) );
		}

		return $settings;
	}

	/**
	 * @param array $tab_sections
	 *
	 * @return array
	 */
	public static function tab_sections( $tab_sections = array() ) {
		$tab_sections['extensions']['rc'] = __( 'Remote Content', 'popup-maker-remote-content' );

		return $tab_sections;
	}

	/**
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function fields( $fields = array() ) {
		$fields['extensions']['rc'] = array(
			'rc_custom_icon_1_html' => array(
				'type'  => 'textarea',
				'label' => 'Custom Icon HTML',
			),
			'rc_custom_icon_1_css'  => array(
				'type'  => 'textarea',
				'label' => 'Custom Icon CSS',
			),
			'rc_custom_icon_2_html' => array(
				'type'       => 'textarea',
				'label'      => 'Custom Icon HTML',
				'allow_html' => true,
			),
			'rc_custom_icon_2_css'  => array(
				'type'  => 'textarea',
				'label' => 'Custom Icon CSS',
			),
			'rc_custom_icon_3_html' => array(
				'type'       => 'textarea',
				'label'      => 'Custom Icon HTML',
				'allow_html' => true,
			),
			'rc_custom_icon_3_css'  => array(
				'type'  => 'textarea',
				'label' => 'Custom Icon CSS',
			),
		);

		return $fields;
	}

}
