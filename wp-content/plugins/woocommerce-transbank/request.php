<?php
if (!isset($_COOKIE["ambient"])) {
	exit;
}



include '../../../wp-load.php';
require_once( __DIR__ . "/libwebpay/healthcheck.php");


$type = $_POST['type'];

	
	  
      switch($type)
	  {
		  case 'checkInit':
		  
			$response = [];
		  
			$arg = [
				'MODO' 			=> $_POST['MODE'],
				'COMMERCE_CODE'	=> $_POST['C_CODE'],
				'PUBLIC_CERT'   => $_POST['PUBLIC_CERT'],
				'PRIVATE_KEY'	=> $_POST['PRIVATE_KEY'],
				'WEBPAY_CERT'	=> $_POST['WEBPAY_CERT'],
				'ECOMMERCE'     => 'woocommerce'
			];
			
			$healthcheck = new HealthCheck($arg);
		  
			try
			{
				$response = $healthcheck->getInitTransaction();
				
				echo json_encode(['success' => true, 'msg' => json_decode($response)]);
			}
			catch (Exception $e)
			{
				echo json_encode(['success' => false, 'msg' => $e->getMessage()]);  
			}
		  
		  break;
	  }	
