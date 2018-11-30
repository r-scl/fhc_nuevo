<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/public
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Utils')):

class THWCFE_Utils {
	const OPTION_KEY_CUSTOM_SECTIONS   = 'thwcfe_sections';
	const OPTION_KEY_SECTION_HOOK_MAP  = 'thwcfe_section_hook_map';
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwcfe_advanced_settings';
	
	static $PATTERN = array(			
			'/d/', '/j/', '/l/', '/z/', '/S/', //day (day of the month, 3 letter name of the day, full name of the day, day of the year, )			
			'/F/', '/M/', '/n/', '/m/', //month (Month name full, Month name short, numeric month no leading zeros, numeric month leading zeros)			
			'/Y/', '/y/' //year (full numeric year, numeric year: 2 digit)
		);
		
	static $REPLACE = array(
			'dd','d','DD','o','',
			'MM','M','m','mm',
			'yy','y'
		);
		
	public static function get_default_address_fields(){
		return array('country', 'address_1', 'address_2', 'city', 'state', 'postcode');
	}
	
	public static function is_default_address_field($field_name){
		$default_address_fields = self::get_default_address_fields();
		if($field_name && in_array($field_name, $default_address_fields) ){
			return true;
		}
		return false;
	}

	/**********************************************
	********* COMMON UTIL FUNCTIONS - START *******
	**********************************************/
	public static function get_cart_summary(){
		$items = WC()->cart->get_cart();
		
		$cart = array();
		$cart['products']   = array();
		$cart['categories'] = array();
		$cart['variations'] = array();
		
		foreach($items as $item => $values) { 
			$cart['products'][] = $values['product_id'];
			$cart['categories'] = array_merge($cart['categories'], self::get_product_categories($values['product_id']));
			if($values['variation_id']){
				$cart['variations'][] = $values['variation_id'];
			}
		} 
		
		$cart['products']   = array_values($cart['products']);
		$cart['categories'] = apply_filters('thwcfe_cart_product_categories', array_values($cart['categories']));
		$cart['variations'] = array_values($cart['variations']);
		
		return $cart;
	}

	public static function get_product_categories($product_id){
		$categories = array();
		$assigned_categories = wp_get_post_terms($product_id, 'product_cat');
		
		$ignore_translation = apply_filters('thwcfe_ignore_wpml_translation_for_product_category', false);
		$is_wpml_active = THWCFE_Utils::is_wpml_active();
		if($is_wpml_active && $ignore_translation){
			global $sitepress;
			global $icl_adjust_id_url_filter_off;
			$orig_flag_value = $icl_adjust_id_url_filter_off;
			$icl_adjust_id_url_filter_off = true;
			$default_lang = $sitepress->get_default_language();
		}
		
		foreach($assigned_categories as $category){
			$parent_categories = get_ancestors( $category->term_id, 'product_cat' ); 
			if(is_array($parent_categories)){
				foreach($parent_categories as $pcat_id){
					$pcat = get_term( $pcat_id, 'product_cat' );
					$categories[] = $pcat->slug;
				}
			}
			
			$cat_slug = $category->slug;
			if($is_wpml_active && $ignore_translation){
				$default_cat_id = icl_object_id($category->term_id, 'category', true, $default_lang);
				$default_cat = get_term($default_cat_id);
				$cat_slug = $default_cat->slug;
			}
			$categories[] = $cat_slug;
		}
		
		if($is_wpml_active && $ignore_translation){
			$icl_adjust_id_url_filter_off = $orig_flag_value;
		}
		
		return $categories;
	}

	public static function get_user_roles($user = false) {
		$user = $user ? new WP_User( $user ) : wp_get_current_user();
		
		if(!($user instanceof WP_User))
		   return false;
		   
		$roles = $user->roles;
		return $roles;
	}

	public static function get_product_tax_class_options() {
		if(self::woo_version_check()){
			return wc_get_product_tax_class_options();
		}else{
			$tax_classes           = WC_Tax::get_tax_classes();
			$tax_class_options     = array();
			$tax_class_options[''] = __( 'Standard', 'woocommerce' );
		
			if ( ! empty( $tax_classes ) ) {
				foreach ( $tax_classes as $class ) {
					$tax_class_options[ sanitize_title( $class ) ] = $class;
				}
			}
			return $tax_class_options;
		}
	}

	public static function get_option_text_from_value($field, $value){
		if(THWCFE_Utils_Field::is_valid_field($field) && apply_filters('thwcfe_display_option_text_instead_of_option_value', true)){
			$type = $field->get_property('type');
			if($type === 'select' || $type === 'radio'){
				$options = $field->get_property('options');
				if(is_array($options) && isset($options[$value]) && $options[$value]['text']){
					$value = $options[$value]['text'];
				}
			}else if($type === 'multiselect' || $type === 'checkboxgroup'){
				$options = $field->get_property('options');
				if(is_array($options)){
					$value = is_array($value) ? $value : array_map('trim', explode(',', $value));
					if(is_array($value)){
						foreach($value as $key => $option_value){
							if(isset($options[$option_value]) && $options[$option_value]['text']){
								$value[$key] = $options[$option_value]['text'];
							}
						}
					}
				}
			}
		}
		return $value;
	}

	//TODO check for any better approach.
	/*public static function get_product_categories($product){
		$categories = array();
		if($product->get_id()){
			$product_cat = wp_get_post_terms($product->get_id(), 'product_cat');
			if(is_array($product_cat)){
				foreach($product_cat as $category){
					$parent_cat = get_ancestors( $category->term_id, 'product_cat' ); 
					if(is_array($parent_cat)){
						foreach($parent_cat as $pcat_id){
							$pcat = get_term( $pcat_id, 'product_cat' );
							$categories[] = $pcat->slug;
						}
					}
					$categories[] = $category->slug;
				}
			}
		}
		return $categories;
	}*/
	/**********************************************
	********* COMMON UTIL FUNCTIONS - END *********
	**********************************************/


	/****************************************************
	********* ADVANCED SETTINGS FUNCTIONS - START *******
	****************************************************/
	public static function get_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		return empty($settings) ? false : $settings;
	}
	
	public static function get_settings($key){
		$settings = self::get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}

	public static function get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	/****************************************************
	********* ADVANCED SETTINGS FUNCTIONS - END *********
	****************************************************/
	

	/**************************************************
	********* CUSTOM SECTIONS & FIELDS - START ********
	**************************************************/
	public static function get_section_hook_map(){
		$section_hook_map = get_option(self::OPTION_KEY_SECTION_HOOK_MAP);	
		$section_hook_map = is_array($section_hook_map) ? $section_hook_map : array();
		return $section_hook_map;
	}
	
	public static function get_custom_sections(){
		$sections = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		return empty($sections) ? false : $sections;
	}

	public static function get_hooked_sections($hook_name){
		$sections = false;
		$section_hook_map = self::get_section_hook_map();
		
		if(is_array($section_hook_map) && isset($section_hook_map[$hook_name])){
			$sections = $section_hook_map[$hook_name];
		}	
						
		return empty($sections) ? false : $sections;
	}
		
	public static function get_checkout_section($section_name, $cart_info=false){
	 	if(isset($section_name) && !empty($section_name)){	
			$sections = self::get_custom_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section = $sections[$section_name];	
				if(THWCFE_Utils_Section::is_valid_section($section) && THWCFE_Utils_Section::is_show_section($section, $cart_info)){
					return $section;
				} 
			}
		}
		return false;
	}

	public static function get_fieldset_to_show($section){
		$cart_info = self::get_cart_summary();
		$fieldset = THWCFE_Utils_Section::get_fieldset($section, $cart_info);
		return !empty($fieldset) ? $fieldset : false;
	}

	public static function get_checkout_fields_full($return_fieldset=false){
		$fields = array();
		$sections = self::get_custom_sections();	
		if($sections){
			$sections = self::sort_sections($sections);
			foreach($sections as $sname => $section){
				$temp_fields = false;

				if($return_fieldset){
					$temp_fields = THWCFE_Utils_Section::get_fieldset($section);
				}else{
					$temp_fields = THWCFE_Utils_Section::get_fields($section);
				}
				
				if($temp_fields && is_array($temp_fields)){
					$fields = array_merge($fields, $temp_fields);
				}
			}
		}
		return $fields;
	}

	public static function exclude_address_fields($fields){
		$billing_keys  = self::get_settings('custom_billing_address_keys');
		$shipping_keys = self::get_settings('custom_shipping_address_keys');
		
		$address_fields = $billing_keys && is_array($billing_keys) ? $billing_keys : array();
		$address_fields = $shipping_keys && is_array($shipping_keys) ? array_merge($address_fields, $shipping_keys) : $address_fields;
		
		if(is_array($fields) && !empty($fields) && $address_fields && is_array($address_fields)){
			foreach($address_fields as $key) {
				unset($fields[$key]);
			}
		}
		return $fields;
	}

	public static function preare_fee_name($name, $label, $value, $fee_labels=false){
		if($label && $value && apply_filters('thwcfe_display_value_with_fee_label', true, $name)){
			$label .= ' ('.$value.')';
		}

		if(is_array($fee_labels) && in_array($label, $fee_labels)){
			$label = $name.'_'.$label;
		}

		return $label;
	}

	/*public static function get_checkout_section($section_name){
	 	if(isset($section_name) && !empty($section_name)){	
			$sections = self::get_custom_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section = $sections[$section_name];	
				if(THWCFE_Utils_Section::is_valid_section($section)){
					return $section;
				} 
			}
		}
		return false;
	}*/

	/*** FIELD FUNCTIONS ***/

	/*public static function get_fieldset_all($section, $exclude_disabled = true){
		$fieldset = array();
		if(THWCFE_Utils_Section::is_valid_enabled_section($section)){
			$fieldset = THWCFE_Utils_Section::get_fieldset($section, false, $exclude_disabled);
		}
		return !empty($fieldset) ? $fieldset : false;
	}*/
	
	/*public static function get_fieldset($section, $cart = false, $ignore_conditions = false){
		$fieldset = array();
		if(THWCFE_Utils_Section::is_valid_enabled_section($section)){
			if($ignore_conditions){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section);
			}else if(!$cart){
				$fieldset = THWCFE_Utils_Section::get_fieldset($section, false, false, false);
			}else{
				$products   = $cart['products'];
				$categories = $cart['categories'];
				$variations = $cart['variations'];
		
				$fieldset = THWCFE_Utils_Section::get_fieldset($section, $products, $categories, $variations);
			}
		}
		
		return !empty($fieldset) ? $fieldset : false;
	}*/
	/**************************************************
	********* CUSTOM SECTIONS & FIELDS - END **********
	**************************************************/
	
	/**************************************************
	********* FILE UPLOAD FUNCTIONS - START ***********
	**************************************************/
	public static function get_posted_file_type($file){
		$file_type = false;
		if($file && isset($file['name'])){
			//$file_type = isset($file['type']) ? $file['type'] : false;
			$file_type = pathinfo($file['name'], PATHINFO_EXTENSION);
			//var_dump($file_type);
		}
		return $file_type;
	}
	/**************************************************
	********* FILE UPLOAD FUNCTIONS - END *************
	**************************************************/


	/**********************************************
	********* OTHER PLUGIN SUPPORT - START ********
	**********************************************/
	//WMSC Support TODO
	public static function get_hooked_sections_($sections, $hook_name){
		$section_hook_map = self::get_section_hook_map();
		
		if(is_array($section_hook_map) && isset($section_hook_map[$hook_name])){
			$sections = $section_hook_map[$hook_name];
		}	
						
		return empty($sections) ? false : $sections;
	}

	public static function has_hooked_sections($result, $hook_name){
		$sections = array();
		$hooked_sections = self::get_hooked_sections($hook_name);
		$cart_info = self::get_cart_summary();

		if(is_array($hooked_sections)){
			foreach($hooked_sections as $key => $section_name){
				$section = self::get_checkout_section($section_name, $cart_info);
				$fieldset = THWCFE_Utils_Section::get_fieldset($section, $cart_info);
				if($section && !empty($fieldset)){
					$sections[$section_name] = $section;
				}
			}
		}
		return empty($sections) ? false : $result;
	}

	/* 
	 * To Access custom fields from outside the plugin.
	 * Added to the hook 'thwcfe_custom_checkout_fields'
	 */
	public static function get_custom_checkout_fields($ofields, $args=array()){
		$args = is_array($args) ? $args : array();
		$exc_addr_fields = isset($args['exclude_address_fields']) ? $args['exclude_address_fields'] : true;
		$display_fields_type = isset($args['display_fields_type']) ? $args['display_fields_type'] : false;

		$custom_fields = array();
		$fieldset = self::get_checkout_fields_full();

		if($exc_addr_fields){
			$fieldset = self::exclude_address_fields($fieldset);
		}
		
		foreach($fieldset as $key => $field) {
			if(THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_custom_field($field)){	
				$show_field = true;
				/*if($sent_to_admin && $field->get_property('show_in_email')){
					$show_field = true;					
				}else if(!$sent_to_admin && $field->get_property('show_in_email_customer')){
					$show_field = true;
				}*/

				if($display_fields_type === 'user_meta' && !THWCFE_Utils_Field::is_user_field($field)){
					continue;
				}
				if($display_fields_type === 'order_meta' && !THWCFE_Utils_Field::is_order_field($field)){
					continue;
				}
			
				if($show_field){	
					$label = $field->get_property('title') ? $field->get_property('title') : $key;
					if(apply_filters('thwcfe_esc_attr_custom_field_label_email', false)){
						$label = THWCFE_i18n::esc_attr__t($label);
					}else{
						$label = THWCFE_i18n::t($label);
					}
					
					$custom_field = array();
					$custom_field['label'] = $label;
					
					$custom_fields[$key] = $custom_field;
				}
			}
		}
		
		return array_merge($ofields, $custom_fields);
	}

	/* 
	 * To Access custom fields and values from outside the plugin.
	 * Added to the hook 'thwcfe_custom_checkout_fields_and_values'
	 */
	public static function get_custom_checkout_fields_and_values($ofields, $order_id, $args=array()){
		$args = is_array($args) ? $args : array();
		$exc_addr_fields = isset($args['exclude_address_fields']) ? $args['exclude_address_fields'] : true;
		$display_fields_type = isset($args['display_fields_type']) ? $args['display_fields_type'] : false;

		$custom_fields = array();
		$fieldset = self::get_checkout_fields_full();

		if($exc_addr_fields){
			$fieldset = self::exclude_address_fields($fieldset);
		}

		$is_nl2br = apply_filters('thwcfe_nl2br_custom_field_value', true);
		
		foreach($fieldset as $key => $field) {
			if(THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_custom_field($field)){	
				$show_field = true;
				/*if($sent_to_admin && $field->get_property('show_in_email')){
					$show_field = true;					
				}else if(!$sent_to_admin && $field->get_property('show_in_email_customer')){
					$show_field = true;
				}*/

				if($display_fields_type === 'user_meta' && !THWCFE_Utils_Field::is_user_field($field)){
					continue;
				}
				if($display_fields_type === 'order_meta' && !THWCFE_Utils_Field::is_order_field($field)){
					continue;
				}
			
				if($show_field){
					$type = $field->get_property('type');
					$value = get_post_meta($order_id, $key, true);
					
					if(!empty($value)){
						$value = self::get_option_text_from_value($field, $value);
						$value = is_array($value) ? implode(", ", $value) : $value;
						
						$label = $field->get_property('title') ? $field->get_property('title') : $key;
						if(apply_filters('thwcfe_esc_attr_custom_field_label_email', false)){
							$label = THWCFE_i18n::esc_attr__t($label);
						}else{
							$label = THWCFE_i18n::t($label);
						}
						
						if($is_nl2br && $type === 'textarea'){
							$value = nl2br($value);
						}
						
						$custom_field = array();
						$custom_field['label'] = $label;
						$custom_field['value'] = $value;
						
						$custom_fields[$key] = $custom_field;
					}
				}
			}
		}
		
		return array_merge($ofields, $custom_fields);
	}
	/**********************************************
	********* OTHER PLUGIN SUPPORT - END ********
	**********************************************/


	
	
	
	
	public static function delete_item_by_value($arr, $value){
		if(is_array($arr) && ($key = array_search($value, $arr)) !== false) {
			unset($arr[$key]);
		}
		return $arr;
	}
		
	public static function get_jquery_date_format($woo_date_format){				
		$woo_date_format = !empty($woo_date_format) ? $woo_date_format : wc_date_format();
		return preg_replace(self::$PATTERN, self::$REPLACE, $woo_date_format);	
	}
	
	public static function convert_cssclass_string($cssclass){
		if(!is_array($cssclass)){
			$cssclass = array_map('trim', explode(',', $cssclass));
		}
		
		if(is_array($cssclass)){
			$cssclass = implode(" ",$cssclass);
		}
		return $cssclass;
	}
	
	public static function convert_string_to_array($str, $separator = ','){
		if(!is_array($str)){
			$str = array_map('trim', explode($separator, $str));
		}
		return $str;
	}
	
	public static function is_subset_of($arr1, $arr2){
		if(is_array($arr1) && is_array($arr2)){
			foreach($arr2 as $value){
				if(!in_array($value, $arr1)){
					return false;
				}
			}
		}
		return true;
	}

	public static function remove_by_value($value, $arr){
		if(is_array($arr)){
			foreach (array_keys($arr, $value, true) as $key) {
			    unset($arr[$key]);
			}
		}
		return $arr;
	}
	
	public static function is_blank($value) {
		return empty($value) && !is_numeric($value);
	}
	
	public static function get_value_from_query_string($query_string, $key) {
		$value = false;
		
		if(is_string($query_string) && is_string($key)){
			$data = urldecode($query_string);
			$params = is_string($data) ? explode("&", $data) : array();
			
			foreach($params as $param) {
				$param_data = is_string($param) ? explode("=", $param) : array();
				
				if(isset($param_data[0]) && $param_data[0] === $key){
					$value = isset($param_data[1]) ? $param_data[1] : '';
				}
			}
		}
		return $value;
	}
	
	public static function sort_sections(&$sections){
		if(is_array($sections) && !empty($sections)){
			self::stable_uasort($sections, array('THWCFE_Utils', 'sort_sections_by_order'));
		}
		return $sections;
	}
	
	public static function sort_sections_by_order($a, $b){
		if(THWCFE_Utils_Section::is_valid_section($a) && THWCFE_Utils_Section::is_valid_section($b)){
			$order_a = is_numeric($a->get_property('order')) ? $a->get_property('order') : 0;
			$order_b = is_numeric($b->get_property('order')) ? $b->get_property('order') : 0;
			$order_a = (int)$order_a;
			$order_b = (int)$order_b;

			if($order_a == $order_b){
				return 0;
			}
			return ($order_a < $order_b) ? -1 : 1;
		}else{
			return 0;
		}
	}
	
	public static function stable_uasort(&$array, $cmp_function) {
		if(count($array) < 2) {
			return;
		}
		
		$halfway = count($array) / 2;
		$array1 = array_slice($array, 0, $halfway, TRUE);
		$array2 = array_slice($array, $halfway, NULL, TRUE);
	
		self::stable_uasort($array1, $cmp_function);
		self::stable_uasort($array2, $cmp_function);
		if(call_user_func_array($cmp_function, array(end($array1), reset($array2))) < 1) {
			$array = $array1 + $array2;
			return;
		}
		
		$array = array();
		reset($array1);
		reset($array2);
		while(current($array1) && current($array2)) {
			if(call_user_func_array($cmp_function, array(current($array1), current($array2))) < 1) {
				$array[key($array1)] = current($array1);
				next($array1);
			} else {
				$array[key($array2)] = current($array2);
				next($array2);
			}
		}
		while(current($array1)) {
			$array[key($array1)] = current($array1);
			next($array1);
		}
		while(current($array2)) {
			$array[key($array2)] = current($array2);
			next($array2);
		}
		return;
	}
	
	public static function is_wpml_active(){
		return function_exists('icl_object_id');
	}

	public static function is_thwmsc_active(){
		$active = is_plugin_active('woocommerce-multistep-checkout/woocommerce-multistep-checkout.php');
		return apply_filters('thwcfe_is_thwmsc_active', $active);
	}
	
	public static function woo_version_check( $version = '3.0' ) {
	  	if(function_exists( 'is_woocommerce_active' ) && is_woocommerce_active() ) {
			global $woocommerce;
			if( version_compare( $woocommerce->version, $version, ">=" ) ) {
		  		return true;
			}
	  	}
	  	return false;
	}
}

endif;