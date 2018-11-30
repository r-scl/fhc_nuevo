<?php

class SGPMApi
{
	public function __construct()
	{
		$this->set();
		$this->loadOption();
		add_action('admin_post_sgpm_connect', array($this, 'connect'));
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

	public function connect()
	{
		//CSRF check
		if (!check_admin_referer('sgpm_connect', 'wp-nonce-token')) {
			wp_die('Security check fail');
		}

		$options = get_option('sgpm_popup_maker_api_option');
		$connectUrl = SGPM_SERVICE_URL.'app/connect';
		$apiKey = $this->sanitize('sgpm-api-key');

		if (!$apiKey && $options['oldUser'] && $options['isAuthenticate'] && $options['apiKey']) {
			$apiKey = $options['apiKey'];
		}

		$data = array(
			'apiKey' => $apiKey,
			'appname' => 'Wordpress'
		);

		$argData = array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $data,
			'cookies' => array()
		);

		// connect call to service
		$response = wp_remote_post($connectUrl, $argData);

		if (is_wp_error($response)) {
			wp_die("Something went wrong after connection!");
			return;
		}

		global $SGPM_DATA_CONFIG_ARRAY;

		$data = json_decode($response['body'], true);

		$options['apiKey'] = '';
		$options['isAuthenticate'] = false;

		if (isset($data['isAuthenticate']) && $data['isAuthenticate']) {
			$options['oldUser'] = true;
			$options['isAuthenticate'] = true;
			$options['apiKey'] = $data['apiKey'];
			$options['popups'] = array_reverse($data['popups'], true);
			$options['user'] = $data['user'];
		}

		// enable last created popup
		if (empty($options['popupsSettings'])) {
			foreach ($options['popups'] as $popupId => $popup) {
				$options['popupsSettings'][$popupId]['status'] = 'enabled';
				$options['popupsSettings'][$popupId]['displayTarget'] = $SGPM_DATA_CONFIG_ARRAY['displayTarget']['initialData'];
				break;
			}
		}

		update_option('sgpm_popup_maker_api_option', $options);
		wp_redirect(SGPM_ADMIN_URL."admin.php?page=popup-maker-api-settings&tryconnect=1");
		exit();
	}

	/**
	 * Sets our global option if it is not found in the DB.
	 *
	 * @since 1.0.0
	 */
	public function loadOption()
	{
		$option = get_option('sgpm_popup_maker_api_option');
		if (!$option || empty($option)) {
			$option = self::defaultOptions();
			update_option('sgpm_popup_maker_api_option', $option);
		}
	}

	public static function defaultOptions()
	{
		$options = array(
			'isAuthenticate' => false,
			'apiKey' => '',
			'popups' => array(),
			'popupsSettings' => array(),
			'user' => array(
				'isActive' => false,
				'isExpired'  => false,
				'isDisabled' => false,
				'email' => '',
				'firstname' => '',
				'lastname' => ''
			),
			'oldUser' => false,
			'pluginVersion' => SGPM_VERSION
		);

		return $options;
	}

	public function sanitize($optionsKey)
	{
		if (isset($_POST[$optionsKey])) {
			return sanitize_text_field($_POST[$optionsKey]);
		}
		else {
			return "";
		}
	}
}
