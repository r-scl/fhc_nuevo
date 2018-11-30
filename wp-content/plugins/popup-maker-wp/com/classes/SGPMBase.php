<?php

class SGPMBase
{
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = SGPM_VERSION;

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $pluginName = 'Popup Maker WP';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $pluginSlug = 'sgpmpopupmaker';

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		// Autoload the class files.
		spl_autoload_register('SGPMBase::autoload');
		register_activation_hook(SGPM_PATH.'popup-maker-api.php', array('SGPMBase', 'activate'));
		register_uninstall_hook(SGPM_PATH.'popup-maker-api.php', array('SGPMBase', 'deactivate'));
		// update data of old user
		add_action('plugins_loaded', array($this, 'overallInit'));

		$this->helper = new SGPMHelper();
		$this->menu = new SGPMMenu();
		$this->api = new SGPMApi();
		$this->output = new SGPMOutput();
		add_action('init', array($this, 'registerDataConfig'), 99999);
		add_action('admin_notices', array($this, 'reviewUsNotice'));
		add_action('admin_init', array($this, 'dismissReviewNotice'));
		add_action('admin_enqueue_scripts', array($this, 'adminStyles'));
	}

	public function adminStyles()
	{
		wp_register_style($this->pluginSlug.'-admin', SGPM_ASSETS_URL.'css/admin.css', array(), $this->version);
		wp_enqueue_style($this->pluginSlug.'-admin');
	}

	public function reviewUsNotice()
	{
		$showReviewNotice = true;
		$pastDate = strtotime('-30 days');

		$activationDate = get_option('sgpm_popup_maker_activation_date');
		$noticeDismissed = get_option('sgpm_popup_maker_dismiss_review_notice');

		if ($noticeDismissed == 'true') {
			$showReviewNotice = false;
		}
		else if (!in_array(get_current_screen()->base , array('dashboard' , 'post' , 'edit'))) {
			$showReviewNotice = false;
		}
		else if (!current_user_can('install_plugins')) {
			$showReviewNotice = false;
		}
		else if (!$activationDate || $activationDate > $pastDate) {
			$showReviewNotice = false;
		}

		if ($showReviewNotice) {
			$reviewUrl = 'https://wordpress.org/support/plugin/'.SGPM_PLUGIN_DIRECTORY.'/reviews/#new-post';
			$dismissUrl = esc_url_raw(add_query_arg('sgpm_dismiss_review_notice', '1', admin_url()));
			$reviewMessage = '<div class=""><img class="sgpm-review-logo" title="Popup Maker" alt="Popup Maker" src="'.SGPM_IMG_URL.'popup-maker-logo-glow.png"></div>';
			$reviewMessage .= sprintf( __( "It's been a couple of weeks you're using <strong>Popup Maker</strong>. We hope our features are useful for the conversion improvement of your site. We'd greatly appreciate if you could take a moment to leave some positive review for us. Your review is very motivating for us and helps us to improve our features to become popular worldwide." , $this->pluginSlug), $this->pluginName);
			$reviewMessage .= "<div class='sgpm-buttons'>";
			$reviewMessage .= sprintf("<a href='%s' target='_blank' class='button-secondary'><span class='dashicons dashicons-star-filled'></span>" . __( "Leave a Review" , $this->pluginSlug) . "</a>", $reviewUrl);
			$reviewMessage .= sprintf( "<a href='%s' class='button-secondary'><span class='dashicons dashicons-no-alt'></span>" . __( "Dismiss" , $this->pluginSlug) . "</a>", $dismissUrl);
			$reviewMessage .= "</div>";
?>
			<div class="sgpm-review-notice">
				<?php echo $reviewMessage; ?>
			</div>
<?php	}
	}

	public function dismissReviewNotice()
	{
		if (isset($_GET['sgpm_dismiss_review_notice'])) {
			add_option('sgpm_popup_maker_dismiss_review_notice', 'true');
		}
	}

	public function registerDataConfig()
	{
		if (file_exists(SGPM_CLASSES.'SGPMDataConfig.php')) {
			require_once(SGPM_CLASSES.'SGPMDataConfig.php');
			SGPMDataConfig::init();
		}
	}

	public function overallInit()
	{
		$options = get_option('sgpm_popup_maker_api_option');
		if (empty($options)) {
			$options = array();
		}
		if (isset($options['pluginVersion']) && $options['pluginVersion'] >= '1.13') return;

		$options['pluginVersion'] = SGPM_VERSION;
	 	if (!isset($options['popups'])) return;

	 	foreach ($options['popups'] as $popupId => $popup) {
	  		if (!isset($options['popupsSettings'][$popupId])) continue;
	  		$popupSettings = $options['popupsSettings'][$popupId];

			if (!isset($popupSettings['displayTarget'])) {
				$popupSettings['displayTarget'] = $this->getUpdatedSettingsForOldUser($popupSettings);
				$options['popupsSettings'][$popupId] = $popupSettings;
			}
	 	}

		update_option('sgpm_popup_maker_api_option', $options);
	}

	public function getUpdatedSettingsForOldUser($popupSettings)
	{
		$updatedSettings = array();

		if (isset($popupSettings['showOnAllPosts']) && $popupSettings['showOnAllPosts'] == 'on') {
			$updatedSettings[] = array(
				'param' => 'post_all',
				'operator' => '=='
			);
		}
		if (isset($popupSettings['showOnSomePosts']) && $popupSettings['showOnSomePosts'] == 'on') {
			$updatedSettings[] = array(
				'param' => 'post_selected',
				'operator' => '==',
				'value' => $this->getSelectedPostAssocArray($popupSettings['selectedPosts'])
			);
		}
		if (isset($popupSettings['showOnAllPages']) && $popupSettings['showOnAllPages'] == 'on') {
			$updatedSettings[] = array(
				'param' => 'page_all',
				'operator' => '=='
			);
			$updatedSettings[] = array(
				'param' => 'page_type',
				'operator' => '==',
				'value' => array('is_home_page')
			);
		}
		if (isset($popupSettings['showOnSomePages']) && $popupSettings['showOnSomePages'] == 'on') {
			$updatedSettings[] = array(
				'param' => 'page_selected',
				'operator' => '==',
				'value' => $this->getSelectedPostAssocArray($popupSettings['selectedPages'])
			);

			if (in_array('-1', $popupSettings['selectedPages'])) {
				$updatedSettings[] = array(
					'param' => 'page_type',
					'operator' => '==',
					'value' => array('is_home_page')
				);
			}

		}
		return $updatedSettings;
	}

	public function getSelectedPostAssocArray($selectedPost)
	{
		$newSelectedPost = array();
		foreach ($selectedPost as $key => $selectedPostId) {
			if ($selectedPostId == '-1') continue;
			$newSelectedPost[$selectedPostId] = get_the_title($selectedPostId);
		}
		return $newSelectedPost;
	}

	public static function autoload($classname)
	{
		// Return early if not the proper classname.
		if ('SGPM' !== substr($classname, 0, 4)) {
			return;
		}
		// Check if the file exists. If so, load the file.
		$filename = SGPM_CLASSES.$classname.'.php';
		if (file_exists($filename)) {
			require_once($filename);
		}
	}

	public static function activate()
	{
		$activationDate = get_option('sgpm_popup_maker_activation_date');
   		if (!$activationDate) {
   			add_option('sgpm_popup_maker_activation_date', strtotime("now"));
   		}
	}

	public static function deactivate()
	{
		delete_option('sgpm_popup_maker_api_option');
		delete_option('sgpm_popup_maker_activation_date');
		delete_option('sgpm_popup_maker_dismiss_review_notice');
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return SGPMBase
	 */
	public static function getInstance()
	{
		if (!isset( self::$instance ) && !(self::$instance instanceof SGPMBase)) {
			self::$instance = new SGPMBase();
		}

		return self::$instance;
	}
}
