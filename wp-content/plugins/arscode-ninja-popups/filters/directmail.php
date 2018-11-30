<?php
	
add_filter('ninja_popups_subscribe_by_directmail', 'ninja_popups_subscribe_by_directmail', 10, 1);

function ninja_popups_subscribe_by_directmail($params = array()) 
{
	require_once SNP_DIR_PATH . '/include/directmail/class.directmail.php';
	
	$form_id = snp_get_option('ml_dm_form_id');
	
	$result = array(
		'status' => false,
		'log' => array(
			'listId' => $form_id,
			'errorMessage' => '',
		)
	);
	
	if ($form_id) {
		$api = new DMSubscribe();
		$retval = $api->submitSubscribeForm($form_id, snp_trim($params['data']['post']['email']), $error_message);
		
		if ($retval) {
			$result['status'] = true;
		} else {
			$result['log']['errorMessage'] = $error_message;
        }
    }
    
    return $result;
}