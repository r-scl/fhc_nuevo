<div data-pws-tab="API Credentials" data-pws-tab-name="API Credentials">
	<div class="sgpm-tabs-content">
		<div class="sgpm-content sgpm-content-api sgpm-content-active">
			<form id="sgpm-form-api" class="sgpm-form" method="post" action="<?php echo admin_url();?>admin-post.php">
				<input type="hidden" name="action" value="sgpm_connect">
				<?php wp_nonce_field('sgpm_connect', 'wp-nonce-token'); ?>
				<h3>API Credentials</h3>
				<h4 class="sgpm-red">
					<strong>Please, connect to your Popup Maker account via API key to start using the plugin.</strong>
				</h4>
				<p>
					<em>Don’t have an account?
						<a href="<?php echo SGPM_SERVICE_URL.'signup'.SGPM_UTM_SOURCE_URL; ?>" title="Click here to view Popup Maker" target="_blank">Click here to create Popup Maker account</a>&nbsp;
						this will take only 30 seconds!
					</em>
				</p>
				<div class="sgpm-field-box sgpm-clear">
					<p class="sgpm-field-wrap">
						<label for="sgpm-field-key">API Key:</label>
						<input type="password" id="sgpm-field-key" name="sgpm-api-key" tabindex="431" value="<?php echo $options['apiKey']; ?>" placeholder="Enter your API Key here..."><br>
						<span class="sgpm-red">*</span>
						<span class="sgpm-field-desc">
							You can find your API key in the <a href="<?php echo SGPM_SERVICE_URL.'settings/index'.SGPM_UTM_SOURCE_URL; ?>" title="Settings section" target="_blank">Settings section</a> of your Popup Maker account.
						</span>
					</p>
				</div>
				<p class="submit">
					<input class="button button-primary" type="submit" name="sgpm-submit" value="Connect to Popup Maker" tabindex="749">
				</p>
			</form>
		</div>
	</div>
</div>
