<?php
/*******************************************************************************
 * Copyright (c) 2018, WP Popup Maker
 ******************************************************************************/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles processing of data migration & upgrade routines.
 *
 * @since 1.1.0
 */
class PUM_RC_Upgrades {

	/**
	 * @var PUM_Upgrades
	 */
	public static $instance;

	/**
	 * Popup Maker version.
	 *
	 * @var    string
	 */
	private $version;

	/**
	 * Popup Maker version.
	 *
	 * @var    string
	 */
	private $db_version;

	/**
	 * Popup Maker upgraded from version.
	 *
	 * @var    string
	 */
	private $upgraded_from;

	/**
	 * Popup Maker initial version.
	 *
	 * @var    string
	 */
	private $initial_version;

	public static function init() {
		self::instance();
	}

	/**
	 * Gets everything going with a singleton instance.
	 *
	 * @return PUM_Upgrades
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Sets up the Upgrades class instance.
	 */
	public function __construct() {
		// Update stored plugin version info.
		$this->update_plugin_version();

		add_action( 'pum_register_upgrades', array( $this, 'register_processes' ) );
	}

	/**
	 * Update version info.
	 */
	public function update_plugin_version() {
		$this->version         = get_option( 'pum_rc_ver' );
		$this->db_version      = get_option( 'pum_rc_db_ver', false );
		$this->upgraded_from   = get_option( 'pum_rc_ver_upgraded_from' );
		$this->initial_version = get_option( 'pum_rc_initial_version' );

		/**
		 * If no version set check if a deprecated one exists.
		 */
		if ( empty( $this->version ) ) {
			$deprecated_ver = get_option( 'popmake_remote_content_version', false );
			// set to the deprecated version or last version that didn't have the version option set
			$this->version = $deprecated_ver ? $deprecated_ver : PUM_RC::$VER; // Since we had versioning in v1 if there isn't one stored its a new install.
		}

		/**
		 * Back fill the initial version with the oldest version we can detect.
		 */
		if ( ! get_option( 'pum_rc_initial_version' ) ) {

			$oldest_known = PUM_RC::$VER;

			if ( $this->version && version_compare( $this->version, $oldest_known, '<' ) ) {
				$oldest_known = $this->version;
			}

			if ( $this->upgraded_from && version_compare( $this->upgraded_from, $oldest_known, '<' ) ) {
				$oldest_known = $this->upgraded_from;
			}

			if ( get_option( 'popmake_remote_content_version', false ) && version_compare( 1.0, $oldest_known, '<' ) ) {
				$oldest_known = 1.0;
			}

			$this->initial_version = $oldest_known;

			// Only set this value if it doesn't exist.
			update_option( 'pum_rc_initial_version', $oldest_known );
		}

		if ( version_compare( $this->version, PUM_RC::$VER, '<' ) ) {
			// Allow processing of small core upgrades
			do_action( 'pum_update_rc_version', $this->version );

			// Save Upgraded From option
			update_option( 'pum_rc_ver_upgraded_from', $this->version );
			update_option( 'pum_rc_ver', PUM_RC::$VER );
			$this->upgraded_from = $this->version;
			$this->version       = PUM_RC::$VER;

			// Reset popup asset cache on update.
			PUM_AssetCache::reset_cache();
		}

		if ( ! $this->db_version ) {
			$this->db_version = PUM_RC::$DB_VER;
			update_option( 'pum_rc_db_ver', $this->db_version );
		}
	}

	/**
	 * @return bool
	 */
	public function needs_v1_1_popup_upgrade() {
		if ( pum_has_completed_upgrade( 'rc-v1_1-popups' ) ) {
			return false;
		}

		$needs_upgrade = get_transient( 'pum_rc_needs_1_1_upgrades' );

		if ( $needs_upgrade === false ) {
			$query = new WP_Query( array(
				'fields'     => 'ids',
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'     => 'popup_remote_content_enabled',
						'compare' => 'EXISTS',
					),
				),
			) );

			$needs_upgrade = $query->found_posts;
		}

		if ( $needs_upgrade <= 0 ) {
			pum_set_upgrade_complete( 'rc-v1_1-popups' );
			delete_transient( 'pum_rc_needs_1_1_upgrades' );

			return false;
		}

		set_transient( 'pum_rc_needs_1_1_upgrades', $needs_upgrade );

		return (bool) $needs_upgrade;

	}

	/**
	 * @param PUM_Upgrade_Registry $registry
	 */
	public function register_processes( PUM_Upgrade_Registry $registry ) {
		// v1.1 Upgrades
		$registry->add_upgrade( 'rc-v1_1-popups', array(
			'rules' => array(
				$this->needs_v1_1_popup_upgrade(),
			),
			'class' => 'PUM_RC_Upgrade_v1_1_Popups',
			'file'  => PUM_RC::$DIR . 'includes/upgrades/class-upgrade-v1_1-popups.php',
		) );
	}

}
