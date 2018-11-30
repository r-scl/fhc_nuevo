<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.3.0
 */
class PUM_RC_Activator {

	/**
	 * @param bool $network_wide
	 */
	public static function activate( $network_wide = false ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) { // See if being activated on the entire network or one blog

			$current_blog = $wpdb->blogid;

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

			// Try to reduce the chances of a timeout with a large number of sites.
			if ( count( $blog_ids ) > 2 ) {
				ignore_user_abort( true );
				if ( ! pum_is_func_disabled( 'set_time_limit' ) ) {
					@set_time_limit( 0 );
				}
			}

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::activate_site();
			}

			// Switch back to the current blog
			switch_to_blog( $current_blog );
		} else {
			// Running on a single blog
			self::activate_site();
		}
	}

	/**
	 * Activate individual sites.
	 */
	public static function activate_site() {
		PUM_RC_Upgrades::instance();
		// Add a temporary option that will fire a hookable action on next load.
		set_transient( '_pum_rc_installed', true, 30 );
	}

}