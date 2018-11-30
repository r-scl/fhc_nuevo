<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin')):
 
class THWCFE_Admin {
	private $plugin_name;
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.9.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	
	public function enqueue_styles_and_scripts($hook) {
		if(strpos($hook, 'page_th_checkout_field_editor_pro') === false) {
			return;
		}
		$debug_mode = apply_filters('thwcfe_debug_mode', false);
		$suffix = $debug_mode ? '' : '.min';
		
		$this->enqueue_styles($suffix);
		$this->enqueue_scripts($suffix);
	}
	
	private function enqueue_styles($suffix) {
		wp_enqueue_style('woocommerce_admin_styles');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('thwcfe-admin-style', THWCFE_ASSETS_URL_ADMIN . 'css/thwcfe-admin'. $suffix .'.css', $this->version);
	}

	private function enqueue_scripts($suffix) {
		$deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip', 'woocommerce_admin', 'select2', 'wp-color-picker');
			
		//wp_enqueue_script( 'thwcfe-admin-script', THWCFE_ASSETS_URL_ADMIN . 'js/thwcfe-admin'. $suffix .'.js', $deps, $this->version, false );
		$this->version = '1.9.3.16';


		wp_enqueue_script('thwcfe-settings-base-script', THWCFE_ASSETS_URL_ADMIN . 'js/thwcfe-settings-base.js', $deps, $this->version);
		wp_enqueue_script('thwcfe-admin-script', THWCFE_ASSETS_URL_ADMIN . 'js/thwcfe-checkout-field-editor-admin.js', array('thwcfe-settings-base-script'), $this->version);
		wp_enqueue_script('thwcfe-settings-advanced-script', THWCFE_ASSETS_URL_ADMIN . 'js/thwcfe-settings-advanced.js', array('thwcfe-settings-base-script'), $this->version);
		
		$wcfe_var = array(
            'admin_url' => admin_url(),
            'ajaxurl'   => admin_url( 'admin-ajax.php' ),
			'sanitize_names' => apply_filters("thwcfe_sanitize_field_names", true),
        );
		wp_localize_script('thwcfe-admin-script', 'wcfe_var', $wcfe_var);
	}
	
	public function admin_menu() {
		$this->screen_id = add_submenu_page('woocommerce', THWCFE_i18n::t('WooCommerce Checkout Field Editor Pro'), THWCFE_i18n::t('Checkout Form'), 'manage_woocommerce', 'th_checkout_field_editor_pro', array($this, 'output_settings'));
	}
	
	public function add_screen_id($ids){
		$ids[] = 'woocommerce_page_th_checkout_field_editor_pro';
		$ids[] = strtolower(THWCFE_i18n::t('WooCommerce')) .'_page_th_checkout_field_editor_pro';

		return $ids;
	}

	public function plugin_action_links($links) {
		$settings_link = '<a href="'.admin_url('admin.php?page=th_checkout_field_editor_pro').'">'. THWCFE_i18n::t('Settings') .'</a>';
		array_unshift($links, $settings_link);
		return $links;
	}
	
	public function plugin_row_meta( $links, $file ) {
		if(THWCFE_BASE_NAME == $file) {
			$doc_link = esc_url('https://www.themehigh.com/help-guides/woocommerce-checkout-field-editor/');
			$support_link = esc_url('https://www.themehigh.com/help-guides/');
				
			$row_meta = array(
				'docs' => '<a href="'.$doc_link.'" target="_blank" aria-label="'.THWCFE_i18n::esc_attr__t('View plugin documentation').'">'.THWCFE_i18n::esc_html__t('Docs').'</a>',
				'support' => '<a href="'.$support_link.'" target="_blank" aria-label="'. THWCFE_i18n::esc_attr__t('Visit premium customer support' ) .'">'. THWCFE_i18n::esc_html__t('Premium support') .'</a>',
			);

			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	
	public function output_settings(){
		$tab  = isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
		
		if($tab === 'advanced_settings'){			
			$advanced_settings = THWCFE_Admin_Settings_Advanced::instance();	
			$advanced_settings->render_page();
			// $advanced_settings = WCFE_Checkout_Field_Editor_Advanced_Settings::instance();	
			// $advanced_settings->output_page();			
		}else if($tab === 'license_settings'){			
			$license_settings = THWCFE_Admin_Settings_License::instance();	
			$license_settings->render_page();	
			// $license_settings = WCFE_License_Settings::instance();	
			// $license_settings->output_page();
		}else{
			$general_settings = THWCFE_Admin_Settings_General::instance();	
			$general_settings->init();
			// $admin_instance = WCFE_Checkout_Field_Editor_Settings::instance();	
			// $admin_instance->output_page();
		}
	}
}

endif;