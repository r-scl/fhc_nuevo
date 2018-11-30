<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC_Loaders
 */
class PUM_RC_Loaders {

	/**
	 * @param string $icon
	 * @param string $group
	 *
	 * @return mixed
	 */
	public static function generate_icon( $icon = 'lines-1', $group = 'lukehass' ) {
		$class = 'PUM_RC_Loaders_' . ucfirst( $group );

		if ( ! class_exists( $class ) ) {
			return '';
		}

		$icon_html = method_exists( $class, 'get_icon' ) ? $class::get_icon( $icon ) : '';

		return "<div class='pum-loader $group'>$icon_html</div>";
	}

	/**
	 * @param string $icon_html
	 * @param string $group
	 *
	 * @return mixed
	 */
	public static function generate_custom_icon( $icon_html = '', $group = 'custom-1' ) {

		return "<div class='pum-loader $group'><div>$icon_html</div></div>";
	}

}
