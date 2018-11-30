<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Site
 */
class PUM_RC_Site {

	/**
	 *
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'pum_generated_js', array( __CLASS__, 'generated_js' ) );
		add_filter( 'pum_generated_css', array( __CLASS__, 'generated_css' ) );
		add_action( 'pum_preload_popup', array( __CLASS__, 'enqueue_popup_assets' ) );
		add_action( 'pum_generate_popup_css', array( __CLASS__, 'generate_popup_css' ) );
	}

	/**
	 *
	 */
	public static function assets() {
		if ( ! PUM_AssetCache::writeable() ) {
			wp_register_script( 'pum-rc', PUM_RC::$URL . '/assets/js/pum-rc-site' . PUM_Site_Assets::$suffix . '.js', array( 'jquery', 'popup-maker-site' ), PUM_RC::$VER, true );
			wp_register_style( 'pum-rc', PUM_RC::$URL . 'assets/css/pum-rc-site' . PUM_Site_Assets::$suffix . '.css', array( 'popup-maker-site' ), PUM_RC::$VER );
		}
	}

	/**
	 * @param array $js
	 *
	 * @return array
	 */
	public static function generated_js( $js = array() ) {
		$js['rc'] = array(
			'content'  => file_get_contents( PUM_RC::$DIR . '/assets/js/pum-rc-site' . PUM_Site_Assets::$suffix . '.js' ),
			'priority' => 5,
		);

		return $js;
	}

	/**
	 * @param array $css
	 *
	 * @return array
	 */
	public static function generated_css( $css = array() ) {
		$css['rc'] = array(
			'content'  => file_get_contents( PUM_RC::$DIR . '/assets/css/pum-rc-site' . PUM_Site_Assets::$suffix . '.css' ),
			'priority' => 5,
		);

		return $css;
	}

	/**
	 * @param int $popup_id
	 */
	public static function enqueue_popup_assets( $popup_id = 0 ) {
		$popup = pum_get_popup( $popup_id );

		if ( ! pum_is_popup( $popup ) ) {
			return;
		}

		if ( has_shortcode( $popup->post_content, 'pum_remote_content' ) ) {
			$shortcodes = PUM_Helpers::get_shortcodes_from_content( $popup->post_content );

			foreach ( $shortcodes as $key => $shortcode ) {
				if ( $shortcode['tag'] == 'pum_remote_content' && $shortcode['atts']['method'] == 'iframe' ) {
					wp_enqueue_script( 'iframe-resizer' );
				}
			}

			wp_enqueue_script( 'pum-rc' );
			wp_enqueue_style( 'pum-rc' );

		}
	}

	public static function generate_popup_css( $popup_id ) {
		$popup = pum_get_popup( $popup_id );

		if ( ! pum_is_popup( $popup ) ) {
			return;
		}

		if ( has_shortcode( $popup->post_content, 'pum_remote_content' ) ) {
			$shortcodes = PUM_Helpers::get_shortcodes_from_content( $popup->post_content );

			foreach ( $shortcodes as $key => $shortcode ) {
				if ( $shortcode['tag'] == 'pum_remote_content' && in_array( $shortcode['atts']['loading_icon'], array( 'custom_1', 'custom_2', 'custom_3' ) ) ) {
					$id = str_replace( 'custom_', '', $shortcode['atts']['loading_icon'] );
					$css = pum_get_option( 'rc_custom_icon_' . $id . '_css', '' );
					if ( ! empty( $css ) ) {
						echo esc_html( $css );
					}
				}
			}
		}


	}
}
