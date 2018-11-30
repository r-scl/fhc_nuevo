<?php
/**
 * Plugin Name:  Popup Maker - Remote Content
 * Plugin URI:   https://wppopupmaker.com/extensions/remote-content/
 * Description:  The remote content extension allows you to easily fill your popup with a remote content source.
 * Version:      1.1.2
 * Author:       WP Popup Maker
 * Author URI:   https://wppopupmaker.com/
 * Text Domain:  popup-maker-remote-content
 *
 * @author       WP Popup Maker
 * @copyright    Copyright (c) 2018, WP Popup Maker
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PUM_RC
 */
class PUM_RC {
	/**
	 * @var int $download_id for EDD.
	 */
	public static $ID = 40197;

	/**
	 * @var string
	 */
	public static $NAME = 'Remote Content';

	/**
	 * @var string Plugin Version
	 */
	public static $VER = '1.1.2';

	/**
	 * @var string Required Version of Popup Maker
	 */
	public static $REQUIRED_CORE_VER = '1.7';

	/**
	 * @var int DB Version
	 */
	public static $DB_VER = 1;

	/**
	 * @var string Plugin Directory
	 */
	public static $DIR;

	/**
	 * @var string Plugin URL
	 */
	public static $URL;

	/**
	 * @var string Plugin FILE
	 */
	public static $FILE;

	/**
	 * @var self $instance
	 */
	private static $instance;

	/**
	 * @return self
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
			self::$instance->setup_constants();
			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Internationalization
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'popup-maker-remote-content', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Set up plugin constants.
	 */
	public static function setup_constants() {
		self::$DIR  = plugin_dir_path( __FILE__ );
		self::$URL  = plugin_dir_url( __FILE__ );
		self::$FILE = __FILE__;

		// Plugin version
		define( 'POPMAKE_REMOTECONTENT_VER', self::$VER );

		// Plugin path
		define( 'POPMAKE_REMOTECONTENT_DIR', self::$DIR );

		// Plugin URL
		define( 'POPMAKE_REMOTECONTENT_URL', self::$URL );

	}

	/**
	 * Include required files
	 */
	private function includes() {
	}


	/**
	 * Initialize the plugin.
	 */
	public static function init() {
		PUM_RC_Site::init();
		PUM_RC_Admin::init();
		PUM_RC_Ajax::init();
		PUM_RC_Popup::init();
		PUM_RC_Upgrades::init();
		PUM_RC_Shortcode_RemoteContent::init();



		PUM_RC_Triggers::init();
	}

}

/**
 * Register this extensions autoload parameters to the pum_autoloaders array.
 *
 * @param array $autoloaders
 *
 * @return array
 */
function pum_rc_autoloader( $autoloaders = array() ) {
	return array_merge( $autoloaders, array(
		array(
			'prefix' => 'PUM_RC_',
			'dir'    => dirname( __FILE__ ) . '/classes/',
		),
	) );
}

add_filter( 'pum_autoloaders', 'pum_rc_autoloader' );

/**
 * Get the ball rolling.
 */
function pum_rc_init() {
	if ( ! class_exists( 'PUM_Extension_Activator' ) ) {
		require_once 'includes/pum-sdk/class-pum-extension-activator.php';
	}

	$activator = new PUM_Extension_Activator( 'PUM_RC' );
	$activator->run();
}

add_action( 'plugins_loaded', 'pum_rc_init', 11 );

if ( ! class_exists( 'PUM_RC_Activator' ) ) {
	require_once 'classes/Activator.php';
}
register_activation_hook( __FILE__, 'PUM_RC_Activator::activate' );

if ( ! class_exists( 'PUM_RC_Deactivator' ) ) {
	require_once 'classes/Deactivator.php';
}
register_deactivation_hook( __FILE__, 'PUM_RC_Deactivator::deactivate' );
