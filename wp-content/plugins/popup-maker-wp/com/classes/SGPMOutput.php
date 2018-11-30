<?php

class SGPMOutput
{
	public function __construct()
	{
		$this->set();
		add_action('wp_head', array($this, 'output'));
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
     * attach popup on website.
     *
     * @since 1.0.0
     */
    public function output()
	{
		$output = '';
		$embedCode = '<script>';
		$popupLoader = '';
		$defaultEmbedCode = "<script>
								window.SGPMPopupLoader=window.SGPMPopupLoader||{ids:[],popups:{},call:function(w,d,s,l,id){
									w['sgp']=w['sgp']||function(){(w['sgp'].q=w['sgp'].q||[]).push(arguments[0]);};
									var sg1=d.createElement(s),sg0=d.getElementsByTagName(s)[0];
									if(SGPMPopupLoader && SGPMPopupLoader.ids && SGPMPopupLoader.ids.length > 0){SGPMPopupLoader.ids.push(id); return;}
									SGPMPopupLoader.ids.push(id);
									sg1.onload = function(){SGPMPopup.openSGPMPopup();}; sg1.async=true; sg1.src=l;
									sg0.parentNode.insertBefore(sg1,sg0);
									return {};
								}};
							</script>";

		$options = get_option('sgpm_popup_maker_api_option');
		$postId = $pageId = get_the_ID();

		foreach ($options['popups'] as $popupId => $popup) {
			if (!isset($options['popupsSettings'][$popupId])) continue;
			$popupSettings = $options['popupsSettings'][$popupId];
			if (!isset($popupSettings['status']) || $popupSettings['status'] == 'disabled') continue;
			$displayTargets = $popupSettings['displayTarget'];

			if ($this->allowToSetPopup($displayTargets)) {
				$popupLoader.= "SGPMPopupLoader.call(window,document,'script','".SGPM_SERVICE_URL."assets/lib/SGPMPopup.min.js','".$popup['hashId']."');";
			}
		}

		if ($popupLoader) {
			$embedCode.= $popupLoader;
			$embedCode.= '</script>';
			$output.= $defaultEmbedCode;
			$output.= $embedCode;
			echo $output;
		}
    }

    private function allowToSetPopup($displayTargets)
	{
		$targetData = $this->divideIntoPermissiveAndForbidden($displayTargets);
		$isPostInForbidden = $this->isPostInForbidden($targetData);
		if ($isPostInForbidden) {
			return false;
		}
		$isPermissive = $this->isPermissive($targetData);
		return $isPermissive;
	}

	public function divideIntoPermissiveAndForbidden($postMetaData)
	{
		$permissive = array();
		$forbidden = array();

		foreach ($postMetaData as $data) {

			if (empty($data['operator'])) {
				break;
			}

			if ($data['operator'] == '==') {
				$permissive[] = $data;
			}
			else {
				$forbidden[] = $data;
			}
		}

		$postMetaDivideData = array(
			'permissive' => $permissive,
			'forbidden' => $forbidden
		);

		return $postMetaDivideData;
	}

	private function isPostInForbidden($target)
	{
		$isForbidden = false;

		if (empty($target['forbidden'])) {
			return $isForbidden;
		}

		foreach ($target['forbidden'] as $targetData) {
			if ($this->isInitPopup($targetData)) {
				$isForbidden = true;
				break;
			}
		}

		return $isForbidden;
	}

	private function isPermissive($target)
	{
		$isPermissive = false;

		if (empty($target['permissive'])) {
			$isPermissive = true;
			return $isPermissive;
		}

		foreach ($target['permissive'] as $targetData) {
			if ($this->isInitPopup($targetData)) {
				$isPermissive = true;
				break;
			}
		}

		return $isPermissive;
	}


	private function isInitPopup($targetData)
	{
		$isInit = false;
		$postId = $pageId = get_the_ID();
		$pageForPosts = get_option('page_for_posts');

		if (strpos($targetData['param'], '_all')) {
			$endIndex = strpos($targetData['param'], '_all');
			$postType = substr($targetData['param'], 0, $endIndex);
			$currentPostType = get_post_type($postId);

			if ($postType == $currentPostType) {
				$isInit = true;
			}
		}
		// for woocomerce
		else if (strpos($targetData['param'], '_selected_category')) {
			$terms = get_the_terms($postId, 'product_cat');
			if ($terms) {
				foreach ($terms as $key => $term) {
					if (in_array($term->name, $targetData['value'])) {
						$isInit = true;
						break;
					}
				}
			}
		}
		else if (strpos($targetData['param'], '_selected')) {
			$values = array();

			if (!empty($targetData['value'])) {
				$values = array_keys($targetData['value']);
			}

			if (in_array($postId, $values) || (is_Home() && in_array($pageForPosts, $values))) {
				$isInit = true;
			}
		}

		else if ($targetData['param'] == 'post_type' && !empty($targetData['value'])) {
			$selectedCustomPostTypes = array_values($targetData['value']);
			$currentPostType = get_post_type($postId);

			if (in_array($currentPostType, $selectedCustomPostTypes)) {
				$isInit = true;
			}
		}
		else if ($targetData['param'] == 'post_category' && !empty($targetData['value'])) {
			$values = $targetData['value'];
			$currentPostCategories = get_the_category($postId);

			foreach ($currentPostCategories as $categoryName) {
				if (in_array($categoryName->term_id, $values)) {
					$isInit = true;
					break;
				}

			}
		}
		else if ($targetData['param'] == 'page_type' && !empty($targetData['value'])) {
			$postTypes = $targetData['value'];
			foreach ($postTypes as $postType) {

				if ($postType == 'is_home_page') {
					if (is_front_page() && is_home()) {
						// Default homepage
						$isInit = true;
						break;
					} elseif (is_front_page()) {
						// static homepage
						$isInit = true;
					    break;
					}
				}
				else if ($postType()) {
					$isInit = true;
					break;
				}
			}
		}
		else if ($targetData['param'] == 'page_template' && !empty($targetData['value'])) {

			$currentPageTemplate = basename(get_page_template());
			if (in_array($currentPageTemplate, $targetData['value'])) {
				$isInit = true;
			}
		}

		return $isInit;
	}
}
