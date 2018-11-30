<?php

class SGPMPage
{
	public function __construct()
	{
		$this->set();
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

    public function optionsSave()
	{
		//CSRF check
		if (!check_admin_referer('sgpm_options_save', 'wp-nonce-token-options-save')) {
			wp_die('Security check fail');
		}

		$diplayTarget = $this->getTargetData();

		$popupId = $this->sanitize('sgpm-popup-id');
		$options = get_option('sgpm_popup_maker_api_option');

		$options['popupsSettings'][$popupId]['displayTarget'] = $diplayTarget;
		update_option('sgpm_popup_maker_api_option', $options);

		wp_redirect(SGPM_ADMIN_URL."admin.php?page=popup-maker-api-settings&popupId=".$popupId);
		exit();
	}

	public function changePopupStatus()
	{
		// AJAX check
		check_ajax_referer(SGPM_AJAX_NONCE, 'nonce_ajax');
		global $SGPM_DATA_CONFIG_ARRAY;
		$popupId = intval($this->sanitize('popupId'));
		$popupStatus = $this->sanitize('popupStatus');
		$options = get_option('sgpm_popup_maker_api_option');
		$options['popupsSettings'][$popupId]['status'] = $popupStatus;
		if ($popupStatus == 'enabled' && !isset($options['popupsSettings'][$popupId]['displayTarget'])) {
			$options['popupsSettings'][$popupId]['displayTarget'] = $SGPM_DATA_CONFIG_ARRAY['displayTarget']['initialData'];
		}

		update_option('sgpm_popup_maker_api_option', $options);
		exit();
	}

	public function init()
	{
		$this->render();
	}

	public function render()
	{
		$options = get_option('sgpm_popup_maker_api_option');

		if (isset($_GET['popupId'])) {
			$popupId = $_GET['popupId'];
			$popup = $options['popups'][$popupId];
			if (isset($options['popupsSettings'][$popupId])) {
				$popupSettings = $options['popupsSettings'][$popupId];
			}

			require_once(SGPM_VIEW.'sgpm_popup_edit_content.php');
			return;
		}

		require_once(SGPM_VIEW.'sgpm_page_content.php');
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

	public function select2SearchData()
	{
		// AJAX check
		check_ajax_referer(SGPM_AJAX_NONCE, 'nonce_ajax');

		$objectKey = sanitize_text_field($_POST['objectKey']);
		$search = sanitize_text_field($_POST['searchTerm']);
		$objectKey = sanitize_text_field($_POST['objectKey']);
		$searchType = sanitize_text_field($_POST['searchType']);
		$search = sanitize_text_field($_POST['searchTerm']);

		if ($searchType == 'category') {

			$taxonomy = ! empty($objectKey) ? $objectKey : 'category';

			$args = array(
				'search'  => ! empty($search) ? $search : '',
				'include' => ! empty( $_REQUEST['include'] ) ? $_REQUEST['include'] : null,
				'page'    => ! empty( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : null,
				'number'  => 10,
			);

			$query = SGPMHelper::taxonomySelectlist($taxonomy, $args, true);

			foreach ( $query['items'] as $name => $id ) {
				$results['items'][] = array(
					'id'   => $id,
					'text' => $name,
				);
			}
		}
		else {
			$args      = array(
				's'              => $search,
				'post__in'       => ! empty( $_REQUEST['include'] ) ? array_map( 'intval', $_REQUEST['include'] ) : null,
				'page'           => ! empty( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : null,
				'posts_per_page' => 10,
				'post_type'      => $objectKey
			);

			$searchResults = SGPMHelper::getPostTypeData($args);

			if (empty($searchResults)) {
				$results['items'] = array();
			}

			/*Selected custom post type convert for select2 format*/
			foreach ($searchResults as $id => $name) {
				$results['items'][] = array(
					'id'   => $id,
					'text' => $name
				);
			}
		}

		echo json_encode($results);
		wp_die();
	}

	public function addConditionRuleRow()
	{
		// AJAX check
		check_ajax_referer(SGPM_AJAX_NONCE, 'nonce_ajax');

		$data = '';
		global $SGPM_DATA_CONFIG_ARRAY;
		$targetType = $this->sanitize('conditionName');
		$ruleId = (int)$_POST['ruleId'];
		$conditionRule = $SGPM_DATA_CONFIG_ARRAY[$targetType]['initialData'][0];
		$data .= SGPMCondition::createConditionRuleRow($conditionRule, $ruleId);

		echo $data;
		wp_die();
	}

	public function changeConditionRuleRow()
	{
		// AJAX check
		check_ajax_referer(SGPM_AJAX_NONCE, 'nonce_ajax');

		$data = '';
		global $SGPM_DATA_CONFIG_ARRAY;

		$targetType = $this->sanitize('conditionName');
		$conditionConfig = $SGPM_DATA_CONFIG_ARRAY[$targetType];
		$ruleId = $this->sanitize('ruleId');
		$paramName = sanitize_text_field($_POST['paramName']);
		$savedData = array(
			'param' => 	$paramName
		);

		if ($targetType == 'displayTarget' || $targetType == 'conditions') {
			$savedData['operator'] = '==';
		}

		$savedData['value'] = $conditionConfig['paramsData'][$paramName];
		$data .= SGPMCondition::createConditionRuleRow($savedData, $ruleId);

		echo $data;
		wp_die();
	}

	private function getTargetData()
	{
		$targetData = $_POST['sgpm-display-target'];
		global $SGPM_DATA_CONFIG_ARRAY;
		$targetConfig = $SGPM_DATA_CONFIG_ARRAY['displayTarget'];
		$paramsData = $targetConfig['paramsData'];
		$attrs = $targetConfig['attrs'];
		$popupTarget = array();

		foreach ($targetData as $ruleId => $ruleData) {

			if (empty($ruleData['value']) && !is_null($paramsData[$ruleData['param']])) {
				$targetData[$ruleId]['value'] = '';
			}

			if (isset($ruleData['value']) && is_array($ruleData['value'])) {

				$valueAttrs = @$attrs[$ruleData['param']];
				$postType = @$valueAttrs['data-value-param'];
				$isNotPostType = @$valueAttrs['isNotPostType'];
				$objectType = @$valueAttrs['data-value-type'];

				if (empty($valueAttrs['isNotPostType'])) {
					$isNotPostType = false;
				}

				if ($objectType == 'category') {
					$searchResults = array();
					foreach ($ruleData['value'] as $key => $catId) {
						$cat = get_term_by('id', $catId, $postType);
						$searchResults[$catId] = $cat->name;

					}
					$targetData[$ruleId]['value'] = $searchResults;
				}
				else if (!$isNotPostType) {
					$args = array(
						'post__in' => array_values($ruleData['value']),
						'posts_per_page' => 10,
						'post_type'      => $postType
					);
					$searchResults = SGPMHelper::getPostTypeData($args);
					$targetData[$ruleId]['value'] = $searchResults;
				}
			}
		}

		return $targetData;
	}
}
