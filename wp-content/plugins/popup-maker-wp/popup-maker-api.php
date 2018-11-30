<?php
/**
* Plugin Name: Popup Maker WP
* Plugin URI: https://popupmaker.com/wordpress
* Description: Popup Maker is the ultimate tool that will help you run a cleverer and more effective marketing popups for your website. Create the most optimal popups to boost your sales.
* Version: 1.2.1.4
* Author: Popup Maker
* Author URI: https://popupmaker.com/
*/
if (!defined('ABSPATH')) exit;

require_once(dirname(__FILE__).'/config.php');
require_once(SGPM_CLASSES.'SGPMBase.php');
$sgpmBase =  SGPMBase::getInstance();
$sgpmBase->init();
