<?php
	
add_filter('ninja_popups_subscribe_by_mailup', 'ninja_popups_subscribe_by_mailup', 10, 1);

function ninja_popups_subscribe_by_mailup($params = array()) 
{
	require_once SNP_DIR_PATH . '/include/mailup/snp_mailup.php';
	
	$ml_mailup_list = $params['popup_meta']['snp_ml_mailup_list'][0];
	if (!$ml_mailup_list) {
		$ml_mailup_list = snp_get_option('ml_mailup_list');
	}
	
	$result = array(
		'status' => false,
		'log' => array(
			'listId' => $ml_mailup_list,
			'errorMessage' => '',
		)
	);
	
	if (
		snp_get_option('ml_mailup_clientid') && 
		snp_get_option('ml_mailup_clientsecret') &&
		snp_get_option('ml_mailup_login') &&
		snp_get_option('ml_mailup_password') &&
		$ml_mailup_list
    ) {
	    $rest = new snp_mailup(
	    	snp_get_option('ml_mailup_clientid'),
	    	snp_get_option('ml_mailup_clientsecret'),
	    	snp_get_option('ml_mailup_login'),
	    	snp_get_option('ml_mailup_password')
	    );
	    
	    $args = array();
	    $args['Email'] = snp_trim($params['data']['post']['email']);
	    $args['Name'] = $params['data']['post']['name'];
	    $args['Fields'] = array();
	    if (!empty($params['data']['post']['name'])) {
		    $args['Fields'][] = array(
		    	'Id' => 1, 
		    	"Value" => $params['data']['names']['first']
		    );
		    $args['Fields'][] = array(
		    	'Id' => 2,
		    	"Value" => $params['data']['names']['last']
		    );
		}
		
		if (count($params['data']['cf']) > 0) {
			foreach($params['data']['cf'] as $k => $v) {
				$args['Fields'][] = array(
					'Id' => $k,
					"Value" => $v
				);
			}
		}
		
		$double_optin = snp_get_option('ml_mailup_double_optin');
		if ($double_optin == 1) {
			$confirm = true;
		} else {
			$confirm = false;
		}
		
		try {
			$response = $rest->subscribe($ml_mailup_list, $args, $confirm);
			
			if (isset($response) && is_int($response)) {
				$result['status'] = true;
			}
		} catch (Exception $e) {
			$result['log']['errorMessage'] = $e->getMessage();
		}
	}
	
	return $result;
}