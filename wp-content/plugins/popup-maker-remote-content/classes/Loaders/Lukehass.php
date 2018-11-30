<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Loaders_Lukehass
 */
class PUM_RC_Loaders_Lukehass {

	/**
	 * @param string $icon
	 *
	 * @return string
	 */
	public static function get_icon( $icon = 'lines-1' ) {
		return "<div class='$icon'><div class='loader'></div></div>";
	}

}
