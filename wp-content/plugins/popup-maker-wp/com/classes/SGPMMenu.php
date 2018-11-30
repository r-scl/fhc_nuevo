<?php

class SGPMMenu
{
	public function __construct()
	{
		$this->set();
		add_action('admin_menu', array($this, 'menu'));
		add_action('admin_post_sgpm_options_save', array(new SGPMPage, 'optionsSave'));
		add_action('wp_ajax_sgpm_change_popup_status', array(new SGPMPage, 'changePopupStatus'));
		add_action('wp_ajax_sgpm_add_condition_rule_row', array(new SGPMPage, 'addConditionRuleRow'));
		add_action('wp_ajax_sgpm_change_condition_rule_row', array(new SGPMPage, 'changeConditionRuleRow'));
		add_action('wp_ajax_sgpm_select2_search_data', array(new SGPMPage, 'select2SearchData'));
	}

	 /**
	 * Sets our object instance and base class instance.
	 *
	 * @since 1.0.0
	 */
	public function set()
	{
		$this->base = SGPMBase::getInstance();
	}

	  /**
	 * Loads assets.
	 *
	 * @since 1.0.0
	 */
	public function assets()
	{
		add_action('admin_enqueue_scripts', array($this, 'styles'));
		add_action('admin_enqueue_scripts', array($this, 'scripts'));
		add_action('in_admin_header', array($this, 'outputPluginScreenBanner'));
	}

	/**
	 * Register and enqueue CSS.
	 *
	 * @since 1.0.0
	 */
	public function styles()
	{
		wp_register_style($this->base->pluginSlug.'-main', SGPM_ASSETS_URL.'css/main.css', array(), $this->base->version);
		wp_enqueue_style($this->base->pluginSlug.'-main');
		wp_register_style($this->base->pluginSlug.'-tab', SGPM_ASSETS_URL.'css/jquery.pwstabs.min.css', array(), $this->base->version);
		wp_enqueue_style($this->base->pluginSlug.'-tab');
		wp_register_style($this->base->pluginSlug.'-select2', SGPM_ASSETS_URL.'css/select2.min.css', array(), $this->base->version);
		wp_enqueue_style($this->base->pluginSlug.'-select2');
		wp_register_style($this->base->pluginSlug.'-switch-button-toggles', SGPM_ASSETS_URL.'css/switch-button-toggles.css', array(), $this->base->version);
		wp_enqueue_style($this->base->pluginSlug.'-switch-button-toggles');
	}

	/**
	 * Register and enqueue JS.
	 *
	 * @since 1.0.0
	 */
	public function scripts()
	{
		wp_register_script( $this->base->pluginSlug.'-main', SGPM_ASSETS_URL.'js/main.js', array('jquery'), $this->base->version, true );
		wp_enqueue_script($this->base->pluginSlug.'-main');
		wp_register_script($this->base->pluginSlug.'-tab', SGPM_ASSETS_URL.'js/jquery.pwstabs.min.js', array('jquery'), $this->base->version, true );
		wp_enqueue_script($this->base->pluginSlug.'-tab');
		wp_register_script( $this->base->pluginSlug.'-select2', SGPM_ASSETS_URL.'js/select2.min.js', array('jquery'), $this->base->version, true );
		wp_enqueue_script($this->base->pluginSlug.'-select2');
		wp_register_script($this->base->pluginSlug.'-options-panel', SGPM_ASSETS_URL.'js/SGPMOptionsPanel.js', array('jquery'), $this->base->version, true );
		wp_enqueue_script($this->base->pluginSlug.'-options-panel');
		wp_register_script($this->base->pluginSlug.'-switch-button-toggles', SGPM_ASSETS_URL.'js/switch-button-toggles.js', array('jquery'), $this->base->version, true );
		wp_enqueue_script($this->base->pluginSlug.'-switch-button-toggles');

		$localizeData = array(
			'handle' => $this->base->pluginSlug.'-options-panel',
			'name' => 'SGPM_JS_PARAMS',
			'data' => array(
				'url'   => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce(SGPM_AJAX_NONCE)
			)
		);
		wp_localize_script($localizeData['handle'], $localizeData['name'], $localizeData['data']);
	}

	/**
	 * Echo out plugin header banner
	 *
	 * @since 1.0.0
	 */
	public function outputPluginScreenBanner()
	{
		$html = '';
		$html .= '<div class="sgpm-static-banner">';
			$html .= '<div class="sgpm-inner-container">';
			$html .= '<div class="sgpm-logo-wrapper"><a href="'.esc_url_raw(SGPM_SERVICE_URL).SGPM_UTM_SOURCE_URL.'" target="_blank"><img class="sgpm-brand-logo" title="Popup Maker" alt="Popup Maker" src="'.SGPM_IMG_URL.'popup-maker-logo-glow.png"></a></div>';
			$html .= '<div class="sgpm-static-menu"><ul>';
			$html .= '<li><a class="sgpm-menu-link" href="'.esc_url_raw('https://help.popupmaker.com/en/').SGPM_UTM_SOURCE_URL.'" target="_blank">'. __('Help', 'popup-maker-api').'</a></li>';
			$html .= '<li><a class="sgpm-create-popup-btn" href="'.esc_url_raw(SGPM_SERVICE_URL).SGPM_UTM_SOURCE_URL.'" target="_blank">Home</a></li>';
			$html .= '</ul></div>';
		$html .= '</div>';
		$html .= '</div>';

		echo $html;
	}

	public function menu()
	{
		$this->hook = add_menu_page("Popup Maker", "Popup Maker", "manage_options", "popup-maker-api-settings", array($this, 'page'), 'dashicons-welcome-widgets-menus');

		if ($this->hook) {
			add_action('load-'.$this->hook, array($this, 'assets'));
		}
	}

	public function page()
	{
		$sgpmPage = new SGPMPage();
		$sgpmPage->init();
	}
}
