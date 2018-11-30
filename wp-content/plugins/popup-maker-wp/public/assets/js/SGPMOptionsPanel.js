function SGPMOptionsPanel() {

}

SGPMOptionsPanel.prototype.init = function ()
{
	this.targetCondition();
	this.popupSelect2();
};

SGPMOptionsPanel.prototype.targetCondition = function()
{
	this.addRuleRow();
	this.removeRuleButton();
	this.changeConditionParams();
};

SGPMOptionsPanel.prototype.reInitRulesConfigButton = function ()
{
	this.addRuleRow();
	this.removeRuleButton();
	this.changeConditionParams();
	this.popupSelect2();
};

SGPMOptionsPanel.prototype.addRuleRow = function ()
{
	var that = this;
	jQuery('.sgpm-rules-add-rule').unbind();
	jQuery('.sgpm-rules-add-rule').on('click', function (e) {
		e.preventDefault();
		var prevRuleDiv = jQuery(this).parents('.sgpm-target-rule').first();
		var lastRuleId = parseInt(prevRuleDiv.attr('data-rule-id'));
		var conditionName = jQuery(this).parents('.sgpm-popup-conditions-wrapper').attr('data-condition-type');
		var ruleId = lastRuleId + 1;
		var data = {
			action: 'sgpm_add_condition_rule_row',
			nonce_ajax: SGPM_JS_PARAMS.nonce,
			conditionName: conditionName,
			ruleId: ruleId
		};
		jQuery.post(ajaxurl, data, function(response) {
			prevRuleDiv.after(response);
			jQuery('.sgpm-popup-conditions-'+conditionName+' >  .sgpm-target-rule-'+lastRuleId+' .sgpm-rules-add-button-wrapper').hide();
			that.reInitRulesConfigButton();
		});
	});
};

SGPMOptionsPanel.prototype.removeRuleButton = function ()
{
	jQuery('.sgpm-rules-delete-rule').unbind();
	jQuery('.sgpm-rules-delete-rule').on('click', function (e) {
		e.preventDefault();
		var currentTargetWrapperDiv = jQuery(this).parents('.sgpm-popup-conditions-wrapper').first();
		var currentRuleDiv = jQuery(this).parents('.sgpm-target-rule').first();
		var lastRuleDiv = currentTargetWrapperDiv.find('.sgpm-target-rule').last();
		var firstRuleDiv = currentTargetWrapperDiv.find('.sgpm-target-rule').first();
		var currentRulesLength = currentTargetWrapperDiv.find('.sgpm-target-rule').length;
		var currentRulId = currentRuleDiv.attr('data-rule-id');
		var lastRuleId = lastRuleDiv.attr('data-rule-id');
		var firstRuleId = firstRuleDiv.attr('data-rule-id');

		if (currentRulId > firstRuleId) {
			currentRuleDiv.remove();
		}

		/*When the last rule*/
		if (currentRulId == lastRuleId && currentRulesLength == 1) {
			alert('You can not delete the last rule.');
		}
		else {
			currentRuleDiv.remove();
		}

		if (currentRulId == lastRuleId) {
			lastRuleDiv = currentTargetWrapperDiv.find('.sgpm-target-rule').last();
			lastRuleDiv.find('.sgpm-rules-add-button-wrapper').removeAttr('style');
			lastRuleDiv.find('.sgpm-rules-add-button-wrapper').show();
		}

		if (currentRulId == firstRuleId) {
			if (currentRulesLength == 1) {
				currentTargetWrapperDiv.next('.sgpm-rules-or').remove();
			}
			else {
				currentRuleDiv.remove();
			}
		}
	});
};

SGPMOptionsPanel.prototype.changeConditionParams = function ()
{
	var that = this;
	jQuery('.sgpm-popup-conditions-wrapper .sgpm-condition-param-wrapper select').each(function () {
		jQuery(this).change(function (e) {
			e.preventDefault();
			var prevRuleDiv = jQuery(this).parents('.sgpm-target-rule').first();
			var currentTargetDiv = jQuery(this).parents('.sgpm-popup-conditions-wrapper').first();
			var ruleId = parseInt(prevRuleDiv.attr('data-rule-id'));
			var conditionName = currentTargetDiv.attr('data-condition-type');
			var paramSavedValue = jQuery(this).val();
			var data = {
				action: 'sgpm_change_condition_rule_row',
				nonce_ajax: SGPM_JS_PARAMS.nonce,
				conditionName: conditionName,
				paramName: paramSavedValue,
				ruleId: ruleId

			};

			jQuery.post(ajaxurl, data, function(response) {
				currentTargetDiv.find('.sgpm-target-rule-'+ruleId).after(response);
				currentTargetDiv.find('.sgpm-target-rule-'+ruleId).first().remove();
				that.reInitRulesConfigButton();
				if (currentTargetDiv.find('.sgpm-target-rule-'+ruleId).next().length) {
					jQuery('.sgpm-popup-conditions-'+conditionName+' > .sgpm-target-rule-'+ruleId+' .sgpm-rules-add-button-wrapper').hide();
				}
			});
		});
	});
};

SGPMOptionsPanel.prototype.popupSelect2 = function ()
{
	if (!jQuery('.js-sgpm-select2').length) {
		return;
	}

	jQuery('.js-sgpm-select2').each(function () {
		var type = jQuery(this).attr('data-select-type');
		var className = jQuery(this).attr('data-select-class');
		var options = {
			width: '100%'
		};

		if (type == 'ajax') {
			options = jQuery.extend(options, {
				minimumInputLength: 1,
				ajax: {
					url: SGPM_JS_PARAMS.url,
					dataType: 'json',
					delay: 250,
					type: "POST",
					data: function (params) {

						var searchKey = jQuery(this).attr('data-value-param');
						var searchType = jQuery(this).attr('data-value-type');

						return {
							action: 'sgpm_select2_search_data',
							nonce_ajax: SGPM_JS_PARAMS.nonce,
							searchTerm: params.term,
							objectKey: searchKey,
							searchType: searchType

						};
					},
					processResults: function (data) {
						return {
							results: jQuery.map(data.items, function (item) {
								return {
									text: item.text,
									id: item.id
								}
							})
						};
					}
				}
			});
		}

		jQuery(this).select2(options);
	});
};
