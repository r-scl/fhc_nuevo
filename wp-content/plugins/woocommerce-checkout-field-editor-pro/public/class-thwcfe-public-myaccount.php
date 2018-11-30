<?php
/**
 * The public-facing functionality of the plugin - My Account.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Public_MyAccount')):
 
class THWCFE_Public_MyAccount extends THWCFE_Public {

	public function __construct( $plugin_name, $version ) {
		parent::__construct($plugin_name, $version);
		
		add_action('after_setup_theme', array($this, 'define_public_hooks'));
	}

	public function enqueue_styles_and_scripts() {
		global $wp_scripts;
		
		if( is_wc_endpoint_url('edit-account') || is_wc_endpoint_url('edit-address') ){
			$debug_mode = apply_filters('thwcfe_debug_mode', false);
			$in_footer  = apply_filters('thwcfe_enqueue_script_in_footer', true);
			
			$suffix = $debug_mode ? '' : '.min';
			$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
			
			$this->enqueue_styles($suffix, $jquery_version, $in_footer);
			$this->enqueue_scripts($suffix, $jquery_version, $in_footer);
		}
	}
	
	private function enqueue_styles($suffix, $jquery_version, $in_footer) {
		wp_enqueue_style('thwcfe-timepicker-style', THWCFE_ASSETS_URL_PUBLIC.'js/timepicker/jquery.timepicker.css');
		wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'. $jquery_version .'/themes/smoothness/jquery-ui.css');
			
			
		/*wp_register_style('select2', THWEPO_WOO_ASSETS_URL.'/css/select2.css');
		
		wp_enqueue_style('select2');
		wp_enqueue_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'. $jquery_version .'/themes/smoothness/jquery-ui.css');
		wp_enqueue_style('thwepo-timepicker-style', THWEPO_ASSETS_URL_PUBLIC.'js/timepicker/jquery.timepicker.css');
		wp_enqueue_style('thwepo-public-style', THWEPO_ASSETS_URL_PUBLIC . 'css/thwepo-public'. $suffix .'.css', $this->version);*/
	}

	private function enqueue_scripts($suffix, $jquery_version, $in_footer) {
		wp_register_script('thwcfe-timepicker-script', THWCFE_ASSETS_URL_PUBLIC.'js/timepicker/jquery.timepicker.min.js', array('jquery'), '1.0.1', $in_footer);
			
		if( apply_filters( 'thwcfe_include_jquery_ui_i18n', TRUE ) ) {
			wp_register_script('jquery-ui-i18n', '//ajax.googleapis.com/ajax/libs/jqueryui/'.$jquery_version.'/i18n/jquery-ui-i18n.min.js',
			array('jquery','jquery-ui-datepicker'), $in_footer);
			wp_register_script('thwcfe-public-myaccount-script', THWCFE_ASSETS_URL_PUBLIC.'js/thwcfe-public-myaccount'. $suffix .'.js', 
			array('jquery-ui-i18n', 'select2'), THWCFE_VERSION, $in_footer);
		}else{
			wp_register_script('thwcfe-public-myaccount-script', THWCFE_ASSETS_URL_PUBLIC.'js/thwcfe-public-myaccount'. $suffix .'.js', 
			array('jquery','jquery-ui-datepicker', 'select2'), THWCFE_VERSION, $in_footer);
		}
		
		if(apply_filters('thwcfe_force_register_date_picker_script', false)){
			wp_register_script('thwcfe-datepicker-script', 'https://code.jquery.com/ui/'.$jquery_version.'/jquery-ui.js', array('jquery'), '1.0.1', $in_footer);
			wp_enqueue_script('thwcfe-datepicker-script');
		}
		
		wp_enqueue_script('thwcfe-timepicker-script');
		wp_enqueue_script('thwcfe-public-myaccount-script');
			
		$wcfe_var = array(
			'lang' => array( 
						'am' => THWCFE_i18n::t('am'), 
						'pm' => THWCFE_i18n::t('pm'),  
						'AM' => THWCFE_i18n::t('AM'), 
						'PM' => THWCFE_i18n::t('PM'),
						'decimal' => THWCFE_i18n::t('.'), 
						'mins' => THWCFE_i18n::t('mins'), 
						'hr'   => THWCFE_i18n::t('hr'), 
						'hrs'  => THWCFE_i18n::t('hrs'),
					),
			'language' 	  => THWCFE_i18n::get_locale_code(),
			'date_format' => THWCFE_Utils::get_jquery_date_format(wc_date_format()),
			'readonly_date_field' => apply_filters('thwcfe_date_picker_field_readonly', true),
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
		);
		wp_localize_script('thwcfe-public-myaccount-script', 'thwcfe_public_var', $wcfe_var);
	}
	
	public function define_public_hooks(){
		parent::define_public_hooks();
		$hp_dis_fields = apply_filters('thwcfe_myaccount_address_fields_hook_priority', 1100);
		
		add_filter('woocommerce_my_account_my_address_formatted_address', array($this, 'woo_my_account_my_address_formatted_address'), 20, 3);
		add_action('woocommerce_edit_account_form', array($this, 'woo_edit_account_form'));
		
		add_filter('woocommerce_before_edit_address_form_billing', array($this, 'output_disabled_field_names_hidden_field'));
		add_filter('woocommerce_before_edit_address_form_shipping', array($this, 'output_disabled_field_names_hidden_field'));
		add_filter('woocommerce_address_to_edit', array($this, 'woo_address_to_edit'), 10, 2);
		
		add_filter('woocommerce_billing_fields', array($this, 'prepare_address_fields_before_validate'), $hp_dis_fields, 2);
		add_filter('woocommerce_shipping_fields', array($this, 'prepare_address_fields_before_validate'), $hp_dis_fields, 2);
		
		add_filter('woocommerce_save_account_details_required_fields', array($this, 'woo_save_account_details_required_fields'));
		add_action('woocommerce_save_account_details_errors', array($this, 'woo_save_account_details_validation'), 10, 2);
		add_action('woocommerce_after_save_address_validation', array($this, 'woo_save_address_fields_validation'), 10, 3);
		
		add_action('woocommerce_save_account_details', array($this, 'woo_save_account_details'));
		add_action('woocommerce_customer_save_address', array($this, 'woo_customer_save_address'), 10, 2);
	}
	
	public function output_disabled_field_names_hidden_field(){
		echo '<input type="hidden" id="thwcfe_disabled_fields" name="thwcfe_disabled_fields" value=""/>';
	}
	
	public function woo_address_to_edit($address, $load_address = 'billing'){
		$section = THWCFE_Utils::get_checkout_section($load_address);
		$fieldset = THWCFE_Utils_Section::get_fieldset($section);
		$active_fieldset = THWCFE_Utils_Section::get_fieldset($section, false, false);
		$display_hidden_as_text = apply_filters('thwcfe_myaccount_display_hidden_field_as_text_field', false);
		
		if($fieldset && is_array($fieldset)){
			foreach($fieldset as $key => $field) {
				if(isset($address[$key]) && isset($field['custom']) && $field['custom']){
					if(apply_filters('thwcfe_ignore_custom_fields_in_address_to_edit', false)) {
						unset($address[$key]);
					}else if(is_array($active_fieldset) && !array_key_exists($key, $active_fieldset)) {
						unset($address[$key]);
					}else{
						$ftype = isset($field['type']) ? $field['type'] : 'text';
						
						if($ftype === 'hidden' && $display_hidden_as_text){
							$address[$key]['type'] = 'text';
						}

						if($ftype === 'file'){
							$address[$key]['type'] = 'file_default';
						}
						
						if(apply_filters('thwcfe_edit_address_ignore_row_split', true)){
							if(isset($field['class']) && is_array($field['class'])){
								$field['class'] = THWCFE_Utils::delete_item_by_value($field['class'], 'form-row-first');
								$field['class'] = THWCFE_Utils::delete_item_by_value($field['class'], 'form-row-last');
								$field['class'][] = 'form-row-wide';
							}
						}
						
						if(isset($field['has_non_ajax_rules']) && $field['has_non_ajax_rules']){
							$address[$key]['required'] = false;
							$address[$key]['validate'] = '';
						}
						
						if($ftype === 'label' || $ftype === 'heading'){
							$show_in_my_account_page = isset($field['show_in_my_account_page']) && $field['show_in_my_account_page'] ? 1 : 0;
							if(!$show_in_my_account_page){
								unset($address[$key]);
							}
						}else if(isset($field['user_meta']) && !$field['user_meta']){
							unset($address[$key]);
						}
						
						if(isset($address[$key]) && !apply_filters('thwcfe_show_in_my_account_page', true, $key)) {
							unset($address[$key]);
						}
					}
				}
			}
		}
		return $address;
	}
	
	public function woo_edit_account_form() {
	  	$user_id = get_current_user_id();
	  	$user = get_userdata($user_id);
	 
	  	if(!$user){
			return;
		}
		
		$display_hidden_as_text = apply_filters('thwcfe_myaccount_display_hidden_field_as_text_field', false);
			
		$sections = THWCFE_Utils::get_custom_sections();
		if($sections && is_array($sections)){
			$this->output_disabled_field_names_hidden_field();
			
			foreach($sections as $sname => $section) {
				if(THWCFE_Utils_Section::is_show_section($section)){
					$fieldset = THWCFE_Utils_Section::get_fieldset($section);
					$show_section = apply_filters('thwcfe_show_section_in_my_account_page', true, $sname);
					$has_user_fields = THWCFE_Utils_Section::has_user_fields($section, $fieldset);
					$show_section = $has_user_fields ? $show_section : false;
					
					if($fieldset && $sname != 'billing' && $sname != 'shipping' && $show_section){
						$show_section_title = $section->get_property('show_title_my_account');
						$show_section_title = apply_filters('thwcfe_show_section_title_in_my_account_page', $show_section_title, $sname);
						$wrap_with_div = THWCFE_Utils::get_settings('wrap_custom_sections_with_div');

						if($wrap_with_div === 'yes'){
							$css_class = $section->get_property('cssclass');
							$css_class = !empty($css_class) ? str_replace(" ", "", $css_class) : '';
							$css_class = !empty($css_class) ? str_replace(",", " ", $css_class) : '';
							
							$conditions_data = $this->prepare_ajax_conditions_data_section($section);
							if($conditions_data){
								$css_class .= empty($css_class) ? 'thwcfe-conditional-section' : ' thwcfe-conditional-section';
							}
							
							echo '<div class="thwcfe-checkout-section '. $css_class .' '. $section->get_property('name') .'" '.$conditions_data.'>';
						}	

						if($show_section_title){
							echo THWCFE_Utils_Section::get_title_html($section);
						}
						
						foreach($fieldset as $key => $field) {
							if(isset($field['custom']) && $field['custom']){
								$ftype = isset($field['type']) ? $field['type'] : 'text';
									
								if(isset($field['user_meta']) && $field['user_meta']){
									$value = get_user_meta( $user_id, $key, true );
									$value = isset($_POST[$key]) ? $_POST[$key] : $value;
									$value = is_array($value) ? implode(",", $value) : $value;
									//$label = $this->get_field_display_name($field);
									
									if($ftype === 'hidden' && $display_hidden_as_text){
										$field['type'] = 'text';
									}

									if($ftype === 'file'){
										$field['type'] = 'file_default';
									}
									
									if(isset($field['has_non_ajax_rules']) && $field['has_non_ajax_rules']){
										$field['required'] = false;
										$field['validate'] = '';
									}
									
									if(apply_filters('thwcfe_show_in_my_account_page', true, $key)) {
										woocommerce_form_field( $key, $field, $value );
									}
									
								}else if(($ftype === 'label' || $ftype === 'heading') && (isset($field['show_in_my_account_page']) && $field['show_in_my_account_page'])){
									woocommerce_form_field( $key, $field, false );
								}
							}
						}

						if($wrap_with_div === 'yes'){
							echo '</div>';
						}
					}
				}
			}
		}
	}
	
	public function prepare_address_fields_before_validate($fields, $country){
		if( is_wc_endpoint_url('edit-address') ){
			$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
			$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : false;
		
			if($fields && is_array($fields) && is_array($dis_fields) && !empty($dis_fields) ){
				foreach($dis_fields as $fname){
					if(in_array($fname, $dis_fields)){
						unset($fields[$fname]);
					}
				}
			}
			
			foreach($fields as $key => &$field){
				if(isset($field['has_non_ajax_rules']) && $field['has_non_ajax_rules']){
					$field['required'] = false;
					$field['validate'] = '';
				}
				
				if((isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && !$field['user_meta']) 
					|| !apply_filters('thwcfe_show_in_my_account_page', true, $key)){
					unset($fields[$key]);
				}
			}
		}
		return $fields;
	}
	
	public function woo_save_account_details_required_fields($required_fields){
		$user_id = get_current_user_id();
		$user = get_userdata($user_id);
	 
		if(!$user){
			return;
		}
			
		$sections = THWCFE_Utils::get_custom_sections();
		if($sections && is_array($sections)){
			$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
			$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();
			
			foreach($sections as $sname => $section) {
				$show_section = THWCFE_Utils_Section::is_show_section($section);
				$show_section = apply_filters('thwcfe_show_section_in_my_account_page', $show_section, $sname);
				if($sname != 'billing' && $sname != 'shipping' && $show_section){
					$fieldset = THWCFE_Utils_Section::get_fieldset($section);
					if($fieldset && is_array($fieldset)){
						foreach($fieldset as $key => $field) {
							if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']
								&& apply_filters('thwcfe_show_in_my_account_page', true, $key)){
								if(!in_array($key, $dis_fields) && $field['required']){
									if(isset($field['has_non_ajax_rules']) && !$field['has_non_ajax_rules']){
										$required_fields[$key] = $field['title'];
									}
								}
							}
						}
					}
				}
			}
		}
		return $required_fields;
	}

	public function woo_save_account_details_validation($errors, $user){
		if($errors && $user){
			$sections = THWCFE_Utils::get_custom_sections();
			if($sections && is_array($sections)){
				$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
				$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();
				
				foreach($sections as $sname => $section) {
					$show_section = apply_filters('thwcfe_show_section_in_my_account_page', true, $sname);
					if($sname != 'billing' && $sname != 'shipping' && $show_section){
						$fieldset = THWCFE_Utils_Section::get_fieldset($section);
						if($fieldset && is_array($fieldset)){
							foreach($fieldset as $key => $field) {
								if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']
									&& apply_filters('thwcfe_show_in_my_account_page', true, $key)){
									if(!in_array($key, $dis_fields)){
										if(isset($field['has_non_ajax_rules']) && !$field['has_non_ajax_rules']){
											$this->validate_custom_field($field, $_POST, $errors);
										}
									}
								}
							}
						}
					}
				}
			}			
		}
	}

	public function woo_save_address_fields_validation($user_id, $load_address, $address){
		if($user_id && $load_address){
			$sections = THWCFE_Utils::get_custom_sections();
			if($sections && is_array($sections) && isset($sections[$load_address])){
				$section = $sections[$load_address];
				$disabled_fields = isset( $_POST['thwcfe_disabled_fields'] ) ? wc_clean( $_POST['thwcfe_disabled_fields'] ) : '';
				$dis_fields = $disabled_fields ? explode(",", $disabled_fields) : array();
					
				$show_section = apply_filters('thwcfe_show_section_in_my_account_page', true, $load_address);
				if($show_section){
					$fieldset = THWCFE_Utils_Section::get_fieldset($section);
					if($fieldset && is_array($fieldset)){
						foreach($fieldset as $key => $field) {
							if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']
								&& apply_filters('thwcfe_show_in_my_account_page', true, $key)){
								if(!in_array($key, $dis_fields)){
									if(isset($field['has_non_ajax_rules']) && !$field['has_non_ajax_rules']){
										$this->validate_custom_field($field, $_POST);
									}
								}
							}
						}
					}
				}
			}			
		}
	}
	
	public function woo_save_account_details( $user_id ) {
		$sections = THWCFE_Utils::get_custom_sections();
		foreach($sections as $sname => $section) {
			$fieldset = THWCFE_Utils_Section::get_fieldset($section, false, true);
			
			if($fieldset){
				foreach($fieldset as $key => $field) {
					if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']){	
						if(isset($_POST[ $key ])){
							$value = is_array($_POST[ $key ]) ? implode(',', $_POST[ $key ]) : $_POST[ $key ];
							update_user_meta( $user_id, $key, htmlentities( $value ) );
						}
					}
				}
			}
		}
	}
	
	public function woo_customer_save_address( $user_id, $load_address ) {
		$sections = THWCFE_Utils::get_custom_sections();
		foreach($sections as $sname => $section) {
			if($sname === $load_address){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section);
				
				if($fieldset){
					foreach($fieldset as $key => $field) {
						if(isset($field['custom']) && $field['custom'] && isset($field['user_meta']) && $field['user_meta']){
							$type = isset($field['type']) ? $field['type'] : 'text';

							if($type === 'file'){
								if(isset($_FILES[$key])){
									$file = $_FILES[$key];
									$result = uploaded_file($file, $key, $field);

									if($result['response'] === 'SUCCESS' && $result['uploaded']){
										$value = json_encode($result['uploaded']);
										update_user_meta($user_id, $key, $value);
									}
								}
							}else if(isset($_POST[$key])){
								$value = is_array($_POST[ $key ]) ? implode(',', $_POST[ $key ]) : $_POST[ $key ];
								update_user_meta( $user_id, $key, htmlentities( $value ) );
							}
						}
					}
				}
			}
		}
	}
	
	/*public function get_posted_value($name, $type = false){
		$is_posted = isset($_POST[$name]) || isset($_REQUEST[$name]) ? true : false;
		$value = false;
		
		if($is_posted){
			$value = isset($_POST[$name]) && $_POST[$name] ? $_POST[$name] : false;
			$value = empty($value) && isset($_REQUEST[$name]) ? $_REQUEST[$name] : $value;
		}
		return $value;
	}*/
}

endif;