<?php
	if (!defined('ABSPATH')) exit;

?>
<div class="sgpm-container">
	<div class="sgpm-content">
		<div class="sgpm-wrapper">
			<?php if (isset($options['isAuthenticate']) && !$options['isAuthenticate'] && isset($_GET['tryconnect'])): ?>
				<div class="error">
					<p>You must provide a valid API Key to authenticate to Popup Maker.</p>
				</div>
			<?php endif; ?>
			<div class="sgpm-tab-container">
			<?php if (isset($options['isAuthenticate']) && $options['isAuthenticate']): ?>
				<div data-pws-tab="Popups" data-pws-tab-name="Popups">
					<div class="sgpm-tabs-content">
						<div class="sgpm-content">
							<form id="sgpm-form-options-save" class="sgpm-form" method="post" action="<?php echo admin_url();?>admin-post.php">
								<input type="hidden" name="action" value="sgpm_options_save">
								<input type="hidden" name="sgpm-popup-id" value="<?php echo $popupId?>">
								<?php wp_nonce_field('sgpm_options_save', 'wp-nonce-token-options-save'); ?>
								<h3>
									<span class="sgpm-red"><?php echo $popup['title']?></span> Settings
								</h3>
								<div class="sgpm-margin-top-30 sgpm-popup-conditions-wrapper sgpm-popup-conditions-displayTarget" data-condition-type="displayTarget">
									<?php
										global $SGPM_DATA_CONFIG_ARRAY;
										$displayTargetData = (isset($popupSettings['displayTarget'])) ? $popupSettings['displayTarget'] : $SGPM_DATA_CONFIG_ARRAY['displayTarget']['initialData'];
										$creator = new SGPMCondition($displayTargetData);
										echo $creator->render();
									?>
								</div>
								<div class="sgpm-options-action-panel">
									<a class="sgpm-go-back-action" href="<?php echo SGPM_ADMIN_URL."admin.php?page=popup-maker-api-settings"?>">Back</a>
									<input class="sgpm-options-save-btn button button-primary" type="submit" name="sgpm-submit" value="Save Changes" tabindex="749">
								</div>
							</form>
						</div>
					</div>
				</div>
			<?php endif; ?>
			<?php
				require_once(SGPM_VIEW.'sgpm_api_credentials_content.php');
				require_once(SGPM_VIEW.'sgpm_support_content.php');
			?>
			</div>
		</div>
	</div>
</div>
