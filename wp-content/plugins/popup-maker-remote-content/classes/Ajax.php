<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Ajax
 */
class PUM_RC_Ajax {

	/**
	 *
	 */
	public static function init() {
		add_action( 'wp_ajax_pum_rc', array( __CLASS__, 'process_ajax' ) );
		add_action( 'wp_ajax_nopriv_pum_rc', array( __CLASS__, 'process_ajax' ) );
	}

	public static function process_ajax() {
		if ( empty( $_REQUEST['popup_id'] ) || $_REQUEST['popup_id'] <= 0 || empty( $_REQUEST['method'] ) ) {
			self::send_content_response( '<p>' . __( 'An error occurred or no data was returned by the server. Please try again.', 'popup-maker-remote-content' ) . '</p>' );
		}

		$settings = wp_parse_args( $_REQUEST, array(
			'popup_id'      => 0,
			'method'        => 'ajax',
			'function_name' => '',
			'url'           => '',
			'postID'        => null,
		) );

		$settings['postID'] = url_to_postid( $settings['url'] );

		switch ( $settings['method'] ) {
			case 'ajax':
				if ( empty( $settings['function_name'] ) || ! function_exists( $settings['function_name'] ) ) {
					self::send_content_response( '<p>' . __( 'No valid AJAX callback function was defined, or the function entered does not exist.', 'popup-maker-remote-content' ) . '</p>' );
				}

				ob_start();
				echo call_user_func( $settings['function_name'], $settings );
				self::send_content_response( ob_get_clean() );
				break;
			case 'posts':
				if ( empty( $settings['postID'] ) || $settings['postID'] <= 0 ) {
					self::send_content_response( '<p>' . __( 'No content found for the clicked link.', 'popup-maker-remote-content' ) . '</p>' );
				}

				$post = get_post( $settings['postID'], ARRAY_A );
				$post['post_content'] = do_shortcode( $post['post_content'] );
				self::send_response( array( 'success' => true, 'postdata' => $post ) );
				break;
		}

		self::send_content_response( '<p>' . __( 'An error occurred or no data was returned by the server. Please try again.', 'popup-maker-remote-content' ) . '</p>' );
	}


	/**
	 * @param array $response
	 */
	public static function send_response( $response = array() ) {
		echo json_encode( $response );
		die();
	}

	/**
	 * @param $content
	 */
	public static function send_content_response( $content ) {
		echo json_encode( array(
			'success' => true,
			'content' => $content,
		) );
		die();
	}
}
