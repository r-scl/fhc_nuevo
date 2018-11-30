<?php

class SGPMCondition
{
	public function __construct($targetData)
	{
		if (!empty($targetData)) {
			$this->setConditions($targetData);
		}
	}

	public function setConditions($conditions)
	{
		$this->conditions = $conditions;
	}

	public function getConditions()
	{
		return $this->conditions;
	}

	public function render()
	{
		$conditions = $this->getConditions();
		$view = '';
		end($conditions);
		$lastRuleId = key($conditions);
		foreach ($conditions as $ruleId => $condition) {
			$separator = '';
			$view .= self::createConditionRuleRow($condition, $ruleId, $lastRuleId);
		}

		return $view;
	}

	public static function createConditionRuleRow($condition, $ruleId, $lastRuleId = 0)
	{
		ob_start();
		?>
		<div class="sgpm-target-rule sgpm-target-rule-<?=$ruleId?>" data-rule-id="<?=$ruleId?>">
			<?php foreach($condition as $conditionName => $conditionValue): ?>
				<?php
					$idHiddenDiv = $conditionName;
					$showRowStatusClass = '';
					$hideStatus = self::getParamRowHideStatus($condition, $conditionName);
					$showRowStatusClass = ($hideStatus) ? 'sgpm-hide-condition-row' : $showRowStatusClass;
				?>
				<div data-condition-name="<?php echo $conditionName;?>" class="<?php echo 'sgpm-condition-'.$conditionName.'-wrapper'.' '.$showRowStatusClass; ?>">
					<div class="sgpm-condition-<?=$conditionName?>-content">
						<?php echo self::createConditionElement($condition, $conditionName, $ruleId);?>
					</div>
				</div>
			<?php endforeach; ?>
			<?php echo self::createConditionOperators($condition, $idHiddenDiv, $lastRuleId, $ruleId); ?>
		</div>
		<?php
		$targetOptionRow = ob_get_contents();
		ob_end_clean();
		return $targetOptionRow;
	}

	public static function createConditionOperators($condition, $idHiddenDiv = '', $lastRuleId, $ruleId)
	{
		global $SGPM_DATA_CONFIG_ARRAY;
		$operatorsHtml = '';
		$conditionData = $SGPM_DATA_CONFIG_ARRAY['displayTarget'];
		$operatorsData = $conditionData['operators'];

		if (empty($operatorsData)) {
			return $operatorsHtml;
		}

		foreach ($operatorsData as $operator) {
			$identificatorClass = '';
			$style = '';

			if ($operator['operator'] == 'edit') {
				$identificatorClass = $idHiddenDiv;
			}
			if ($operator['operator'] == 'add') {
				$style = '';
				/*Don't show add button if it's not for last element*/
				if ($ruleId < $lastRuleId) {
					$style = 'style="display: none;"';
				}
			}
			$operatorsHtml .= '<div class="sgpm-rules-'.$operator['operator'].'-button-wrapper" '.$style.'>
				<div class="sgpm-display-inline-grid">
					<span></span>
					<a href="#" class="sgpm-margin-top-7 sgpm-rules-action-button sgpm-rules-'.$operator['operator'].'-rule" data-id="'.$identificatorClass.'">
						'.__($operator['name'], SGPM_POPUP_TEXT_DOMAIN).'
					</a>
				</div>
			</div>';
		}

		return $operatorsHtml;
	}

	public static function createConditionElement($condition, $ruleName, $ruleId)
	{
		global $SGPM_DATA_CONFIG_ARRAY;
		$ruleElementData = array();
		$savedParam = '';
		$conditionConfig = $SGPM_DATA_CONFIG_ARRAY['displayTarget'];
		$rulesType = $conditionConfig['columnTypes'];
		$paramsData = $conditionConfig['paramsData'];
		$attrs = $conditionConfig['attrs'];

		if (!empty($condition[$ruleName])) {
			$savedParam =  $condition[$ruleName];
		}
		else if (!empty($condition['hiddenOption'])) {
			$savedParam = @$condition['hiddenOption'][$ruleName];
		}

		$ruleElementData['ruleName'] = $ruleName;
		if ($ruleName == 'value') {
			$ruleName = $condition['param'];
		}

		$type = @$rulesType[$ruleName];
		$data = @$paramsData[$ruleName];
		$attr = @$attrs[$ruleName];

		$ruleElementData['ruleId'] = $ruleId;
		$ruleElementData['type'] = $type;
		$ruleElementData['data'] = $data;
		$ruleElementData['saved'] = $savedParam;
		$ruleElementData['attr'] = $attr;
		$ruleElementData['condition'] = $condition;

		return self::createRuleField($ruleElementData);
	}

	public static function createRuleField($ruleElementData)
	{
		$attr = array();
		$type = $ruleElementData['type'];
		$condition = $ruleElementData['condition'];
		echo self::createElementHeader($ruleElementData);
		$name = 'sgpm-display-target['.$ruleElementData['ruleId'].']['.$ruleElementData['ruleName'].']';
		$attr['name'] = $name;

		if (is_array($ruleElementData['attr'])) {
			$attr += $ruleElementData['attr'];
		}
		$rowField = '';

		switch ($type) {
			case 'select':
				if (!empty($attr['multiple'])) {
					$attr['name'] .= '[]';
				}
				$savedData = $ruleElementData['saved'];

				if (empty($ruleElementData['data'])) {
					$ruleElementData['data'] = $ruleElementData['saved'];
					$savedData = array();

					if (!empty($ruleElementData['saved'])) {
						$savedData = array_keys($ruleElementData['saved']);
					}
				}
 				$rowField .= SGPMHelper::createSelectBox($ruleElementData['data'], $savedData, $attr);
				break;
			case 'text':
				$attr['type'] = $type;
				$rowField .= SGPMHelper::createInput($ruleElementData['data'], $ruleElementData['saved'], $attr);
				break;
			case 'checkbox':
				$attr['type'] = $type;
				$rowField .= SGPMHelper::createCheckBox($ruleElementData['data'], $ruleElementData['saved'], $attr);
				break;
		}

		return $rowField;
	}

	public static function createElementHeader($ruleElementData)
	{
		global $SGPM_DATA_CONFIG_ARRAY;
		$condition = $ruleElementData['condition'];
		$conditionName = 'displayTarget';
		$ruleName = $ruleElementData['ruleName'];
		$elementHeader = $ruleName;
		/*
		 *
		 * Check here labels
		 *
		 * */
		if ($ruleName == 'param') {
			$elementHeader =  __('Select Target', SGPM_POPUP_TEXT_DOMAIN);
		}
		else if ($ruleName == 'value') {

			$paramName = explode("_", $condition['param']);
			$paramName = $paramName[0];
			if (!isset($SGPM_DATA_CONFIG_ARRAY['displayTarget']['paramsData']['param'][$paramName][$condition['param']])) {
				$paramName =  ucfirst($paramName);
			}
			$elementHeader = @$SGPM_DATA_CONFIG_ARRAY['displayTarget']['paramsData']['param'][$paramName][$condition['param']];
		}
		else if ($ruleName == 'operator') {
			$elementHeader = ucfirst($ruleName);
		}

		return '<span class="sgpm-margin-bottom-5">'.$elementHeader.'</span>';
	}

	private static function getParamRowHideStatus($condition, $ruleName)
	{
		global $SGPM_DATA_CONFIG_ARRAY;

		if ($ruleName == 'hiddenOption') {
			return '';
		}
		$status = false;
		$conditionName = $ruleName;
		$conditionConfig = $SGPM_DATA_CONFIG_ARRAY['displayTarget'];
		$paramsData = $conditionConfig['paramsData'];
		$ruleElementData['ruleName'] = $ruleName;

		if ($ruleName == 'value') {
			$ruleName = $condition['param'];
		}

		if (is_null($paramsData[$ruleName])) {
			$status = true;
		}

		return $status;
	}
}
