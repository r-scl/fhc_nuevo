<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Admin
 */
class PUM_RC_Admin {

	/**
	 *
	 */
	public static function init() {
		PUM_RC_Admin_Settings::init();
		add_action( 'pum_save_popup', array( __CLASS__, 'save_popup' ) );
	}

	/**
	 * @param int $popup_id
	 */
	public static function save_popup( $popup_id ) {
		$popup = pum_get_popup( $popup_id );

		if ( $popup->get_meta( 'pum_rc_data_ver' ) === false ) {
			$popup->update_meta( 'pum_rc_data_ver', PUM_RC::$DB_VER );
		}
	}

}
