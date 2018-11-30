<?php
/**
 * The admin general settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      2.9.0
 *
 * @package    woocommerce-checkout-field-editor-pro
 * @subpackage woocommerce-checkout-field-editor-pro/admin
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWCFE_Admin_Settings_General')):

class THWCFE_Admin_Settings_General extends THWCFE_Admin_Settings {
	protected static $_instance = null;
	public $field_factory = NULL;
	
	private $cell_props_L = array();
	private $cell_props_R = array();
	private $cell_props_CB = array();
	private $cell_props_CBS = array();
	private $cell_props_CBL = array();
	private $cell_props_CP = array();

	public function __construct() {
		parent::__construct();
		$this->page_id    = 'fields';
		$this->section_id = 'billing';
		
		//$this->move_fields_from_one_to_another();
	}
	
	//Example function to move fields from one section to another.
	private function move_fields_from_one_to_another(){
		$section_from = $this->get_checkout_section('billing');
		$section_to = $this->get_checkout_section('activities');
		
		$field_set = THWCFE_Utils_Section::get_fields($section_from);
		
		$fields = array('fall_sports', 'winter_sports', 'spring_sport', 'middle_school', 'orange_community_ed');
		foreach($fields as $fname){
			$field = $field_set[$fname];
			$section_to = THWCFE_Utils_Section::add_field($section_to, $field);
		}
		$result = $this->update_section($section_to);
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}	

	public function init(){
		$this->init_constants();
		$this->wpml_register_address_strings();

		$this->locale_fields = array(
			'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city',
			'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city',
			'order_comments'
		);

		$this->render_page();
	}

	public function define_admin_hooks(){
		if(THWCFE_Utils::get_settings('lazy_load_products') != 'yes'){
			add_filter('thpladmin_load_products', array('WCFE_Checkout_Fields_Utils', 'load_products'));
		}
		
		if(THWCFE_Utils::get_settings('lazy_load_categories') != 'yes'){
			add_filter('thpladmin_load_products_cat', array('WCFE_Checkout_Fields_Utils', 'load_products_cat'));
		}
		
		add_filter('thpladmin_load_user_roles', array('WCFE_Checkout_Fields_Utils', 'load_user_roles'));		
		
		// Show in order details page
		add_action('woocommerce_admin_order_data_after_order_details', array($this, 'woo_admin_order_data_after_order_details'), 20, 1);
		add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'woo_admin_order_data_after_billing_address'), 20, 1);
		add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'woo_admin_order_data_after_shipping_address'), 20, 1);
		add_filter('postmeta_form_keys', array($this, 'postmeta_form_keys'), 10, 2);
		
		add_filter("woocommerce_customer_meta_fields", array($this, 'woo_customer_meta_fields'), 11, 1 );
		
		// Shop order columns
		add_action('manage_edit-shop_order_columns', array($this, 'manage_edit_shop_order_columns'), 11, 1);
		add_action('manage_shop_order_posts_custom_column', array($this, 'manage_shop_order_posts_custom_column'), 11, 2 );
		add_filter("manage_edit-shop_order_sortable_columns", array($this, 'manage_edit_shop_order_sortable_columns'), 11, 1 );
		add_filter('posts_clauses', array($this, 'posts_clauses_sort_shop_orders'), 10, 2);
		//add_action('pre_get_posts', array($this, 'pre_get_posts'));
		//add_filter('posts_orderby', array($this, 'posts_orderby'), 10, 2);
		
		// Formatted addresses
		add_filter('woocommerce_localisation_address_formats', array($this, 'woo_localisation_address_formats'), 20, 2 ); 
		add_filter('woocommerce_formatted_address_replacements', array($this, 'woo_formatted_address_replacements'), 20, 2 ); 
		add_filter('woocommerce_order_formatted_billing_address', array($this, 'woo_order_formatted_billing_address'), 20, 2 );
		add_filter('woocommerce_order_formatted_shipping_address', array($this, 'woo_order_formatted_shipping_address'), 20, 2 );
		//add_filter('woocommerce_my_account_my_address_formatted_address', array($this, 'woo_my_account_my_address_formatted_address'), 20, 3 );
		//add_filter('woocommerce_formatted_address_force_country_display', '__return_true' );
		
		// Show in Email
		add_filter('woocommerce_email_customer_details_fields', array($this, 'woo_hide_default_customer_fields_in_emails'), 10, 3);
		if(THWCFE_Utils::get_settings('custom_fields_position_email') === 'woocommerce_email_customer_details_fields' ){
			add_filter('woocommerce_email_customer_details_fields', array($this, 'woo_display_custom_fields_in_emails'), 10, 3);
		}else{
			add_filter('woocommerce_email_order_meta_fields', array($this, 'woo_display_custom_fields_in_emails'), 10, 3);
		}
		
		//add_filter('woocommerce_attribute_label', array($this, 'woo_attribute_label'), 10, 2 );
		
		//To get checkout fields & values outside the plugin
		//add_filter('thwcfe_custom_checkout_fields_and_values', array('THWCFE_Utils', 'get_custom_checkout_fields_and_values'), 10, 3);
	}
	
	public function init_constants(){
		$this->cell_props_L = array( 
			'label_cell_props' => 'width="13%"', 
			'input_cell_props' => 'width="34%"', 
			'input_width' => '250px',  
		);
		
		$this->cell_props_R = array( 
			'label_cell_props' => 'width="13%"', 
			'input_cell_props' => 'width="34%"', 
			'input_width' => '250px', 
		);
		
		$this->cell_props_CB = array( 
			'label_props' => 'style="margin-right: 40px;"', 
		);
		$this->cell_props_CBS = array( 
			'label_props' => 'style="margin-right: 15px;"', 
		);
		$this->cell_props_CBL = array( 
			'label_props' => 'style="margin-right: 52px;"', 
		);
		
		$this->cell_props_CP = array(
			'label_cell_props' => 'width="13%"', 
			'input_cell_props' => 'width="34%"', 
			'input_width' => '225px',
		);
		
		$this->section_form_props = $this->get_section_form_props();
		
		$this->field_form_props = $this->get_field_form_props();
		$this->field_form_props_display = $this->get_field_form_props_display();
	}
	
	public function wpml_register_address_strings(){
		THWCFE_i18n::wpml_register_string('Field Title - '.'Canton', 'Canton' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'County', 'County' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'District', 'District' );		
		THWCFE_i18n::wpml_register_string('Field Title - '.'Municipality', 'Municipality' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'Prefecture', 'Prefecture' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'Province', 'Province' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'Region', 'Region' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'State', 'State' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'State / Zone', 'State / Zone' );
		
		THWCFE_i18n::wpml_register_string('Field Title - '.'Suburb', 'Suburb' );	
		THWCFE_i18n::wpml_register_string('Field Title - '.'Town / District', 'Town / District' );
		
		THWCFE_i18n::wpml_register_string('Field Title - '.'Postcode', 'Postcode' );
		THWCFE_i18n::wpml_register_string('Field Title - '.'ZIP', 'ZIP' );
	}
	
	public function get_section_form_props(){
		$positions = $this->get_available_positions();
		$html_text_tags = $this->get_label_types();
		
		return array(
			'name' 		 => array('name'=>'name', 'label'=>'Name/ID', 'type'=>'text', 'required'=>1),
			'position' 	 => array('name'=>'position', 'label'=>'Display Position', 'type'=>'select', 'options'=>$positions, 'required'=>1),
			//'box_type' 	 => array('name'=>'box_type', 'label'=>'Box Type', 'type'=>'select', 'options'=>$box_types),
			'cssclass' 	 => array('name'=>'cssclass', 'label'=>'CSS Class', 'type'=>'text'),
			'show_title' => array('name'=>'show_title', 'label'=>'Show section title in checkout page.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1),
			'show_title_my_account' => array('name'=>'show_title_my_account', 'label'=>'Show section title in my account page.', 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1),
			
			'title' 		   => array('name'=>'title', 'label'=>'Title', 'type'=>'text'),
			//'title_position' => array('name'=>'title_position', 'label'=>'Title Position', 'type'=>'select', 'options'=>$title_positions),
			'title_type' 	   => array('name'=>'title_type', 'label'=>'Title Type', 'type'=>'select', 'value'=>'h3', 'options'=>$html_text_tags),
			'title_color' 	   => array('name'=>'title_color', 'label'=>'Title Color', 'type'=>'colorpicker'),
			'title_class' 	   => array('name'=>'title_class', 'label'=>'Title Class', 'type'=>'text'),
			
			'subtitle' 			  => array('name'=>'subtitle', 'label'=>'Subtitle', 'type'=>'text'),
			//'subtitle_position' => array('name'=>'subtitle_position', 'label'=>'Subtitle Position', 'type'=>'select', 'options'=>$title_positions),
			'subtitle_type' 	  => array('name'=>'subtitle_type', 'label'=>'Subtitle Type', 'type'=>'select', 'value'=>'h3', 'options'=>$html_text_tags),
			'subtitle_color' 	  => array('name'=>'subtitle_color', 'label'=>'Subtitle Color', 'type'=>'colorpicker'),
			'subtitle_class' 	  => array('name'=>'subtitle_class', 'label'=>'Subtitle Class', 'type'=>'text'),
		);
	}
	
	/*public function get_field_form_props_display(){
		return array(
			'name'  => array('name'=>'name', 'type'=>'text'),
			'type'  => array('name'=>'type', 'type'=>'select'),
			'title' => array('name'=>'title', 'type'=>'text'),
			'placeholder' => array('name'=>'placeholder', 'type'=>'text'),
			'validate' => array('name'=>'validate', 'type'=>'text'),
			'required' => array('name'=>'required', 'type'=>'checkbox', 'status'=>1),
			'clear'    => array('name'=>'clear', 'type'=>'checkbox', 'status'=>1),
			'enabled'  => array('name'=>'enabled', 'type'=>'checkbox', 'status'=>1),
		);
	}*/
	
	public function get_field_form_props(){
		$field_types = $this->get_field_types();
		
		$validations = array(
			'email' => 'Email',
			'phone' => 'Phone',
			'postcode' => 'Postcode',
			'state' => 'State',
			'number' => 'Number',
		);
		$custom_validators = THWCFE_Utils::get_settings('custom_validators');
		if(is_array($custom_validators)){
			foreach( $custom_validators as $vname => $validator ) {
				$validations[$vname] = $validator['label'];
			}
		}
		
		$confirm_validators = THWCFE_Utils::get_settings('confirm_validators');
		if(is_array($confirm_validators)){
			foreach( $confirm_validators as $vname => $validator ) {
				$validations[$vname] = $validator['label'];
			}
		}
		
		$price_types = array(
			'normal' => 'Fixed',
			'custom' => 'Custom',
			'percentage' => 'Percentage of Cart Contents Total',
			'percentage_subtotal' => 'Percentage of Subtotal',
			'percentage_subtotal_ex_tax' => 'Percentage of Subtotal Ex Tax',
			'dynamic' => 'Dynamic',
		);
		
		$week_days = array(
			'sun' => 'Sunday',
			'mon' => 'Monday',
			'tue' => 'Tuesday',
			'wed' => 'Wednesday',
			'thu' => 'Thursday',
			'fri' => 'Friday',
			'sat' => 'Saturday',
		);
		
		$html_text_tags = $this->get_label_types();
		//$title_positions = array( 'left' => 'Left of the field', 'above' => 'Above field', );
		
		$time_formats = array(
			'h:i A' => '12-hour format',
			'H:i' => '24-hour format',
		);
		
		$hint_accept = "Specify allowed file types separated by comma (e.g. png,jpg,docx,pdf).";
		
		$hint_price = "If taxable, always enter price exclusive of tax.";
		$hint_default_date = "Specify a date in the current dateFormat, or number of days from today (e.g. +7) or a string of values and periods ('y' for years, 'm' for months, 'w' for weeks, 'd' for days, e.g. '+1m +7d'), or leave empty for today.";
		$hint_date_format = "The format for parsed and displayed dates.";
		$hint_min_date = "The minimum selectable date. Specify a date in yyyy-mm-dd format, or number of days from today (e.g. -7) or a string of values and periods ('y' for years, 'm' for months, 'w' for weeks, 'd' for days, e.g. '-1m -7d'), or leave empty for no minimum limit.";
		$hint_max_date = "The maximum selectable date. Specify a date in yyyy-mm-dd format, or number of days from today (e.g. +7) or a string of values and periods ('y' for years, 'm' for months, 'w' for weeks, 'd' for days, e.g. '+1m +7d'), or leave empty for no maximum limit.";
		$hint_year_range = "The range of years displayed in the year drop-down: either relative to today's year ('-nn:+nn' e.g. -5:+3), relative to the currently selected year ('c-nn:c+nn' e.g. c-10:c+10), absolute ('nnnn:nnnn' e.g. 2002:2012), or combinations of these formats ('nnnn:+nn' e.g. 2002:+3). Note that this option only affects what appears in the drop-down, to restrict which dates may be selected use the minDate and/or maxDate options.";
		$hint_number_of_months = "The number of months to show at once.";
		$hint_disabled_dates = "Specify dates in yyyy-mm-dd format separated by comma.";
		
		return array(
			'name' 		  => array('type'=>'text', 'name'=>'name', 'label'=>'Name', 'required'=>1),
			'type' 		  => array('type'=>'select', 'name'=>'type', 'label'=>'Field Type', 'required'=>1, 'options'=>$field_types, 
								'onchange'=>'thwcfeFieldTypeChangeListner(this)'),
			'value' 	  => array('type'=>'text', 'name'=>'value', 'label'=>'Default Value'),
			'placeholder' => array('type'=>'text', 'name'=>'placeholder', 'label'=>'Placeholder'),
			'description' => array('type'=>'text', 'name'=>'description', 'label'=>'Description'),
			'validate'    => array('type'=>'multiselect', 'name'=>'validate', 'label'=>'Validations', 'placeholder'=>'Select validations', 'options'=>$validations),
			'cssclass'    => array('type'=>'text', 'name'=>'cssclass', 'label'=>'Wrapper Class', 'placeholder'=>'Seperate classes with comma', 'value'=>'form-row-wide'),
			'input_class' => array('type'=>'text', 'name'=>'input_class', 'label'=>'Input Class', 'placeholder'=>'Seperate classes with comma'),
			
			'price'        => array('type'=>'text', 'name'=>'price', 'label'=>'Price', 'placeholder'=>'Price', 'hint_text'=>$hint_price),
			'price_unit'   => array('type'=>'text', 'name'=>'price_unit', 'label'=>'Unit', 'placeholder'=>'Unit'),
			'price_type'   => array('type'=>'select', 'name'=>'price_type', 'label'=>'Price Type', 'options'=>$price_types, 'onchange'=>'thwcfePriceTypeChangeListener(this)'),
			'taxable'      => array('type'=>'select', 'name'=>'taxable', 'label'=>'Taxable', 'options'=>array('no' => 'No', 'yes' => 'Yes')),
			'tax_class'    => array('type'=>'select', 'name'=>'tax_class', 'label'=>'Tax Class', 'options'=>THWCFE_Utils::get_product_tax_class_options()),
			
			'order_meta' => array('type'=>'checkbox', 'name'=>'order_meta', 'label'=>'Order Meta Data', 'value'=>'yes', 'checked'=>1),
			'user_meta'  => array('type'=>'checkbox', 'name'=>'user_meta', 'label'=>'User Meta Data', 'value'=>'yes', 'checked'=>0),
			
			'checked'   => array('type'=>'checkbox', 'name'=>'checked', 'label'=>'Checked by default', 'value'=>'yes', 'checked'=>1),
			'required'  => array('type'=>'checkbox', 'name'=>'required', 'label'=>'Required', 'value'=>'yes', 'checked'=>0, 'status'=>1),
			'clear' 	=> array('type'=>'checkbox', 'name'=>'clear', 'label'=>'Clear Row', 'value'=>'yes', 'checked'=>0, 'status'=>1),
			'enabled'   => array('type'=>'checkbox', 'name'=>'enabled', 'label'=>'Enabled', 'value'=>'yes', 'checked'=>1, 'status'=>1),
			
			'show_in_email' => array('type'=>'checkbox', 'name'=>'show_in_email', 'label'=>'Display in Admin Emails', 'value'=>'yes', 'checked'=>1),
			'show_in_email_customer' => array('type'=>'checkbox', 'name'=>'show_in_email_customer', 'label'=>'Display in Customer Emails', 'value'=>'yes', 'checked'=>1),
			'show_in_order' => array('type'=>'checkbox', 'name'=>'show_in_order', 'label'=>'Display in Order Detail Pages', 'value'=>'yes', 'checked'=>1),
			'show_in_thank_you_page' => array('type'=>'checkbox', 'name'=>'show_in_thank_you_page', 'label'=>'Display in Thank You Page', 'value'=>'yes', 'checked'=>1),
			'show_in_my_account_page' => array('type'=>'checkbox', 'name'=>'show_in_my_account_page', 'label'=>'Display in My Account Page', 'value'=>'yes', 'checked'=>0),
			
			'title'          => array('type'=>'text', 'name'=>'title', 'label'=>'Label'),
			'title_type'     => array('type'=>'select', 'name'=>'title_type', 'label'=>'Title Type', 'value'=>'h3', 'options'=>$html_text_tags),
			'title_color'    => array('type'=>'colorpicker', 'name'=>'title_color', 'label'=>'Title Color'),
			'title_class'    => array('type'=>'text', 'name'=>'title_class', 'label'=>'Label Class', 'placeholder'=>'Seperate classes with comma'),
			
			'subtitle'       => array('type'=>'text', 'name'=>'subtitle', 'label'=>'Subtitle'),
			'subtitle_type'  => array('type'=>'select', 'name'=>'subtitle_type', 'label'=>'Subtitle Type', 'value'=>'label', 'options'=>$html_text_tags),
			'subtitle_color' => array('type'=>'colorpicker', 'name'=>'subtitle_color', 'label'=>'Subtitle Color'),
			'subtitle_class' => array('type'=>'text', 'name'=>'subtitle_class', 'label'=>'Subtitle Class', 'placeholder'=>'Seperate classes with comma'),
			
			'minlength'   => array('type'=>'text', 'name'=>'minlength', 'label'=>'Min. Length', 'hint_text'=>'The minimum number of characters allowed'),
			'maxlength'   => array('type'=>'text', 'name'=>'maxlength', 'label'=>'Max. Length', 'hint_text'=>'The maximum number of characters allowed'),
			//'repeat_x'    => array('type'=>'text', 'name'=>'repeat_x', 'label'=>'Repeat X'),
			
			'maxsize' => array('type'=>'text', 'name'=>'maxsize', 'label'=>'Maxsize(in MB)'),
			'accept'  => array('type'=>'text', 'name'=>'accept', 'label'=>'Accepted File Types', 'placeholder'=>'eg: png,jpg,docx,pdf', 'hint_text'=>$hint_accept),

			'country_field' => array('type'=>'text', 'name'=>'country_field', 'label'=>'Country Field Name'),
						
			'default_date' => array('type'=>'text','name'=>'default_date', 'label'=>'Default Date','placeholder'=>"Leave empty for today's date",'hint_text'=>$hint_default_date),
			'date_format'  => array('type'=>'text', 'name'=>'date_format', 'label'=>'Date Format', 'value'=>'dd/mm/yy', 'hint_text'=>$hint_date_format),
			'min_date'     => array('type'=>'text', 'name'=>'min_date', 'label'=>'Min. Date', 'placeholder'=>'The minimum selectable date', 'hint_text'=>$hint_min_date),
			'max_date'     => array('type'=>'text', 'name'=>'max_date', 'label'=>'Max. Date', 'placeholder'=>'The maximum selectable date', 'hint_text'=>$hint_max_date),
			'year_range'   => array('type'=>'text', 'name'=>'year_range', 'label'=>'Year Range', 'value'=>'-100:+1', 'hint_text'=>$hint_year_range),
			'number_of_months' => array('type'=>'text', 'name'=>'number_of_months', 'label'=>'Number Of Months', 'value'=>'1', 'hint_text'=>$hint_number_of_months),
			'disabled_days'  => array('type'=>'multiselect', 'name'=>'disabled_days', 'label'=>'Disabled Days', 'placeholder'=>'Select days to disable', 'options'=>$week_days),
			'disabled_dates' => array('type'=>'text', 'name'=>'disabled_dates', 'label'=>'Disabled Dates', 'placeholder'=>'Seperate dates with comma', 
			'hint_text'=>$hint_disabled_dates),
			
			'min_time'    => array('type'=>'text', 'name'=>'min_time', 'label'=>'Min. Time', 'value'=>'12:00am', 'sub_label'=>'ex: 12:30am'),
			'max_time'    => array('type'=>'text', 'name'=>'max_time', 'label'=>'Max. Time', 'value'=>'11:30pm', 'sub_label'=>'ex: 11:30pm'),
			'start_time'  => array('type'=>'text', 'name'=>'start_time', 'label'=>'Start Time', 'value'=>'', 'sub_label'=>'ex: 2h 30m'),
			'time_step'   => array('type'=>'text', 'name'=>'time_step', 'label'=>'Time Step', 'value'=>'30', 'sub_label'=>'In minutes, ex: 30'),
			'time_format' => array('type'=>'select', 'name'=>'time_format', 'label'=>'Time Format', 'value'=>'h:i A', 'options'=>$time_formats),
			'linked_date' => array('type'=>'text', 'name'=>'linked_date', 'label'=>'Linked Date'),
		);
	}
	
	public function get_field_form_props_display(){
		return array('name', 'type', 'title', 'placeholder', 'validate', 'required', 'clear', 'enabled');
	}
	
	public function get_field_types(){
		return array(
			'text' => 'Text', 'hidden' => 'Hidden', 'password' => 'Password', 
			'tel' => 'Telephone', 'email' => 'Email', 'number' => 'Number',  
			'textarea' => 'Textarea', 'select' => 'Select', 'multiselect' => 'Multiselect', 
			'radio' => 'Radio', 'checkbox' => 'Checkbox', 'checkboxgroup' => 'Checkbox Group', 
			'datepicker' => 'Date Picker', 'timepicker' => 'Time Picker', 
			'file' => 'File Upload', 
			'heading' => 'Heading', 'label' => 'Label'
		);
		
		/*return array('text' => 'Text', 'hidden' => 'Hidden', 'password' => 'Password', 'textarea' => 'Textarea', 'select' => 'Select', 'multiselect' => 'Multiselect', 
			'radio' => 'Radio', 'checkbox' => 'Checkbox', 'checkboxgroup' => 'Checkbox Group', 'datepicker' => 'Date Picker', 'timepicker' => 'Time Picker', 
			'heading' => 'Heading', 'label' => 'Label');*/
		
	}
	
	public function get_label_types(){
		return array('h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'p' => 'p', 'div' => 'div', 'span' => 'span', 'label' => 'label');
	}
	
	/*public function woo_attribute_label( $label, $key ) {
		if(!empty($label)){
			$name_title_map = $this->get_options_name_title_map();
			if($name_title_map){
				if(array_key_exists($label, $name_title_map)) {
					$label = $name_title_map[$label];
				}
			}
		}
		return $label;
	}*/
	
   /*-----------------------------------
	----- UTILITY FUNCTIONS - START ----
	------------------------------------*/
	/*public function load_products(){
		$args = array( 'post_type' => 'product', 'order' => 'ASC', 'posts_per_page' => -1, 'fields' => 'ids' );
		$products = get_posts( $args );
		$productsList = array();
		
		if(count($products) > 0){
			foreach($products as $pid){				
				//$productsList[] = array("id" => $product->ID, "title" => $product->post_title);
				$productsList[] = array("id" => $pid, "title" => get_the_title($pid));
			}
		}		
		return $productsList;
	}
	
	public function load_products_cat(){
		$product_cat = array();
		$pcat_terms = get_terms('product_cat', 'orderby=count&hide_empty=0');
		
		foreach($pcat_terms as $pterm){
			$product_cat[] = array("id" => $pterm->slug, "title" => $pterm->name);
		}		
		return $product_cat;
	}
	
	public function load_user_roles(){
		$user_roles = array();
		
		global $wp_roles;
    	$roles = $wp_roles->roles;
		//$roles = get_editable_roles();
		foreach($roles as $key => $role){
			$user_roles[] = array("id" => $key, "title" => $role['name']);
		}		
		
		return $user_roles;
	}*/	
	
	public function is_reserved_field_name( $field_name ){
		if($field_name && in_array($field_name, array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 
			'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 
			'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments'
		))){
			return true;
		}
		return false;
	}
	
	public function is_default_field_name($field_name){
		if($field_name && in_array($field_name, array(
			'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 
			'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
			'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 
			'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments'
		))){
			return true;
		}
		return false;
	}
	
	
	
	public function get_available_positions(){
		$positions = array(
			//'before_checkout_form' => 'Before checkout form',
			//'after_checkout_form' => 'After checkout form',
			'before_customer_details' => 'Before customer details',
			'after_customer_details' => 'After customer details',
			'before_checkout_billing_form' => 'Before billing form',
			'after_checkout_billing_form' => 'After billing form',
			'before_checkout_shipping_form' => 'Before shipping form',
			'after_checkout_shipping_form' => 'After shipping form',
			'before_checkout_registration_form' => 'Before registration form',
			'after_checkout_registration_form' => 'After registration form',
			'before_order_notes' => 'Before order_notes',
			'after_order_notes' => 'After order notes',
			'before_terms_and_conditions' => 'Before terms and conditions',
			'after_terms_and_conditions' => 'After terms and conditions',
			'before_submit' => 'Before submit button',
			'after_submit' => 'After submit button',
			/*
			'before_cart_contents' => 'Review Order - Before cart contents',
			'after_cart_contents' => 'Review Order - After cart contents',
			'before_order_total' => 'Review Order - Before order total',
			'after_order_total' => 'Review Order - After order total',
			'before_order_review' => 'Before order review wrapper',
			'after_order_review' => 'After order review wrapper',
			'order_review_0' => 'Before order review content',
			'order_review_99' => 'After order review content',*/
		);

		if(apply_filters('thwcfe_enable_review_order_section_positions', false)){
			$positions['before_cart_contents'] = 'Review Order - Before cart contents';
			$positions['after_cart_contents'] = 'Review Order - After cart contents';
			$positions['before_order_total'] = 'Review Order - Before order total';
			$positions['after_order_total'] = 'Review Order - After order total';
			$positions['before_order_review'] = 'Before order review wrapper';
			$positions['after_order_review'] = 'After order review wrapper';
			$positions['order_review_0'] = 'Before order review content';
			$positions['order_review_99'] = 'After order review content';
		}
		
		$custom_positions = apply_filters('thwcfe_custom_section_positions', array());
		if(is_array($custom_positions)){
			$positions = array_merge($positions, $custom_positions);
		}
		
		return $positions;
	}
	
	
	
	/*public function prepare_sections_and_fields(){
		$sections = $this->get_checkout_sections();
		//$this->copy_old_settings_message($sections);
		
		if(empty($sections)){
			$sections = $this->get_default_sections();
			$result   = $this->save_sections($sections);
		}
	}*/
	
	/*public function get_default_sections(){
		//$default_sections = array('billing' => 'Billing Fields', 'shipping' => 'Shipping Fields', 'additional' => 'Additional Fields', 'address' => 'Address Fields');
		$default_sections = array('billing' => 'Billing Fields', 'shipping' => 'Shipping Fields', 'additional' => 'Additional Fields');
		
		$sections = array();
		$order = -3;
		foreach($default_sections as $name => $title){
			$section = new WCFE_Checkout_Section();
			$section->set_id($name);
			$section->set_name($name);
			$section->set_property('order', $order);
			$section->set_title($title);
			$section->set_custom_section(0);
			$section->set_fields($this->get_default_fields($name));
			
			$sections[$name] = $section;
			$order++;
		}
		return $sections;
	}*/
	
	/*public function get_default_fields($section_name){
		$fields = false;
		if($section_name === 'billing' || $section_name === 'shipping'){
			$fields = WC()->countries->get_address_fields(WC()->countries->get_base_country(), $section_name . '_');
		}else if($section_name === 'additional'){
			$fields = array(
				'order_comments' => array(
					'type'        => 'textarea',
					'class'       => array('notes'),
					'label'       => __('Order Notes', 'woocommerce'),
					'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'woocommerce')
				)
			);
		}
			
		$field_objects = array();
		if(!empty($fields) && is_array($fields)){
			foreach($fields as $name => $field){
				if(!empty($name) && !empty($field) && is_array($field)){
					$field['type'] = isset($field['type']) ? $field['type'] : 'text';
					$field_object = $this->field_factory->create_field($field['type'], $name, $field); 
				
					if($field_object){
						$field_objects[$name] = $field_object;
					}
				}
			}
		}
		
		return $field_objects;
	}*/
	
	public function reset_to_default() {
		delete_option(self::OPTION_KEY_CUSTOM_SECTIONS);
		delete_option(self::OPTION_KEY_SECTION_HOOK_MAP);
		delete_option('thwepo_options_name_title_map');
		
		$this->prepare_sections_and_fields();
		
		echo '<div class="updated"><p>'. THWCFE_i18n::t('Checkout fields successfully reset') .'</p></div>';
	}
   /*-----------------------------------
	----- UTILITY FUNCTIONS - END ------
	------------------------------------*/
   
   /*-----------------------------------
	----- SECTION FUNCTIONS - START ----
	------------------------------------*/
	/* Override */
	public function output_sections() {
		$result = false;
		if(isset($_POST['s_action']) && $_POST['s_action'] == 'new')
			$result = $this->create_section();	
			
		if(isset($_POST['s_action']) && $_POST['s_action'] == 'edit')
			$result = $this->edit_section();	
			
		if(isset($_POST['s_action']) && $_POST['s_action'] == 'remove')
			$result = $this->remove_section();
			
		$current_section = $this->get_current_section();
		$sections = THWCFE_Utils::get_custom_sections();
					
		if(empty($sections)){
			return;
		}
		
		$this->sort_sections($sections);
		
		$array_keys = array_keys( $sections );
				
		echo '<ul class="thpladmin-sections">';
		$i=0;
		foreach( $sections as $name => $section ){
			$url = $this->get_admin_url($this->page_id, sanitize_title($name));	
			$rules_json = htmlspecialchars($section->get_property('conditional_rules_json'));
			$rules_json_ajax = htmlspecialchars($section->get_property('conditional_rules_ajax_json'));
			
			echo '<li><a href="'. $url .'" class="'. ($current_section == $name ? 'current' : '') .'">'. THWCFE_i18n::t($section->get_property('title')) .'</a></li>';
			if(THWCFE_Utils_Section::is_custom_section($section)){
				?>
                <li>
                	<form id="section_prop_form_<?php echo $name; ?>" method="post" action="">
                        <input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $rules_json; ?>" />
                        <input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $rules_json_ajax; ?>" />
                    </form>
                    <span class='s_edit_btn dashicons dashicons-edit tips' data-tip='<?php THWCFE_i18n::et('Edit Section'); ?>'  
					onclick='thwcfeOpenEditSectionForm(<?php echo THWCFE_Utils_Section::get_property_json($section); ?>)'></span>
                    
                	<?php /*?><a href="javascript:void(0)" onclick='thwcfeOpenEditSectionForm(<?php echo THWCFE_Utils_Section::get_property_json($section); ?>)' class="edit_section" 
                		title="<?php THWCFE_i18n::et('Edit Section') ?>">
                		<img src="<?php echo THWCFE_ASSETS_URL; ?>css/edit.png" />
                	</a><?php */?>
                </li>
                <li>
					<span class="s_copy_btn dashicons dashicons-admin-page tips" data-tip="<?php THWCFE_i18n::et('Duplicate Section'); ?>"  
					onclick='thwcfeOpenCopySectionForm(<?php echo THWCFE_Utils_Section::get_property_json($section); ?>)'></span>
				</li>
				<li>
                    <form method="post" action="">
                        <input type="hidden" name="s_action" value="remove" />
                        <input type="hidden" name="i_name" value="<?php echo $name; ?>" />
                        <span class='s_delete_btn dashicons dashicons-no tips' data-tip='<?php THWCFE_i18n::et('Delete Section'); ?>'  
						onclick='thwcfeRemoveSection(this)'></span>
                        
                        <?php /*?><a href="javascript:void(0)" onclick="thwcfeRemoveSection(this)" class="delete_section" title="<?php THWCFE_i18n::et('Delete Section'); ?>">
                        	<img src="<?php echo THWCFE_ASSETS_URL; ?>css/delete.png" />
                        </a><?php */?>
					</form>
                </li>
                <?php
			}
			echo '<li>';
			echo(end( $array_keys ) == $name ? '' : ' | ');
			echo '</li>';
		}
		echo '<li><a href="javascript:void(0)" onclick="thwcfeOpenNewSectionForm()" class="add_link">+ '. THWCFE_i18n::t( 'Add new section' ) .'</a></li>';
		echo '</ul>';		
		
		if($result){
			echo $result;
		}
	}
	
	public function prepare_copy_section($section, $posted){
		$s_name_copy = isset($posted['s_name_copy']) ? $posted['s_name_copy'] : '';
		if($s_name_copy){
			$section_copy = WCFE_Checkout_Fields_Utils::get_checkout_section($s_name_copy);
			if(THWCFE_Utils_Section::is_valid_section($section_copy)){
				$field_set = $section_copy->get_property('fields');
				if(is_array($field_set) && !empty($field_set)){
					$section->set_property('fields', $field_set);
				}
			}
		}
		return $section;
	}
					
	public function create_section(){
		/*$name  	   = !empty($_POST['i_name']) ? $_POST['i_name'] : '';
		$position  = !empty($_POST['i_position']) ? $_POST['i_position'] : '';
		$order     = !empty($_POST['i_order']) ? $_POST['i_order'] : '';
		$type      = !empty($_POST['i_box_type']) ? $_POST['i_box_type'] : '';
		$cssclass  = !empty($_POST['i_cssclass']) ? $_POST['i_cssclass'] : '';
		
		$show_title = (!empty($_POST['i_show_title']) && $_POST['i_show_title'] === 'yes') ? 1 : 0;
		
		$title    	 = !empty($_POST['i_title']) ? $_POST['i_title'] : '';
		$title_pos   = !empty($_POST['i_title_position']) ? $_POST['i_title_position'] : '';
		$title_type  = !empty($_POST['i_title_type']) ? $_POST['i_title_type'] : '';
		$title_color = !empty($_POST['i_title_color']) ? $_POST['i_title_color'] : '';
		$title_class = !empty($_POST['i_title_class']) ? $_POST['i_title_class'] : '';
		
		$subtitle    	= !empty($_POST['i_subtitle']) ? $_POST['i_subtitle'] : '';
		$subtitle_pos   = !empty($_POST['i_subtitle_position']) ? $_POST['i_subtitle_position'] : '';
		$subtitle_type  = !empty($_POST['i_subtitle_type']) ? $_POST['i_subtitle_type'] : '';
		$subtitle_color = !empty($_POST['i_subtitle_color']) ? $_POST['i_subtitle_color'] : '';
		$subtitle_class = !empty($_POST['i_subtitle_class']) ? $_POST['i_subtitle_class'] : '';
		
		if(!$name || !$title || !$position){
			return;
		}
				
		$name = strtolower($name);
		$name = is_numeric($name) ? "s_".$name : $name;
		
		$section = new WCFE_Checkout_Section();
		$section->set_id($name);
		$section->set_name($name);
		$section->set_position($position);
		$section->set_property('order', $order);
		$section->set_type($type);
		$section->set_cssclass($cssclass);
		
		$section->set_custom_section(1);
		$section->set_show_title($show_title);
		
		$section->set_title($title);
		$section->set_title_position($title_pos);
		$section->set_title_type($title_type);
		$section->set_title_color($title_color);
		$section->set_title_class($title_class);
				
		$section->set_subtitle($subtitle);
		$section->set_subtitle_position($subtitle_pos);
		$section->set_subtitle_type($subtitle_type);
		$section->set_subtitle_color($subtitle_color);
		$section->set_subtitle_class($subtitle_class);
		
		//WPML Support
		$section->add_wpml_support();*/
				
		$section = THWCFE_Utils_Section::prepare_section_from_posted_data($_POST);
		$section = $this->prepare_copy_section($section, $_POST);
		$result = $this->update_section($section);
						
		if($result == true){			
			return '<div class="updated"><p>'. THWCFE_i18n::t('New section added successfully.') .'</p></div>';
		}else{
			return '<div class="error"><p> '. THWCFE_i18n::t('New section not added due to an error.') .'</p></div>';
		}		
	}
	
	public function edit_section(){
		/*$name  	  = !empty($_POST['i_name']) ? $_POST['i_name'] : '';
		$position = !empty($_POST['i_position']) ? $_POST['i_position'] : '';
		$order    = !empty($_POST['i_order']) ? $_POST['i_order'] : '';
		$type     = !empty($_POST['i_box_type']) ? $_POST['i_box_type'] : '';
		$cssclass = !empty($_POST['i_cssclass']) ? $_POST['i_cssclass'] : '';
		
		$show_title = (!empty($_POST['i_show_title']) && $_POST['i_show_title'] === 'yes') ? 1 : 0;
		
		$title    	 = !empty($_POST['i_title']) ? $_POST['i_title'] : '';
		$title_pos   = !empty($_POST['i_title_position']) ? $_POST['i_title_position'] : '';
		$title_type  = !empty($_POST['i_title_type']) ? $_POST['i_title_type'] : '';
		$title_color = !empty($_POST['i_title_color']) ? $_POST['i_title_color'] : '';
		$title_class = !empty($_POST['i_title_class']) ? $_POST['i_title_class'] : '';
		
		$subtitle    	= !empty($_POST['i_subtitle']) ? $_POST['i_subtitle'] : '';
		$subtitle_pos   = !empty($_POST['i_subtitle_position']) ? $_POST['i_subtitle_position'] : '';
		$subtitle_type  = !empty($_POST['i_subtitle_type']) ? $_POST['i_subtitle_type'] : '';
		$subtitle_color = !empty($_POST['i_subtitle_color']) ? $_POST['i_subtitle_color'] : '';
		$subtitle_class = !empty($_POST['i_subtitle_class']) ? $_POST['i_subtitle_class'] : '';
		
		if(!$name || !$title || !$position){
			return;
		}
		
		$section = $this->get_checkout_section($name);
		$section->set_property('position', $position);
		$section->set_property('order', $order);
		$section->set_property('type', $type);
		$section->set_property('cssclass', $cssclass);
		
		$section->set_property('show_title', $show_title);
		
		$section->set_property('title', $title);
		$section->set_property('title_position', $title_pos);
		$section->set_property('title_type', $title_type);
		$section->set_property('title_color', $title_color);
		$section->set_property('title_class', $title_class);
				
		$section->set_property('subtitle', $subtitle);
		$section->set_property('subtitle_position', $subtitle_pos);
		$section->set_property('subtitle_type', $subtitle_type);
		$section->set_property('subtitle_color', $subtitle_color);
		$section->set_property('subtitle_class', $subtitle_class);
		
		$old_position = !empty($_POST['i_position_old']) ? $_POST['i_position_old'] : '';
		if($old_position && $position && ($old_position != $position)){			
			$this->remove_section_from_hook($position_old, $name);
		}
		
		//WPML Support
		THWCFE_Utils_Section::add_wpml_support($section);
				
		$result = $this->update_section($section);*/
		$result = false;
		$section  = THWCFE_Utils_Section::prepare_section_from_posted_data($_POST, 'edit');
		if($section){
			$name 	  = $section->get_property('name');
			$position = $section->get_property('position');
			$old_position = !empty($_POST['i_position_old']) ? $_POST['i_position_old'] : '';
			
			if($old_position && $position && ($old_position != $position)){			
				$this->remove_section_from_hook($position_old, $name);
			}
			
			$result = $this->update_section($section);
		}
		if($result == true){			
			return '<div class="updated"><p>'. THWCFE_i18n::t('Section details updated successfully.') .'</p></div>';
		}else{
			return '<div class="error"><p> '. THWCFE_i18n::t('Section details not updated due to an error.') .'</p></div>';
		}		
	}
			
	public function remove_section(){
		$section_name = !empty($_POST['i_name']) ? $_POST['i_name'] : false;		
		if($section_name){	
			$result = $this->delete_section($section_name);			
										
			if ($result == true) {
				return '<div class="updated"><p>'. THWCFE_i18n::t('Section removed successfully.') .'</p></div>';
			} else {
				return '<div class="error"><p> '. THWCFE_i18n::t('Section not removed due to an error.') .'</p></div>';
			}
		}
	}
	 
	public function delete_section($section_name){
		if(isset($section_name) && !empty($section_name)){	
			$sections = $this->get_checkout_sections();
			if(is_array($sections) && isset($sections[$section_name])){
				$section   = $sections[$section_name];
				$hook_name = $section->get_property('position');
				
				$this->remove_section_from_hook($hook_name, $section_name);
				unset($sections[$section_name]);
							
				$result = $this->save_sections($sections);		
				return $result;
			}
		}
		return false;
	}	
	
	 	 
	/*public function update_options_name_title_map(){
	 	$name_title_map = array();
	 	$sections = $this->get_checkout_sections();
		if($sections && is_array($sections)){
			foreach($sections as $section_name => $section){
				if($this->is_valid_section($section)){					
					$fields = $section->get_fields();					
					if($fields){
						foreach($fields as $field_name => $field){
							if($this->is_valid_field($field) && $field->is_enabled()){
								$name_title_map[$field_name] = $field->get_title();
							}
						}
					}
				}
			}
		}
	 
		$result = update_option('thwepo_options_name_title_map', $name_title_map);
		return $result;
	}*/
	
   /*-----------------------------------
	*----- SECTION FUNCTIONS - END -----
	*-----------------------------------*/
	
   /*-----------------------------------
	*------ SECTION FORMS - START ------
	*-----------------------------------*/
	private function output_add_section_form_pp(){		
		?>
		<div id="thwcfe_new_section_form_pp" title="Create New Section" class="thwcfe_popup_wrapper">
			<?php $this->output_popup_form_section('new'); ?>
		</div>
        <?php
		/*
		?>
        <div id="thwcfe_new_section_form_pp" title="Create New Section" class="thwcfe_popup_wrapper">
          	<form method="post" id="thwcfe_new_section_form" action="">
          		<input type="hidden" name="s_action" value="new" />            
                <table width="100%" border="0">
                    <?php
                    $this->output_section_info_form();
                    $this->output_h_separator();
                    $this->output_title_form(true);
                    $this->output_h_separator();
                    $this->output_rule_form(true);
                    ?>    
                </table>
          	</form>
        </div>
        <?php
		*/
	}
	
	private function output_edit_section_form_pp(){		
		?>
		<div id="thwcfe_edit_section_form_pp" title="Edit Section" class="thwcfe_popup_wrapper">
			<?php $this->output_popup_form_section('edit'); ?>
		</div>
        <?php
		/*
		?>
        <div id="thwcfe_edit_section_form_pp" title="Edit Section" class="thwcfe_popup_wrapper">
          	<form method="post" id="thwcfe_edit_section_form" action="">
          		<input type="hidden" name="s_action" value="edit" />
            	<input type="hidden" name="s_name" value="" />
            	<input type="hidden" name="i_position_old" value="" />                
          		<table width="100%" border="0">
                	<?php
                    $this->output_section_info_form();
                    $this->output_h_separator();
                    $this->output_title_form(true);
                    $this->output_h_separator();
                    $this->output_rule_form(true);
                    ?> 
            	</table>
          	</form>
        </div>
        <?php
		*/
	}
	
	private function output_popup_form_section($form_type){
		?>
        <form method="post" id="thwcfe_<?php echo $form_type ?>_section_form" action="">
        	<input type="hidden" name="s_action" value="<?php echo $form_type ?>" />
            <div id="thwcfe-tabs-container_<?php echo $form_type ?>">
                <ul class="thpladmin-tabs-menu">
                    <li class="first current"><a class="thwcfe_tab_general_link" href="javascript:void(0)" 
                    onclick="thwcfeOpenFormTab(this, 'thwcfe-section-tab-general', '<?php echo $form_type ?>')">General Properties</a></li>
                    <li><a class="thwcfe_tab_rules_link" href="javascript:void(0)" 
                    onclick="thwcfeOpenFormTab(this, 'thwcfe-section-tab-rules', '<?php echo $form_type ?>')">Display Rules</a></li>
                </ul>
                <div id="thwcfe_section_editor_form_<?php echo $form_type ?>" class="thpladmin-tab thwcfe_popup_wrapper">
                    <div id="thwcfe-section-tab-general_<?php echo $form_type ?>" class="thpladmin-tab-content">
                    	<?php if($form_type === 'edit'){ ?>
                            <input type="hidden" name="s_name" value="" />
            				<input type="hidden" name="i_position_old" value="" />
                        <?php }else{ ?>
                            <input type="hidden" name="s_name_copy" value="" />
                        <?php } ?>
                        <input type="hidden" name="i_rules" value="" />
						<input type="hidden" name="i_rules_ajax" value="" />
                        
                    	<table width="100%" border="0">
							<?php
                            $this->output_section_info_form();
                            $this->output_h_separator();
                            $this->output_title_form(true);
                            $this->output_h_separator();
                            //$this->output_rule_form(true);
                            ?> 
                        </table>
                    </div>
                    <div id="thwcfe-section-tab-rules_<?php echo $form_type ?>" class="thpladmin-tab-content">
                        <table class="thwcfe_section_form_tab_rules_placeholder" width="100%" style="margin-top: 10px;">
                        <?php 
                        $this->render_field_form_fragment_rules('section'); 
                        $this->render_field_form_fragment_rules_ajax('section');
                        ?>
                        </table>
                    </div>
                </div>
            </div>
        </form>
        <?php
	}
		
	private function output_section_info_form(){
		$available_positions = $this->get_available_positions();
		?>
        <tr>                
            <td colspan="4" class="err_msgs"></td>
        </tr>            	
        <tr>                
            <td width="15%"><?php THWCFE_i18n::et('Name/ID'); ?><abbr class="required" title="required">*</abbr></td>
            <td width="35%"><input type="text" name="i_name" style="width:250px;"/></td>
            
            <td width="15%"><?php THWCFE_i18n::et('Display Position'); ?><abbr class="required" title="required">*</abbr></td>
            <td>
                <select name="i_position" style="width:250px;">
                	<?php foreach($available_positions as $value=>$label){ ?>
                    <option value="<?php echo trim($value); ?>"><?php THWCFE_i18n::et($label); ?></option>
                	<?php } ?>
                </select>
            </td>
        </tr>  
        <tr>                
            <?php /*?><td><?php THWCFE_i18n::et('Box Type'); ?></td>
            <td>
                <select name="i_box_type" style="width:250px;">
                    <option value="" selected="selected">Normal (clear)</option>
                    <option value="box">Box</option>
                    <option value="collapse">Expand and Collapse (start opened)</option>
                    <option value="collapseclosed">Expand and Collapse (start closed)</option>
                    <option value="accordion">Accordion</option>
                </select>
            </td><?php */?>
            
            <td><?php THWCFE_i18n::et('CSS Class'); ?></td>
            <td>
                <input type="text" name="i_cssclass" style="width:250px;"/>
            </td>
            
            <td><?php THWCFE_i18n::et('Display Order'); ?></td>
            <td>
                <input type="text" name="i_order" style="width:250px;"/>
            </td>           
        </tr> 
        <?php
	}
	
	private function output_title_form($show_subtitle = false){
		$this->output_h_separator(false);
		?>
        <tr>                
            <td>&nbsp;</td>
            <td colspan="3">
                <input type="checkbox" id="a_fshow_title" name="i_show_title" value="yes" checked />
        		<label for="a_fshow_title" style="margin-right: 20px;"><?php THWCFE_i18n::et('Show section title in checkout page'); ?></label>
                <input type="checkbox" id="a_fshow_title_my_account" name="i_show_title_my_account" value="yes" checked/>
        		<label for="a_fshow_title_my_account" ><?php THWCFE_i18n::et('Show section title in my account page'); ?></label>
            </td>
        </tr> 
        <?php $this->output_h_separator(false); ?>
        <tr>                
            <td><?php THWCFE_i18n::et('Title'); ?><abbr class="required" title="required">*</abbr></td>
            <td><input type="text" name="i_title" style="width:250px;"/></td>
            
            <td><?php THWCFE_i18n::et('Title Type'); ?></td>
            <td>
            	<select name="i_title_type" value="h3" style="width:250px;">
                	<?php foreach($this->get_label_types() as $value => $label){ ?>
                    <option value="<?php echo trim($value); ?>"><?php THWCFE_i18n::et($label); ?></option>
                	<?php } ?>
                </select>
            </td>
            
            <?php /*?><td><?php THWCFE_i18n::et('Title Position'); ?></td>
            <td>
                <select name="i_title_position" style="width:250px;">
                    <option selected="selected" value="">Above field</option>
                    <option value="left">Left of the field</option>
                    <option value="right">Right of the field</option>
                    <option value="disable">Disable</option>
                </select>
            </td><?php */?>
        </tr>  
        <tr>              
            <td><?php THWCFE_i18n::et('Title Color'); ?></td>
            <td>
            	<span class="thpladmin-colorpickpreview title_preview" style=""></span>
            	<input type="text" name="i_title_color" class="thpladmin-colorpick" style="width:225px;"/>                
            </td>
            
            <td><?php THWCFE_i18n::et('Title Class'); ?></td>
            <td><input type="text" name="i_title_class" style="width:250px;"/></td>
        </tr>
        
        <?php
		if($show_subtitle){
			$this->output_h_separator(false);
		?>
        <tr>                
            <td><?php THWCFE_i18n::et('Subtitle'); ?></td>
            <td><input type="text" name="i_subtitle" style="width:250px;"/></td>
            
            <td><?php THWCFE_i18n::et('Subtitle Type'); ?></td>
            <td>
            	<select name="i_subtitle_type" value="p" style="width:250px;">
                	<?php foreach($this->get_label_types() as $value => $label){ ?>
                    <option value="<?php echo trim($value); ?>"><?php THWCFE_i18n::et($label); ?></option>
                	<?php } ?>
                </select>            
            </td>   
            
            <?php /*?><td><?php THWCFE_i18n::et('Subtitle Position'); ?></td>
            <td>
                <select name="i_subtitle_position" style="width:250px;">
                    <option selected="selected" value="">Above field</option>
                    <option value="left">Left of the field</option>
                    <option value="right">Right of the field</option>
                    <option value="disable">Disable</option>
                </select>
            </td><?php */?>
        </tr>  
        <tr>                         
            <td><?php THWCFE_i18n::et('Subtitle Color'); ?></td>
            <td>
            	<span class="thpladmin-colorpickpreview subtitle_preview" style=""></span>
            	<input type="text" name="i_subtitle_color" class="thpladmin-colorpick" style="width:225px;"/>
            </td>
            
            <td><?php THWCFE_i18n::et('Subtitle Class'); ?></td>
            <td><input type="text" name="i_subtitle_class" style="width:250px;"/></td>
        </tr>
        <?php
		}
	}
	
	private function output_h_separator($show_line = true){
		$style = $show_line ? 'style="height:5px; border-bottom: 1px dashed #ccc;"' : 'style="height: 5px;"';
		?>
		<tr><td colspan="4" <?php echo $style; ?> ></td></tr> 
        <?php
	}
	
	private function output_rule_form(){
	
	}
   /*-----------------------------------
	*------ SECTION FORMS - END --------
	*-----------------------------------*/
	
	
	public function render_page(){
		if(isset($_POST['reset_fields']))
			echo $this->reset_to_default();	
		
		$memory_limit_current = ini_get('memory_limit');	
		$memory_limit = THWCFE_Utils::get_settings('wp_memory_limit');
		
		if(!empty($memory_limit)){
			ini_set('memory_limit', $memory_limit);
		}
			
		$this->output_tabs();
		$this->output_sections();
		$this->output_content();
		
		if(!empty($memory_limit)){
			ini_set('memory_limit', $memory_limit_current);
		}
	}
	
	
   /*---------------------------------------------
	*------ CHECKOUT FIELDS FUNCTIONS - START ----
	*---------------------------------------------*/
	private function output_actions_row($section){
		if(THWCFE_Utils_Section::is_valid_section($section)){
		?>
            <th colspan="6">
                <button type="button" class="button button-primary" onclick="thwcfeOpenNewFieldForm('<?php echo $section->get_property('name'); ?>')">
                    <?php THWCFE_i18n::et('+ Add field'); ?>
                </button>
                <button type="button" class="button" onclick="thwcfeRemoveSelectedFields()"><?php  THWCFE_i18n::et('Remove'); ?></button>
                <button type="button" class="button" onclick="thwcfeEnableSelectedFields()"><?php  THWCFE_i18n::et('Enable'); ?></button>
                <button type="button" class="button" onclick="thwcfeDisableSelectedFields()"><?php THWCFE_i18n::et('Disable'); ?></button>
            </th>
            <th colspan="5">
                <input type="submit" name="save_fields" class="button-primary" value="<?php THWCFE_i18n::et('Save changes') ?>" style="float:right" />
                <input type="submit" name="reset_fields" class="button" value="<?php THWCFE_i18n::et('Reset to default fields') ?>" style="float:right; margin-right: 5px;" 
                onclick="return confirm('Are you sure you want to reset to default fields? all your changes will be deleted.');"/>
            </th>  
    	<?php 
		}
	}
	
	private function output_fields_table_heading(){
		?>
		<th class="sort"></th>
		<th class="check-column" style="padding-left:0px !important;"><input type="checkbox" style="margin-left:7px;" onclick="thwcfeSelectAllCheckoutFields(this)"/></th>
		<th class="name"><?php THWCFE_i18n::et('Name'); ?></th>
		<th class="type"><?php THWCFE_i18n::et('Type'); ?></th>
		<th class="label"><?php THWCFE_i18n::et('Label'); ?></th>
		<th class="placeholder"><?php THWCFE_i18n::et('Placeholder'); ?></th>
		<th class="validate"><?php THWCFE_i18n::et('Validation Rules'); ?></th>
        <th class="status"><?php THWCFE_i18n::et('Required'); ?></th>
        <th class="status"><?php THWCFE_i18n::et('Clear Row'); ?></th>
		<th class="status"><?php THWCFE_i18n::et('Enabled'); ?></th>	
        <th class="actions align-center"><?php THWCFE_i18n::et('Actions'); ?></th>	
        <?php
	}
	
	private function output_content(){
		$section_name = $this->get_current_section();
		$section = $this->get_checkout_section($section_name);
		$action = isset($_POST['f_action']) ? $_POST['f_action'] : false;
		
		if($action === 'new')
			echo $this->save_or_update_field($section, $action);	
			
		if($action === 'edit')
			echo $this->save_or_update_field($section, $action);
		
		if(isset($_POST['save_fields']))
			echo $this->save_fields($section);
			
		$section = $this->get_checkout_section($section_name);
		$ignore_fields = apply_filters('thwcfe_ignore_fields', array());
		
		?>            
        <div class="wrap woocommerce"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>                
		    <form method="post" id="thwcfe_checkout_fields_form" action="">
            <table id="thwcfe_checkout_fields" class="wc_gateways widefat thpladmin_fields_table" cellspacing="0">
                <thead>
                    <tr><?php $this->output_actions_row($section); ?></tr>
                    <tr><?php $this->output_fields_table_heading(); ?></tr>						
                </thead>
                <tfoot>
                    <tr><?php $this->output_fields_table_heading(); ?></tr>
                    <tr><?php $this->output_actions_row($section); ?></tr>
                </tfoot>
                <tbody class="ui-sortable">
                <?php 
				if(THWCFE_Utils_Section::is_valid_section($section)):
				
				$i=0;												
				foreach( $section->fields as $field ) :	
					$name = $field->get_property('name');
					$type = $field->get_property('type');
					$is_enabled = $field->get_property('enabled') ? 1 : 0;
					$props_json = htmlspecialchars($this->get_property_set_json($field));
					
					$options_json = htmlspecialchars($field->get_property('options_json'));
					$rules_json = htmlspecialchars($field->get_property('conditional_rules_json'));
					$rules_json_ajax = htmlspecialchars($field->get_property('conditional_rules_ajax_json'));
					
					//$disabled_actions = $is_enabled ? in_array($type, THWCFE_Utils_Field::$SPECIAL_FIELD_TYPES) : 1;
					$disable_actions = in_array($name, $ignore_fields) ? true : false;
					$disable_edit = $disable_actions || !$is_enabled ? true : false;
					$disable_copy = $disable_actions || in_array($type, THWCFE_Utils_Field::$SPECIAL_FIELD_TYPES) ? true : false;
					$disabled_cb = $disable_actions ? 'disabled' : '';
				?>
					<tr class="row_<?php echo $i; echo($is_enabled === 1 ? '' : ' thpladmin-disabled') ?>">
						<td width="1%" class="sort ui-sortable-handle">
							<?php
							/*foreach( $this->field_form_props as $pname => $property ){
								$pvalue = $field->get_property($pname);
								$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
								$pvalue = esc_attr($pvalue);
								
								if($property['type'] == 'checkbox'){
									$pvalue = $pvalue ? 1 : 0;
								}
								?>
								<input type="hidden" name="f_<?php echo $pname; ?>[<?php echo $i; ?>]" class="f_<?php echo $pname; ?>" value="<?php echo $pvalue; ?>" />
								<?php
							}*/
							
							//$price_field  = $field->get_property('price_field') ? 1 : 0;
							//$custom_field = $field->get_property('custom_field') ? 1 : 0;
							
							//$rules_action = $field->get_property('rules_action');
							//$rules_action_ajax = $field->get_property('rules_action_ajax');
							
							?>
							<input type="hidden" name="f_name[<?php echo $i; ?>]" class="f_name" value="<?php echo $name; ?>" />
							<input type="hidden" name="f_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $i; ?>" />
							<input type="hidden" name="f_deleted[<?php echo $i; ?>]" class="f_deleted" value="0" />
							<input type="hidden" name="f_enabled[<?php echo $i; ?>]" class="f_enabled" value="<?php echo $is_enabled; ?>" />
							
							<input type="hidden" name="f_props[<?php echo $i; ?>]" class="f_props" value='<?php echo $props_json; ?>' />
							<input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo $options_json; ?>" />
							<input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $rules_json; ?>" />
							<input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $rules_json_ajax; ?>" />
							
							
							<?php /*?><input type="hidden" name="f_custom[<?php echo $i; ?>]" class="f_custom" value="<?php echo $custom_field; ?>" />
							<input type="hidden" name="f_name_new[<?php echo $i; ?>]" class="f_name_new" value="" /><?php */?>
							
							<?php /*?><input type="hidden" name="f_price_field[<?php echo $i; ?>]" class="f_price_field" value="<?php echo $price_field; ?>" />
							<input type="hidden" name="f_options[<?php echo $i; ?>]" class="f_options" value="<?php echo $options_json; ?>" /> 
							
							<input type="hidden" name="f_rules_action[<?php echo $i; ?>]" class="f_rules_action" value="<?php echo $rules_action; ?>" />
							<input type="hidden" name="f_rules_action_ajax[<?php echo $i; ?>]" class="f_rules_action_ajax" value="<?php echo $rules_action_ajax; ?>" />                            
							<input type="hidden" name="f_rules[<?php echo $i; ?>]" class="f_rules" value="<?php echo $rules_json; ?>" />
							<input type="hidden" name="f_rules_ajax[<?php echo $i; ?>]" class="f_rules_ajax" value="<?php echo $rules_json_ajax; ?>" /><?php */?>
						</td>
						<td class="td_select"><input type="checkbox" name="select_field" <?php echo $disabled_cb; ?>/></td>
						
						<?php
						foreach( $this->field_form_props_display as $pname ){
							$property = $this->field_form_props[$pname];
						
							$pvalue = $field->get_property($pname);
							$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
							$pvalue = esc_attr($pvalue);
							
							if($property['type'] == 'checkbox'){
								$pvalue = $pvalue ? 1 : 0;
							}
							
							if(isset($property['status']) && $property['status'] == 1){
								//$statusHtml = $pvalue == 1 ? '<span class="status-enabled tips" data-tip="'.THWCFE_i18n::t('Yes').'">'.THWCFE_i18n::t('Yes').'</span>' : '-';
								$statusHtml = $pvalue == 1 ? '<span class="dashicons dashicons-yes tips" data-tip="'.THWCFE_i18n::t('Yes').'"></span>' : '-';
								?>
								<td class="td_<?php echo $pname; ?> status"><?php echo $statusHtml; ?></td>
								<?php
							}else{
								?>
								<td class="td_<?php echo $pname; ?>"><?php echo stripslashes($pvalue); ?></td>
								<?php
							}
						}
						?>
						
						<td class="td_actions" align="center">
							<?php if($disable_edit){ ?>
								<span class="f_edit_btn dashicons dashicons-edit disabled"></span>
							<?php }else{ ?>
								<span class="f_edit_btn dashicons dashicons-edit tips" data-tip="<?php THWCFE_i18n::et('Edit Field'); ?>"  
								onclick="thwcfeOpenEditFieldForm(this, <?php echo $i; ?>)"></span>
							<?php } ?>
							
							<?php if($disable_copy){ ?>
								<span class="f_copy_btn dashicons dashicons-admin-page disabled"></span>
							<?php }else{ ?>
								<span class="f_copy_btn dashicons dashicons-admin-page tips" data-tip="<?php THWCFE_i18n::et('Duplicate Field'); ?>"  
								onclick="thwcfeOpenCopyFieldForm(this, <?php echo $i; ?>)"></span>
							<?php } ?>
						</td>
					</tr>						
                <?php $i++; endforeach; endif; ?>
                </tbody>
            </table> 
            </form>
            <?php
            $this->output_add_field_form_pp();
			$this->output_edit_field_form_pp();
			$this->output_add_section_form_pp();
			$this->output_edit_section_form_pp();
			$this->output_popup_form_field_fragments();
			?>
    	</div>
    <?php
    }
	
	public function get_property_set_json($field){
		if(THWCFE_Utils_Field::is_valid_field($field)){
			$props_set = array();
			
			foreach( $this->field_form_props as $pname => $property ){
				$pvalue = $field->get_property($pname);
				$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
				$pvalue = esc_attr($pvalue);
				
				if($property['type'] == 'checkbox'){
					$pvalue = $pvalue ? 1 : 0;
				}
				$props_set[$pname] = $pvalue;
			}
						
			$props_set['custom'] = THWCFE_Utils_Field::is_custom_field($field) ? 1 : 0;
			$props_set['price_field'] = $field->get_property('price_field') ? 1 : 0;
			$props_set['rules_action'] = $field->get_property('rules_action');
			$props_set['rules_action_ajax'] = $field->get_property('rules_action_ajax');
						
			return json_encode($props_set);
		}else{
			return '';
		}
	}
	
	private function save_or_update_field($section, $action) {
		try {
			$field = THWCFE_Utils_Field::prepare_field_from_posted_data($_POST, $this->field_form_props);
			
			if($action === 'edit'){
				$section = THWCFE_Utils_Section::update_field($section, $field);
			}else{
				$section = THWCFE_Utils_Section::add_field($section, $field);
			}
			
			$result = $this->update_section($section);
			
			if($result == true) {
				echo '<div class="updated"><p>'. THWCFE_i18n::t('Your changes were saved.') .'</p></div>';
				do_action('thwcfe-checkout-fields-updated');
			}else {
				echo '<div class="error"><p>'. THWCFE_i18n::t('Your changes were not saved due to an error (or you made none!).') .'</p></div>';
			}
		} catch (Exception $e) {
			echo '<div class="error"><p>'. THWCFE_i18n::t('Your changes were not saved due to an error.') .'</p></div>';
			//echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
	
	private function save_fields($section) {
		try {
			$f_names = !empty( $_POST['f_name'] ) ? $_POST['f_name'] : array();	
			if(empty($f_names)){
				echo '<div class="error"><p> '. THWCFE_i18n::t('Your changes were not saved due to no fields found.') .'</p></div>';
				return;
			}
			
			$f_order   = !empty( $_POST['f_order'] ) ? $_POST['f_order'] : array();	
			$f_deleted = !empty( $_POST['f_deleted'] ) ? $_POST['f_deleted'] : array();
			$f_enabled = !empty( $_POST['f_enabled'] ) ? $_POST['f_enabled'] : array();
						
			$sname = $section->get_property('name');
			$field_set = THWCFE_Utils_Section::get_fields($section);
			
			/*foreach($field_set as $name => $field){
				if(THWCFE_Utils_Field::is_valid_field($field)){
					if(isset($f_deleted[$i]) && $f_deleted[$i] == 1){
						continue;
					}
				} 
			}*/
			
			$max = max( array_map( 'absint', array_keys( $f_names ) ) );
			for($i = 0; $i <= $max; $i++) {
				$name = $f_names[$i];
				
				if(isset($field_set[$name])){
					if(isset($f_deleted[$i]) && $f_deleted[$i] == 1){
						unset($field_set[$name]);
						continue;
					}
					
					$field = $field_set[$name];
					$field->set_property('order', isset($f_order[$i]) ? trim(stripslashes($f_order[$i])) : 0);
					$field->set_property('enabled', isset($f_enabled[$i]) ? trim(stripslashes($f_enabled[$i])) : 0);
					
					$field_set[$name] = $field;
				}
			}
			$section->set_property('fields', $field_set);
			$section = THWCFE_Utils_Section::sort_fields($section);
			
			$result1 = $this->update_section($section);
			//$result2 = $this->update_options_name_title_map();
			
			if ($result1 == true) {
				echo '<div class="updated"><p>'. THWCFE_i18n::t('Your changes were saved.') .'</p></div>';
				do_action('thwcfe-checkout-fields-updated');
			} else {
				echo '<div class="error"><p>'. THWCFE_i18n::t('Your changes were not saved due to an error (or you made none!).') .'</p></div>';
			}
			
		} catch (Exception $e) {
			echo '<div class="error"><p>'. THWCFE_i18n::t('Your changes were not saved due to an error.') .'</p></div>';
		}
	}
	
	/*private function save_fields_1($section) {
		try {
			$f_names = !empty( $_POST['f_name'] ) ? $_POST['f_name'] : array();	
			if(empty($f_names)){
				echo '<div class="error"><p> '. THWCFE_i18n::t('Your changes were not saved due to no fields found.') .'</p></div>';
				return;
			}
			
			//----- Recieve POST data START ----
			$field_values = array();
			foreach( $this->field_form_props as $pname => $property ){
				$values = !empty( $_POST['f_'.$pname] ) ? $_POST['f_'.$pname] : array();
				$field_values[$pname] = $values;
			}
			
			//$f_is_price_field = !empty( $_POST['f_is_price_field'] ) ? $_POST['f_is_price_field'] : array();
			$f_custom  = !empty( $_POST['f_custom'] ) ? $_POST['f_custom'] : array();
			$f_options = !empty( $_POST['f_options'] ) ? $_POST['f_options'] : array();
			$f_order   = !empty( $_POST['f_order'] ) ? $_POST['f_order'] : array();	
			$f_deleted = !empty( $_POST['f_deleted'] ) ? $_POST['f_deleted'] : array();
			
			$f_rules_action = !empty( $_POST['f_rules_action'] ) ? $_POST['f_rules_action'] : array();
			$f_rules_action_ajax = !empty( $_POST['f_rules_action_ajax'] ) ? $_POST['f_rules_action_ajax'] : array();
			
			$f_rules = !empty( $_POST['f_rules'] ) ? $_POST['f_rules'] : array();
			$f_rules_ajax = !empty( $_POST['f_rules_ajax'] ) ? $_POST['f_rules_ajax'] : array();
			//----- Recieve POST data END ----
			
			$section->clear_fields();
			$sname = $section->get_property('name');
			
			$max = max( array_map( 'absint', array_keys( $f_names ) ) );
			for($i = 0; $i <= $max; $i++) {
				if(isset($f_deleted[$i]) && $f_deleted[$i] == 1){
					continue;
				}
				
				$types = isset($field_values['type']) ? $field_values['type'] : array();
				$type  = isset($types[$i]) ? trim(stripslashes($types[$i])) : '';
				
				$field = $this->field_factory->create_field($type); 
				
				foreach( $this->field_form_props as $pname => $property ){
					$pvalues = isset($field_values[$pname]) ? $field_values[$pname] : array();
					$pvalue = '';
					if($property['type'] === 'checkbox'){
						$pvalue = isset($pvalues[$i]) ? $pvalues[$i] : 0;
					}else{
						$pvalue  = isset($pvalues[$i]) ? trim(stripslashes($pvalues[$i])) : '';
					}
					
					$field->set_property($pname, $pvalue);
				}
				
				if($type === 'select' || $type === 'multiselect' || $type === 'radio' || $type === 'checkboxgroup'){
					if(isset($f_options[$i])){
						$field->set_property('options_json', isset($f_options[$i]) ? trim(stripslashes($f_options[$i])) : '');
						$field->set_property('options', $this->prepare_options_array($field->get_property('options_json')));
					}
				}
				
				$field->set_property('order', isset($f_order[$i]) ? trim(stripslashes($f_order[$i])) : 0);
				$field->set_property('custom_field', isset($f_custom[$i]) ? trim(stripslashes($f_custom[$i])) : 0);
				
				$field->set_property('rules_action', isset($f_rules_action[$i]) ? trim(stripslashes($f_rules_action[$i])) : '');
				$field->set_property('rules_action_ajax', isset($f_rules_action_ajax[$i]) ? trim(stripslashes($f_rules_action_ajax[$i])) : '');
				
				$field->set_property('conditional_rules_json', isset($f_rules[$i]) ? trim(stripslashes($f_rules[$i])) : '');
				$field->set_property('conditional_rules', $this->prepare_conditional_rules($field->get_property('conditional_rules_json')));
				
				$field->set_property('conditional_rules_ajax_json', isset($f_rules_ajax[$i]) ? trim(stripslashes($f_rules_ajax[$i])) : '');
				$field->set_property('conditional_rules_ajax', $this->prepare_conditional_rules($field->get_property('conditional_rules_ajax_json')));
				
				//if($field->is_custom_field() && ($sname === 'billing' || $sname === 'shipping')){
					//$sname = $sname === 'additional' ? 'order' : $sname;
					
					//$fname = $field->get_property('name');
					//if(strpos($fname, $sname.'_') === false) {
						//$fname = $sname.'_'.$fname;
					//}
					//$field->set_property('name', $fname);
				//}
				
				$field->prepare_properties();
				
				$section->add_field($field);
			}
			$section->sort_fields();
			
			$result1 = $this->update_section($section);
			//$result2 = $this->update_options_name_title_map();
			
			if ($result1 == true) {
				echo '<div class="updated"><p>'. THWCFE_i18n::t('Your changes were saved.') .'</p></div>';
				do_action('thwcfe-checkout-fields-updated');
			} else {
				echo '<div class="error"><p>'. THWCFE_i18n::t('Your changes were not saved due to an error (or you made none!).') .'</p></div>';
			}
		} catch (Exception $e) {
			echo '<div class="error"><p>'. THWCFE_i18n::t('Your changes were not saved due to an error.') .'</p></div>';
			//echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}*/
	
	private function output_add_field_form_pp(){
		?>
        <div id="thwcfe_new_field_form_pp" title="New Checkout Field" class="thpladmin-popup-wrapper">
          <?php $this->output_popup_form_fields('new'); ?>
        </div>
        <?php
	}
		
	private function output_edit_field_form_pp(){		
		?>
        <div id="thwcfe_edit_field_form_pp" title="Edit Checkout Field" class="thpladmin-popup-wrapper">
          <?php $this->output_popup_form_fields('edit'); ?>
        </div>
        <?php
	}
   /*---------------------------------------------
	*------ CHECKOUT FIELDS FUNCTIONS - END ------
	*---------------------------------------------*/

	
	/*public function prepare_options_array($options_json){
		$options_json = urldecode($options_json);
		$options_arr = json_decode($options_json, true);
		$options = array();
		
		if($options_arr){
			foreach($options_arr as $option){
				$option['key'] = empty($option['key']) ? $option['text'] : $option['key'];
				$options[$option['key']] = $option;
			}
		}
		return $options;
	}*/
		
	/*public function prepare_conditional_rules($conditional_rules){
		$condition_rule_sets = array();	
		if(!empty($conditional_rules)){
			$conditional_rules = urldecode($conditional_rules);
			$rule_sets = json_decode($conditional_rules, true);
				
			if(is_array($rule_sets)){
				foreach($rule_sets as $rule_set){
					if(is_array($rule_set)){
						$condition_rule_set_obj = new WCFE_Condition_Rule_Set();
						$condition_rule_set_obj->set_logic('and');
												
						foreach($rule_set as $condition_sets){
							if(is_array($condition_sets)){
								$condition_rule_obj = new WCFE_Condition_Rule();
								$condition_rule_obj->set_logic('or');
														
								foreach($condition_sets as $condition_set){
									if(is_array($condition_set)){
										$condition_set_obj = new WCFE_Condition_Set();
										$condition_set_obj->set_logic('and');
													
										foreach($condition_set as $condition){
											if(is_array($condition)){
												$condition_obj = new WCFE_Condition();
												$condition_obj->set_operand_type(isset($condition['operand_type']) ? $condition['operand_type'] : '');
												$condition_obj->set_operand(isset($condition['operand']) ? $condition['operand'] : '');
												$condition_obj->set_operator(isset($condition['operator']) ? $condition['operator'] : '');
												$condition_obj->set_value(isset($condition['value']) ? $condition['value'] : '');
												
												$condition_set_obj->add_condition($condition_obj);
											}
										}										
										$condition_rule_obj->add_condition_set($condition_set_obj);	
									}								
								}
								$condition_rule_set_obj->add_condition_rule($condition_rule_obj);
							}
						}
						$condition_rule_sets[] = $condition_rule_set_obj;
					}
				}	
			}
		}
		return $condition_rule_sets;
	}*/
			
	
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER DETAILS PAGE - START ***
	*******************************************************************************/	
	public function woo_admin_order_data_after_order_details($order){	
		$fields = array();
		
		$sections = WCFE_Checkout_Fields_Utils::get_checkout_sections();	
		foreach($sections as $sname => $section){	
			if($sname != 'billing' && $sname != 'shipping'){
				$temp_fields = THWCFE_Utils_Section::get_fields($section);
				if($temp_fields && is_array($temp_fields)){
					$fields = array_merge($fields, $temp_fields);
				}
			}			
		}
		
		$this->display_fields_in_admin_order($order, $fields, '', '<p>&nbsp;</p>');
	}
	
	public function woo_admin_order_data_after_billing_address($order){	
		$section = WCFE_Checkout_Fields_Utils::get_checkout_section('billing');
		if(THWCFE_Utils_Section::is_valid_section($section)){
			$fields = THWCFE_Utils_Section::get_fields($section);
			$this->display_fields_in_admin_order($order, $fields, '');
		}
	}
	
	public function woo_admin_order_data_after_shipping_address($order){	
		$section = WCFE_Checkout_Fields_Utils::get_checkout_section('shipping');
		if(THWCFE_Utils_Section::is_valid_section($section)){
			$fields = THWCFE_Utils_Section::get_fields($section);
			$this->display_fields_in_admin_order($order, $fields, '');
		}
	}
	
	public function display_fields_in_admin_order($order, $fields, $field_name_prefix = '', $prefix_html = ''){
		if($fields){
			$found = false;
			$html = '';
			$is_nl2br = apply_filters('thwcfe_nl2br_custom_field_value', true);
			
			$order_id = false;
			if($this->woo_version_check()){
				$order_id = $order->get_id();
			}else{
				$order_id = $order->id;
			}
			$dis_fields = WCFE_Checkout_Fields_Utils::get_disabled_fields($order_id);
		
			foreach($fields as $name => $field){	
				if(THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_custom_field($field) && 
						THWCFE_Utils_Field::is_enabled($field) && $field->get_property('show_in_order')){	
					$type = $field->get_property('type');
					
					if($type === 'label' || $type === 'heading'){
						if(!in_array($name, $dis_fields)){
							$label = $field->get_property('title') ? $field->get_property('title') : false;
							$subtitle = $field->get_property('subtitle') ? $field->get_property('subtitle') : false;
							if($label || $subtitle){
								if(apply_filters('thwcfe_esc_attr_custom_field_label_admin_order', false)){
									$label = $label ? THWCFE_i18n::esc_attr__t($label) : '';
									$subtitle = $subtitle ? THWCFE_i18n::esc_attr__t($subtitle) : '';
								}else{
									$label = $label ? THWCFE_i18n::t($label) : '';
									$subtitle = $subtitle ? THWCFE_i18n::t($subtitle) : '';
								}
								
								if($subtitle){
									$label .= '<br/><span style="font-size:80%">'.$subtitle.'</span>';
								}
								
								$found = true;
								if($type === 'heading'){
									$html .= '<h3>'. $label .'</h3>';
								}else{
									$html .= '<p><strong>'. $label .'</strong></p>';
								}
							}
						}
					}else{
						$value = get_post_meta( $order_id, $field_name_prefix.$name, true );
						if(!empty($value)){
							if($type === 'file'){
								$value = WCFE_Checkout_Fields_Utils::get_file_display_name_order($value, apply_filters('thwcfe_clickable_filename_in_order_admin_view', true, $name));
							}else{
								$value = $this->get_option_text_from_value($field, $value);
								$value = is_array($value) ? implode(",", $value) : $value;
							}

							$label = $field->get_property('title') ? $field->get_property('title') : $name;
							if(apply_filters('thwcfe_esc_attr_custom_field_label_admin_order', false)){
								$label = THWCFE_i18n::esc_attr__t($label);
							}else{
								$label = THWCFE_i18n::t($label);
							}
							
							if($is_nl2br && $type === 'textarea'){
								$value = nl2br($value);
							}else if($type !== 'file'){
								$value = esc_html($value);
							}
							
							$found = true;
							$html .= '<p><strong>'. $label .':</strong><br/> '. $value .'</p>';									
						}
					}
				}
			}
			if($found){
				echo $prefix_html.$html;	
			}
		}
	} 
	
	public function woo_customer_meta_fields($fields){
		$sections = $this->get_checkout_sections();
		if($sections && is_array($sections)){
			foreach($sections as $sname => $section) {
				$fieldset = THWCFE_Utils_Section::get_fields($section);
					
				if($fieldset && is_array($fieldset) && !empty($fieldset)){
					if($sname === 'billing' || $sname === 'shipping'){
						foreach($fieldset as $key => $field) {
							if(THWCFE_Utils_Field::is_custom_field($field) && $field->get_property('user_meta')){	
								$fields[$sname]['fields'][$key] = array(
									'label'       => THWCFE_i18n::t($field->get_property('title')),
									'description' => THWCFE_i18n::t($field->get_property('description')),
									'type'        => $field->get_property('type'),
									'class'       => '',
									'options'     => THWCFE_Utils_Field::get_option_array($field)
								);
							}
						}
					}else{
						$cfields = array();
						
						foreach($fieldset as $key => $field) {
							if(THWCFE_Utils_Field::is_custom_field($field) && $field->get_property('user_meta')){	
								$cfields[$key] = array(
									'label'       => THWCFE_i18n::t($field->get_property('title')),
									'description' => THWCFE_i18n::t($field->get_property('description')),
									'type'        => $field->get_property('type'),
									'class'       => '',
									'options'     => THWCFE_Utils_Field::get_option_array($field)
								);
							}
						}
						
						if(!empty($cfields)){
							$fields[$sname]['title'] = THWCFE_i18n::t($section->get_property('title'));
							$fields[$sname]['fields'] = $cfields;
						}
					}
				}
			}
		}
		
		return $fields;
	}
	
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER DETAILS PAGE - END *****
	*******************************************************************************/
	
	
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER LIST TABLE - START *****
	*******************************************************************************/
	
	public function get_custom_shop_order_columns(){
		$custom_columns_str = $this->get_settings('custom_shop_order_columns');
		$custom_columns = array();
		
		if(!empty($custom_columns_str)){
			$col_arr = explode(",", $custom_columns_str);
			
			if($col_arr){
				foreach($col_arr as $col_str){
					$col = explode(":", $col_str);
					
					if(is_array($col) && !empty($col)){
						$name = isset($col[0]) ? $col[0] : false;
						if($name){
							$title = isset($col[1]) ? $col[1] : $name;
							$custom_columns[$name] = $title;
						}
					}
				}
			}
		}
		
		return is_array($custom_columns) ? $custom_columns : array();
	}
	
	public function manage_edit_shop_order_columns($columns){
		$custom_columns = $this->get_custom_shop_order_columns();

		if(!empty($custom_columns)){
			$new_columns = (is_array($columns)) ? $columns : array();
			if(isset($new_columns['order_actions'])){
				unset($new_columns['order_actions']);
			}
			
			foreach($custom_columns as $name => $title){
				$new_columns[$name] = $title;
			}
			
			if(isset($columns['order_actions'])){
				$new_columns['order_actions'] = $columns['order_actions'];
			}		
			return $new_columns;
		}
		return $columns;
	}
	
	public function manage_shop_order_posts_custom_column($column){
		$custom_columns = $this->get_custom_shop_order_columns();
		
		if(!empty($custom_columns)){
			global $post;
			$data = get_post_meta( $post->ID );
			
			if(array_key_exists($column, $custom_columns)){
				echo (isset($data[$column]) ? $data[$column][0] : '');
			}
		}
	}
	
	public function manage_edit_shop_order_sortable_columns( $columns ) {
		$custom_columns = $this->get_custom_shop_order_columns();
		$custom = array();
		
		if(!empty($custom_columns)){
			foreach($custom_columns as $name => $title){
				//$custom[$name] = $name.'_POST_META_ID';
				$custom[$name] = $name;
			}
		}

		return wp_parse_args( $custom, $columns );
	}
	
	public function pre_get_posts($query) { 
		//if ($query->is_post_type_archive('shop_order') && $query->is_main_query()) {
		if($query->query['post_type']  == 'shop_order' && $query->is_main_query() && isset($query->query['orderby'])) {	
			$custom_columns = $this->get_custom_shop_order_columns();
			$orderby = $query->query['orderby'];
	
			if(!empty($custom_columns) && array_key_exists($orderby, $custom_columns)){
				//$query->set('meta_key', $orderby);
				//$query->set('orderby', 'meta_value');
			}
		}
		//return $query;
	}

	public function posts_clauses_sort_shop_orders($pieces, $query) {
		global $wpdb;

		if(isset($query->query['post_type']) && $query->query['post_type'] == 'shop_order' && $query->is_main_query() && isset($query->query['orderby'])) {	
			$custom_columns = $this->get_custom_shop_order_columns();
			$orderby = $query->query['orderby'];
	
			if(!empty($custom_columns) && array_key_exists($orderby, $custom_columns)){
				$fieldset = self::get_all_checkout_fields();
				$cfield = is_array($fieldset) && isset($fieldset[$orderby]) ? $fieldset[$orderby] : false;

				if($cfield){
					$orderby_str = 'wp_rd.meta_value';

					if($cfield->get_property('type') === 'datepicker'){
						$date_format = $cfield->get_property('date_format');
						if($date_format){
							$date_format = str_replace("dd", "%d", $date_format);
							$date_format = str_replace("mm", "%m", $date_format);
							$date_format = str_replace("yy", "%Y", $date_format);
						}else{
							$date_format = '%d/%m/%Y';
						}

						$orderby_str = "STR_TO_DATE( wp_rd.meta_value,'".$date_format."' )";
					}

					$order = strtoupper($query->get('order'));
			    	$order = in_array($order, array('ASC', 'DESC')) ? $order : 'ASC';

					$pieces['join'] .= " LEFT JOIN $wpdb->postmeta wp_rd ON wp_rd.post_id = {$wpdb->posts}.ID AND wp_rd.meta_key = '".$orderby."'";
					
		            $pieces['orderby'] = $orderby_str." $order, ". $pieces['orderby'];
				}
			}
		}
		return $pieces;
	}
	
	
   /*******************************************************************************
	******** DISPLAY CUSTOM FIELDS & VALUES in ADMIN ORDER LIST TABLE - START *****
	*******************************************************************************/
	 
	 
   /*------------------------------------------
	*-------- HTML FORM FRAGMENTS - START -----
	*------------------------------------------*/
	private function output_popup_form_fields($form_type){
		?>
		<form method="post" id="thwcfe_<?php echo $form_type ?>_field_form" action="">
          	<input type="hidden" name="f_action" value="<?php echo $form_type ?>" />
        	<div id="thwcfe-tabs-container_<?php echo $form_type ?>">
                <ul class="thpladmin-tabs-menu">
                    <li class="first current"><a class="thwcfe_tab_general_link" href="javascript:void(0)" 
                    onclick="thwcfeOpenFormTab(this, 'thwcfe-tab-general', '<?php echo $form_type ?>')">General Properties</a></li>
                    <li><a class="thwcfe_tab_rules_link" href="javascript:void(0)" 
                    onclick="thwcfeOpenFormTab(this, 'thwcfe-tab-rules', '<?php echo $form_type ?>')">Conditional Rules</a></li>
                </ul>
                <div id="thwcfe_field_editor_form_<?php echo $form_type ?>" class="thpladmin-tab thwcfe_popup_wrapper">
                    <div id="thwcfe-tab-general_<?php echo $form_type ?>" class="thpladmin-tab-content">
						<input type="hidden" name="i_name_old" value="" />
                        <input type="hidden" name="i_options" value="" />
						<input type="hidden" name="i_rules" value="" />
						<input type="hidden" name="i_rules_ajax" value="" />
						
						<?php $this->render_field_form_fragment_general($form_type); ?>
                        <table class="thwcfe_field_form_tab_general_placeholder" width="100%"></table>
                    </div>
                    <div id="thwcfe-tab-rules_<?php echo $form_type ?>" class="thpladmin-tab-content">
                    	<table class="thwcfe_field_form_tab_rules_placeholder" width="100%" style="margin-top: 10px;">
                    	<?php 
						$this->render_field_form_fragment_rules(); 
						$this->render_field_form_fragment_rules_ajax();
						?>
                        </table>
                    </div>
                </div>
        	</div>
        </form>
        <?php
	}	
	
	private function output_popup_form_field_fragments(){
		$this->render_form_field_inputtext();
		$this->render_form_field_hidden();
		$this->render_form_field_password();
		$this->render_form_field_tel();
		$this->render_form_field_email();
		$this->render_form_field_number();		
		$this->render_form_field_textarea();
		$this->render_form_field_select();
		$this->render_form_field_multiselect();		
		$this->render_form_field_radio();
		$this->render_form_field_checkbox();
		$this->render_form_field_checkboxgroup();
		$this->render_form_field_datepicker();
		$this->render_form_field_timepicker();	
		$this->render_form_field_file();
		//$this->render_form_field_country();
		//$this->render_form_field_state();	
		$this->render_form_field_heading();
		$this->render_form_field_label();
		$this->render_form_field_default();
		
		$this->render_field_form_fragment_product_list();
		$this->render_field_form_fragment_category_list();
		$this->render_field_form_fragment_user_role_list();
		$this->render_field_form_fragment_fields_wrapper();
	}
	
	private function render_form_field_inputtext(){
		?>
        <table id="thwcfe_field_form_id_text" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['maxlength'], $this->cell_props_L);
				$this->render_form_element_empty_cell();
            	//$this->render_form_field_element($this->field_form_props['repeat_x'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('inputtext');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>     
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
        </table>
        <?php   
	}
	
	private function render_form_field_hidden(){
		$field = $this->field_form_props['title'];
		$field['placeholder'] = 'For order details page & email';
		?>
        <table id="thwcfe_field_form_id_hidden" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
				$this->render_form_field_element($field, $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_R);
				?>
            </tr>
			<tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_L);
            	$this->render_form_element_empty_cell();
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('hidden');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>      
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
        </table>
        <?php   
	}
	
	private function render_form_field_password(){
		?>
        <table id="thwcfe_field_form_id_password" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['maxlength'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('password');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>    
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
        </table>
        <?php   
	}

	private function render_form_field_tel(){
		?>
        <table id="thwcfe_field_form_id_tel" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['maxlength'], $this->cell_props_L);
				$this->render_form_element_empty_cell();
            	//$this->render_form_field_element($this->field_form_props['repeat_x'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('inputtext');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>     
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
        </table>
        <?php   
	}

	private function render_form_field_email(){
		?>
        <table id="thwcfe_field_form_id_email" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['maxlength'], $this->cell_props_L);
				$this->render_form_element_empty_cell();
            	//$this->render_form_field_element($this->field_form_props['repeat_x'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('inputtext');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>     
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
        </table>
        <?php   
	}

	private function render_form_field_number(){
		?>
        <table id="thwcfe_field_form_id_number" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['maxlength'], $this->cell_props_L);
				$this->render_form_element_empty_cell();
            	//$this->render_form_field_element($this->field_form_props['repeat_x'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('inputtext');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>     
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
        </table>
        <?php   
	}
	
	private function render_form_field_textarea(){
		$value_field_props = $this->field_form_props['value'];
		$value_field_props['type'] = 'textarea';
		?>
        <table id="thwcfe_field_form_id_textarea" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($value_field_props, $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['maxlength'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('textarea');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
        </table>
        <?php   
	}
	
	private function render_form_field_select(){
		?>
        <table id="thwcfe_field_form_id_select" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
			<tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <?php $this->render_form_element_h_spacing(); ?>
            <?php $this->render_field_form_fragment_options(); ?>
            <?php $this->render_form_element_h_spacing(); ?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
        </table>
        <?php   
	}
	
	private function render_form_field_multiselect(){
		$field_props_maxlength = $this->field_form_props['maxlength'];
		$field_props_maxlength['label'] = 'Max. Selections';
		$field_props_maxlength['hint_text'] = 'The maximum number of options that can be selected';
		?>
        <table id="thwcfe_field_form_id_multiselect" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($field_props_maxlength, $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <?php $this->render_form_element_h_spacing(); ?>
            <?php $this->render_field_form_fragment_options(); ?>
            <?php $this->render_form_element_h_spacing(); ?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>    
        </table>
        <?php   
	}
	
	private function render_form_field_radio(){
		?>
        <table id="thwcfe_field_form_id_radio" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
			<tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_L);
            	$this->render_form_element_empty_cell();
				?>
            </tr>
            <?php $this->render_form_element_h_spacing(); ?>
            <?php $this->render_field_form_fragment_options(); ?>
            <?php $this->render_form_element_h_spacing(); ?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
        </table>
        <?php   
	}
	
	private function render_form_field_checkbox(){
		$field_value_props = $this->field_form_props['value'];
		$field_value_props['label'] = THWCFE_i18n::t('Value');
		
		?>
        <table id="thwcfe_field_form_id_checkbox" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
            	$this->render_form_field_element($field_value_props, $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
			<tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_L);
            	$this->render_form_element_empty_cell();
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('checkbox');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['checked'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>    
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
        </table>
        <?php   
	}
	
	private function render_form_field_checkboxgroup(){
		$field_value_props = $this->field_form_props['value'];
		$field_value_props['label'] = THWCFE_i18n::t('Default Values');
		
		?>
        <table id="thwcfe_field_form_id_checkboxgroup" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
            	$this->render_form_field_element($field_value_props, $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <?php $this->render_form_element_h_spacing(); ?>
            <?php $this->render_field_form_fragment_options(); ?>
            <?php $this->render_form_element_h_spacing(); ?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
        </table>
        <?php   
	}
	
	private function render_form_field_datepicker(){
		?>
        <table id="thwcfe_field_form_id_datepicker" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('datepicker');
			?>
            <?php 
				$this->render_form_element_h_separator();
				$this->render_form_element_h_spacing(); 
				$this->render_field_form_fragment_datepicker();
				$this->render_form_element_h_separator();
				$this->render_form_element_h_spacing(); 
			?>  
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>      
        </table>
        <?php   
	}
	
	private function render_form_field_timepicker(){
		?>
        <table id="thwcfe_field_form_id_timepicker" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['linked_date'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				 $this->render_field_form_fragment_price('timepicker');
			?>
            <?php 
				$this->render_form_element_h_separator();
				$this->render_form_element_h_spacing(); 
				$this->render_field_form_fragment_timepicker();
				$this->render_form_element_h_separator();
				$this->render_form_element_h_spacing(); 
			?>   
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
        </table>
        <?php   
	}
	
	private function render_form_field_file(){
		?>
        <table id="thwcfe_field_form_id_file" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['maxsize'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['accept'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_element_empty_cell();
				?>
            </tr>
            <?php 
				$this->render_field_form_fragment_price('file');
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>     
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				//$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
        </table>
        <?php   
	}

	private function render_form_field_country(){
		?>
        <table id="thwcfe_field_form_id_country" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
        </table>
        <?php   
	}

	private function render_form_field_state(){
		?>
        <table id="thwcfe_field_form_id_state" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['country_field'], $this->cell_props_L);
            	$this->render_form_element_empty_cell();
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
        </table>
        <?php   
	}
	
	private function render_form_field_heading(){
		?>
        <table id="thwcfe_field_form_id_heading" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_element_empty_cell();
				?>
            </tr>
            <?php 
				$this->render_form_element_h_spacing(); 
				$this->render_field_form_fragment_title(true);
			?>   
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$show_in_order = $this->field_form_props['show_in_order'];
				$show_in_thank_you_page = $this->field_form_props['show_in_thank_you_page'];
				$show_in_order['checked'] = 0;
				$show_in_thank_you_page['checked'] = 0;
				
            	$this->render_form_field_element($show_in_order, $this->cell_props_CBS, false);
				$this->render_form_field_element($show_in_thank_you_page, $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
				<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_my_account_page'], $this->cell_props_CBS, false);
				?>
				</td>
            </tr>
        </table>
        <?php   
	}
	
	private function render_form_field_label(){
		?>
        <table id="thwcfe_field_form_id_label" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_element_empty_cell();
				?>
            </tr>
            <?php 
				$this->render_form_element_h_spacing();  
				$this->render_field_form_fragment_title(true);
			?>    
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
				$show_in_order = $this->field_form_props['show_in_order'];
				$show_in_thank_you_page = $this->field_form_props['show_in_thank_you_page'];
				$show_in_order['checked'] = 0;
				$show_in_thank_you_page['checked'] = 0;
				
            	$this->render_form_field_element($show_in_order, $this->cell_props_CBS, false);
				$this->render_form_field_element($show_in_thank_you_page, $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
				<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_my_account_page'], $this->cell_props_CBS, false);
				?>
				</td>
            </tr>
        </table>
        <?php   
	}
	
	private function render_form_field_default(){
		?>
        <table id="thwcfe_field_form_id_default" class="thpladmin_field_info_tbl" width="100%" style="display:none;">
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['title'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['description'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['value'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['placeholder'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
            	$this->render_form_field_element($this->field_form_props['input_class'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['title_class'], $this->cell_props_R);
				?>
            </tr>
            <tr>
            	<?php
				$this->render_form_field_element($this->field_form_props['cssclass'], $this->cell_props_L);
            	$this->render_form_field_element($this->field_form_props['validate'], $this->cell_props_R);
				?>
            </tr>
            <?php 
				$this->render_form_element_h_spacing(); 
			?>
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['required'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['clear'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['enabled'], $this->cell_props_CB, false);
				?>
                </td>
            </tr> 
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_email'], $this->cell_props_CBL, false);
				$this->render_form_field_element($this->field_form_props['show_in_email_customer'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>
			<tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['show_in_order'], $this->cell_props_CBS, false);
				$this->render_form_field_element($this->field_form_props['show_in_thank_you_page'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>   
            <tr>
            	<td colspan="2">&nbsp;</td>
            	<td colspan="4">
            	<?php
            	$this->render_form_field_element($this->field_form_props['order_meta'], $this->cell_props_CB, false);
				$this->render_form_field_element($this->field_form_props['user_meta'], $this->cell_props_CB, false);
				?>
                </td>
            </tr>  
        </table>
        <?php   
	}
	
	private function render_field_form_fragment_general($form_type, $input_field = true){
		//$field_name_label = $input_field ? THWCFE_i18n::t('Name') : THWCFE_i18n::t('ID');
		?>
        <table width="100%">
            <tr>                
                <td colspan="6" class="err_msgs"></td>
            </tr> 
            
            <?php if($form_type === 'edit'){ ?> 
            <tr>
            	<td colspan="6">
                    <input type="hidden" name="i_rowid" value="" />
                    <input type="hidden" name="i_original_type" value="" />
                </td>
            </tr>    
            <?php } ?>  
                	         
            <tr>  
            <?php 
				$this->render_form_field_element($this->field_form_props['name'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['type'], $this->cell_props_R); 
			?>         
            </tr>  
		</table> 
		<?php 
	}

    private function render_field_form_fragment_options(){
		?>
		<tr>  
		<?php
			$this->render_form_field_element($this->field_form_props['taxable'], $this->cell_props_L);
			$this->render_form_field_element($this->field_form_props['tax_class'], $this->cell_props_R);
		?>
        </tr>
		<tr>
			<td width="13%" valign="top"><?php THWCFE_i18n::et('Options'); ?></td>
			<?php $this->render_form_element_tooltip(false); ?>
			<td colspan="4">
				<table border="0" cellpadding="0" cellspacing="0" class="thwcfe-option-list thpladmin-dynamic-row-table"><tbody>
					<tr>
						<td style="width:190px;"><input type="text" name="i_options_key[]" placeholder="Option Value" style="width:180px;"/></td>
						<td style="width:190px;"><input type="text" name="i_options_text[]" placeholder="Option Text" style="width:180px;"/></td>
						<td style="width:80px;"><input type="text" name="i_options_price[]" placeholder="Price" style="width:70px;"/></td>
						<td style="width:130px;">    
							<select name="i_options_price_type[]" style="width:120px;">
								<option selected="selected" value="">Normal</option>
								<option value="percentage">Percentage of Cart Contents Total</option>
								<option value="percentage_subtotal">Percentage of Subtotal</option>
								<option value="percentage_subtotal_ex_tax">Percentage of Subtotal Ex Tax</option>
							</select>
						</td>
						<td class="action-cell"><a href="javascript:void(0)" onclick="thwcfeAddNewOptionRow(this)" class="btn btn-blue" title="Add new option">+</a></td>
						<td class="action-cell"><a href="javascript:void(0)" onclick="thwcfeRemoveOptionRow(this)" class="btn btn-red" title="Remove option">x</a></td>
						<td class="action-cell sort ui-sortable-handle"></td>
					</tr>
				</tbody></table>            	
			</td>
		</tr>
        <?php
	}
	
	private function render_field_form_fragment_title($show_subtitle = false){
		?>
        <tr>                
        <?php
			$title_props = $this->field_form_props['title'];
			if($title_props['label']){
				$title_props['label'] = 'Title';
			}
			
			$title_class_props = $this->field_form_props['title_class'];
			if($title_class_props['label']){
				$title_class_props['label'] = 'Title Class';
			}
			
			$this->render_form_field_element($title_props, $this->cell_props_L);
			$this->render_form_field_element($this->field_form_props['title_type'], $this->cell_props_R);
		?>
        </tr>  
        <tr>
        <?php	
			$this->render_form_field_element($this->field_form_props['title_color'], $this->cell_props_CP);
			$this->render_form_field_element($title_class_props, $this->cell_props_R);
		?>
        </tr>
        <?php
		if($show_subtitle){
			$this->output_h_separator(false);
			?>
			<tr class="thwcfe_subtitle_row">              
			<?php
				$this->render_form_field_element($this->field_form_props['subtitle'], $this->cell_props_L);
				$this->render_form_field_element($this->field_form_props['subtitle_type'], $this->cell_props_R);
			?>
			</tr>
			<tr class="thwcfe_subtitle_row"> 
			<?php
				$this->render_form_field_element($this->field_form_props['subtitle_color'], $this->cell_props_CP);
				$this->render_form_field_element($this->field_form_props['subtitle_class'], $this->cell_props_R);
			?>
            </tr>
            <?php
		}
	}
	
	private function render_field_form_fragment_datepicker(){
		?>
        <tr>  
		<?php
            $this->render_form_field_element($this->field_form_props['date_format'], $this->cell_props_L);
            $this->render_form_field_element($this->field_form_props['default_date'], $this->cell_props_R);
        ?>
        </tr>
        <tr>  
		<?php
            $this->render_form_field_element($this->field_form_props['min_date'], $this->cell_props_L);
            $this->render_form_field_element($this->field_form_props['max_date'], $this->cell_props_R);
        ?>
        </tr>
        <tr>  
		<?php
            $this->render_form_field_element($this->field_form_props['year_range'], $this->cell_props_L);
            $this->render_form_field_element($this->field_form_props['number_of_months'], $this->cell_props_R);
        ?>
        </tr>
        <tr>  
		<?php
            $this->render_form_field_element($this->field_form_props['disabled_days'], $this->cell_props_L);
            $this->render_form_field_element($this->field_form_props['disabled_dates'], $this->cell_props_R);
        ?>
        </tr>
        <?php 
    }
	
	private function render_field_form_fragment_timepicker(){
		?>
        <tr>  
		<?php
            $this->render_form_field_element($this->field_form_props['min_time'], $this->cell_props_L);
            $this->render_form_field_element($this->field_form_props['max_time'], $this->cell_props_R);
        ?>
        </tr>
        <tr>  
		<?php
            $this->render_form_field_element($this->field_form_props['start_time'], $this->cell_props_L);
            $this->render_form_field_element($this->field_form_props['time_step'], $this->cell_props_R);
        ?>
        </tr>
        <tr>  
		<?php
            $this->render_form_field_element($this->field_form_props['time_format'], $this->cell_props_L);
            $this->render_form_element_empty_cell();
        ?>
        </tr>
        <?php
    }
	
	private function render_field_form_fragment_price($type = false){
		?>
        <tr>                
            <td width="13%"><?php THWCFE_i18n::et('Price'); ?></td>
            <?php 
			$price_field = $this->field_form_props['price'];
			$tooltip = ( isset($price_field['hint_text']) && !empty($price_field['hint_text']) ) ? $price_field['hint_text'] : false;
			$this->render_form_element_tooltip($tooltip);
			?>
            <td width="34%">
            	<input type="text" name="i_price" placeholder="Price" style="width:250px;" class="thpl-price-field"/>
                <label class="thpl-dynamic-price-field" style="display:none">per</label>
                <input type="text" name="i_price_unit" placeholder="Unit" style="width:80px; display:none" class="thpl-dynamic-price-field"/>
                <label class="thpl-dynamic-price-field" style="display:none">unit</label>
            </td>
		<?php 
			$field_props = $this->field_form_props['price_type'];
			$options = isset($field_props['options']) ? $field_props['options'] : array();
			
			if($type === 'datepicker' || $type === 'timepicker'){
				unset($options['custom']);
				unset($options['dynamic']);
			}
			
			$field_props['options'] = $options;
			$this->render_form_field_element($field_props, $this->cell_props_R); 
		?>
        </tr>  
		<tr>  
		<?php
			$this->render_form_field_element($this->field_form_props['taxable'], $this->cell_props_L);
			$this->render_form_field_element($this->field_form_props['tax_class'], $this->cell_props_R);
		?>
        </tr>
        <?php
	}
	
	private function render_field_form_fragment_rules($type="field"){
		?>
        <tr>
        	<td style="padding-left: 12px;">
                <select name="i_rules_action" style="width:80px;">
                    <option value="show">Show</option>
                    <option value="hide">Hide</option>
                </select>
                <?php echo $type; ?> if all below conditions are met.
            </td>
        </tr>
        <tr>                
            <td>
            	<table class="thwepo_conditional_rules" width="100%"><tbody>
                    <tr class="thwepo_rule_set_row">                
                        <td>
                            <table class="thwepo_rule_set" width="100%"><tbody>
                                <tr class="thwepo_rule_row">
                                    <td>
                                        <table class="thwepo_rule" width="100%" style=""><tbody>
                                            <tr class="thwepo_condition_set_row">
                                                <td>
                                                    <table class="thwepo_condition_set" width="100%" style=""><tbody>
                                                        <tr class="thwepo_condition">
                                                            <td width="25%">
                                                                <select name="i_rule_operator" style="width:200px;" onchange="thwcfeRuleOperatorChangeListner(this)">
                                                                    <option value=""></option>
                                                                    <option value="cart_contains">Cart contains</option>
                                                                    <option value="cart_not_contains">Cart not contains</option>
                                                                    <option value="cart_only_contains">Cart only contains</option>
                                                                    
																	<option value="cart_subtotal_eq">Cart subtotal equals to</option>
                                                                    <option value="cart_subtotal_gt">Cart subtotal greater than</option>
                                                                    <option value="cart_subtotal_lt">Cart subtotal less than</option>
                                                                    <option value="cart_total_eq">Cart total equals to</option>
                                                                    <option value="cart_total_gt">Cart total greater than</option>
                                                                    <option value="cart_total_lt">Cart total less than</option>
																	
																	<option value="user_role_eq">User role equals to</option>
                                                                    <option value="user_role_ne">User role not equals to</option>
                                                                    
                                                                    <?php /*?><option value="count_eq">Product count equals to</option>
                                                                    <option value="count_gt">Product count greater than</option>
                                                                    <option value="count_lt">Product count less than</option><?php */?>
                                                                </select>
                                                            </td>
                                                            <td width="25%">
                                                                <select name="i_rule_operand_type" style="width:200px;" onchange="thwcfeRuleOperandTypeChangeListner(this)">
                                                                    <option value=""></option>
                                                                    <option value="product">Product</option>
																	<option value="product_variation">Product Variation</option>
                                                                    <option value="category">Category</option>
                                                                </select>
                                                            </td>
                                                            <td width="25%" class="thpladmin_rule_operand"><input type="text" name="i_rule_operand" style="width:200px;"/></td>
                                                            <td class="actions">
                                                                <a href="javascript:void(0)" class="thpl_logic_link" onclick="thwcfeAddNewConditionRow(this, 1)" title="">AND</a>
                                                                <a href="javascript:void(0)" class="thpl_logic_link" onclick="thwcfeAddNewConditionRow(this, 2)" title="">OR</a>
                                                                <a href="javascript:void(0)" class="thpl_delete_icon" onclick="thwcfeRemoveRuleRow(this)" title="Remove"></a>
                                                            </td>
                                                        </tr>
                                                    </tbody></table>
                                                </td>
                                            </tr>
                                        </tbody></table>
                                    </td>
                                </tr>
                            </tbody></table>            	
                        </td>            
                    </tr> 
        		</tbody></table>
        	</td>
        </tr>
        <?php
	}
	
	private function render_field_form_fragment_rules_ajax($type="field"){
		?>
        <tr><td style="border-top: 1px dashed #e6e6e6;">&nbsp;</td></tr>
        <tr>
        	<td style="padding-left: 12px;">
                <select name="i_rules_action_ajax" style="width:80px;">
                    <option value="show">Show</option>
                    <option value="hide">Hide</option>
                </select>
                <?php echo $type; ?> if all below conditions are met.
            </td>
        </tr>
        <tr>                
            <td>
            	<table class="thwepo_conditional_rules_ajax" width="100%"><tbody>
                    <tr class="thwepo_rule_set_row">                
                        <td>
                            <table class="thwepo_rule_set" width="100%"><tbody>
                                <tr class="thwepo_rule_row">
                                    <td>
                                        <table class="thwepo_rule" width="100%" style=""><tbody>
                                            <tr class="thwepo_condition_set_row">
                                                <td>
                                                    <table class="thwepo_condition_set" width="100%" style=""><tbody>
                                                        <tr class="thwepo_condition">
                                                        	<td width="25%" class="thpladmin_rule_operand">
                                                            	<input type="hidden" name="i_rule_operand_type" value="field" />
                                                            	<?php $this->render_field_form_fragment_fields_select(); ?>
                                                            </td>
                                                            <td width="25%">
                                                                <select name="i_rule_operator" style="width:200px;" onchange="thwcfeRuleOperatorChangeListnerAjax(this)">
                                                                    <option value="">Please select an operator</option>
                                                                    <option value="empty">Is empty</option>
                                                                    <option value="not_empty">Is not empty</option>
                                                                    <option value="value_eq">Value equals to</option>
                                                                    <option value="value_ne">Value not equals to</option>
                                                                    <option value="value_gt">Value greater than</option>
                                                                    <option value="value_le">Value less than</option>
																	<option value="date_eq">Date equals to</option>
                                                                    <option value="date_ne">Date not equals to</option>
                                                                    <option value="date_gt">Date after</option>
                                                                    <option value="date_lt">Date before</option>
																	<option value="day_eq">Day equals to</option>
                                                                    <option value="day_ne">Day not equals to</option>
                                                                    <option value="checked">Is checked</option>
                                                                    <option value="not_checked">Is not checked</option>
                                                                </select>
                                                            </td>
                                                            <td width="25%"><input type="text" name="i_rule_value" style="width:200px;"/></td>
                                                            <td class="actions">
                                                              <a href="javascript:void(0)" class="thpl_logic_link" onclick="thwcfeAddNewConditionRowAjax(this, 1)" title="">AND</a>
                                                              <a href="javascript:void(0)" class="thpl_logic_link" onclick="thwcfeAddNewConditionRowAjax(this, 2)" title="">OR</a>
                                                              <a href="javascript:void(0)" class="thpl_delete_icon" onclick="thwcfeRemoveRuleRowAjax(this)" title="Remove"></a>
                                                            </td>
                                                        </tr>
                                                    </tbody></table>
                                                </td>
                                            </tr>
                                        </tbody></table>
                                    </td>
                                </tr>
                            </tbody></table>            	
                        </td>            
                    </tr> 
        		</tbody></table>
        	</td>
        </tr>
        <?php
	}
	
	/*private function render_field_form_fragment_rules_ajax(){
		?>
        <tr><td style="border-top: 1px dashed #e6e6e6;">&nbsp;</td></tr>
        <tr>
        	<td style="padding-left: 12px;">
                <select name="i_rules_action_ajax" style="width:80px;">
                    <option value="show">Show</option>
                    <option value="hide">Hide</option>
                </select>
                field if all below conditions are met.
            </td>
        </tr>
        <tr>                
            <td>
            	<table class="thwepo_conditional_rules_ajax" width="100%"><tbody>
                    <tr class="thwepo_rule_set_row">                
                        <td>
                            <table class="thwepo_rule_set" width="100%"><tbody>
                                <tr class="thwepo_rule_row">
                                    <td>
                                        <table class="thwepo_rule" width="100%" style=""><tbody>
                                            <tr class="thwepo_condition_set_row">
                                                <td>
                                                    <table class="thwepo_condition_set" width="100%" style=""><tbody>
                                                        <tr class="thwepo_condition">
                                                        	<td width="25%" class="thpladmin_rule_operand">
                                                            	<input type="hidden" name="i_rule_operand_type" value="field" />
                                                            	<?php $this->render_field_form_fragment_fields_select(); ?>
                                                            </td>
                                                            <td width="25%">
                                                                <select name="i_rule_operator" style="width:200px;" onchange="thwcfeRuleOperatorChangeListnerAjax(this)">
                                                                    <option value="">Please select an operator</option>
                                                                    <option value="empty">Is empty</option>
                                                                    <option value="not_empty">Is not empty</option>
                                                                    <option value="value_eq">Value equals to</option>
                                                                    <option value="value_ne">Value not equals to</option>
                                                                    <option value="value_gt">Value greater than</option>
                                                                    <option value="value_le">Value less than</option>
																	<option value="date_eq">Date equals to</option>
                                                                    <option value="date_ne">Date not equals to</option>
                                                                    <option value="date_gt">Date after</option>
                                                                    <option value="date_lt">Date before</option>
																	<option value="day_eq">Day equals to</option>
                                                                    <option value="day_ne">Day not equals to</option>
                                                                    <option value="checked">Is checked</option>
                                                                    <option value="not_checked">Is not checked</option>
                                                                </select>
                                                            </td>
                                                            <td width="25%"><input type="text" name="i_rule_value" style="width:200px;"/></td>
                                                            <td class="actions">
                                                              <a href="javascript:void(0)" class="thpl_logic_link" onclick="thwcfeAddNewConditionRowAjax(this, 1)" title="">AND</a>
                                                              <a href="javascript:void(0)" class="thpl_logic_link" onclick="thwcfeAddNewConditionRowAjax(this, 2)" title="">OR</a>
                                                              <a href="javascript:void(0)" class="thpl_delete_icon" onclick="thwcfeRemoveRuleRowAjax(this)" title="Remove"></a>
                                                            </td>
                                                        </tr>
                                                    </tbody></table>
                                                </td>
                                            </tr>
                                        </tbody></table>
                                    </td>
                                </tr>
                            </tbody></table>            	
                        </td>            
                    </tr> 
        		</tbody></table>
        	</td>
        </tr>
        <?php
	}*/
	
	private function render_field_form_fragment_product_list(){
		$products = apply_filters( "thpladmin_load_products", array() );
		array_unshift( $products , array( "id" => "-1", "title" => "All Products" ));
		?>
        <div id="thwcfe_product_select" style="display:none;">
        <select multiple="multiple" name="i_rule_operand" data-placeholder="Click to select products" class="thwcfe-enhanced-multi-select" style="width:200px;" value="">
			<?php 	
                foreach($products as $product){
                    echo '<option value="'. $product["id"] .'" >'. $product["title"] .'</option>';
                }
            ?>
        </select>
        </div>
        <?php
	}
	
	private function render_field_form_fragment_category_list(){		
		$categories = apply_filters( "thpladmin_load_products_cat", array() );
		array_unshift( $categories , array( "id" => "-1", "title" => "All Categories" ));
		?>
        <div id="thwcfe_product_cat_select" style="display:none;">
        <select multiple="multiple" name="i_rule_operand" data-placeholder="Click to select categories" class="thwcfe-enhanced-multi-select" style="width:200px;" value="">
			<?php 	
                foreach($categories as $category){
                    echo '<option value="'. $category["id"] .'" >'. $category["title"] .'</option>';
                }
            ?>
        </select>
        </div>
        <?php
	}
	
	private function render_field_form_fragment_user_role_list(){		
		$user_roles = apply_filters( "thpladmin_load_user_roles", array() );
		//array_unshift( $user_roles , array( "id" => "-1", "title" => "All User Roles" ));
		?>
        <div id="thwcfe_user_role_select" style="display:none;">
        <select multiple="multiple" name="i_rule_operand" data-placeholder="Click to select user roles" class="thwcfe-enhanced-multi-select" style="width:200px;" value="">
			<?php 	
                foreach($user_roles as $role){
                    echo '<option value="'. $role["id"] .'" >'. $role["title"] .'</option>';
                }
            ?>
        </select>
        </div>
        <?php
	}
	
	private function render_field_form_fragment_fields_wrapper(){		
		?>
        <div id="thwcfe_checkout_fields_select" style="display:none;">
			<?php $this->render_field_form_fragment_fields_select(); ?>
        </div>
        <?php
	}
	
	private function render_field_form_fragment_fields_select(){		
		$sections = THWCFE_Utils::get_custom_sections();	
		$show_name = apply_filters('thwcfe_show_filed_name_for_field_list_in_conditions_tab', true);
		
		$other_fields = array('ship-to-different-address-checkbox' => 'Ship to a different address');
		if(THWCFE_Utils::get_settings('enable_conditions_payment_shipping')){
			$other_fields['shipping_method[0]'] = 'Shipping Method';
			$other_fields['payment_method'] = 'Payment Method';
		}
		$other_fields = apply_filters('thwcfe_extra_fields_for_diaplay_rules', $other_fields);
		
		?>
        <select multiple="multiple" name="i_rule_operand" data-placeholder="Click to select field(s)" class="thwcfe-enhanced-multi-select" style="width:200px;" value="">
			<?php 
			if($sections && is_array($sections)){	
				foreach($sections as $sname => $section){	
					if($section && THWCFE_Utils_Section::is_valid_section($section)){
						$fields = THWCFE_Utils_Section::get_fields($section);
						if($fields && is_array($fields)){	
							echo '<optgroup label="'. $section->get_property('title') .'">';
							foreach($fields as $name => $field){
								if($field && THWCFE_Utils_Field::is_valid_field($field) && THWCFE_Utils_Field::is_enabled($field)){
									$label = $field->get_property('title');
									$label = empty($label) ? $name : $label;
									if($show_name){
										$label .= ' ('. $name .')';
									}
									echo '<option value="'. $name .'" >'. $label .'</option>';
								}
							}
							echo '</optgroup>';
						}
					}
				}
				echo '<optgroup label="Other Fields">';
				foreach($other_fields as $name => $label){
					if($name && $label){
						echo '<option value="'. $name .'" >'. THWCFE_i18n::t($label) .'</option>';
					}
				}
				echo '</optgroup>';
			}
            ?>
        </select>
        <?php 
	}
	
   /*******************************************
 	*-------- HTML FORM FRAGMENTS - END ------- 
 	*******************************************/
}

endif;