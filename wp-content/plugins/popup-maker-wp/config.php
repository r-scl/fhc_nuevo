<?php

define('SGPM_DS', DIRECTORY_SEPARATOR);
define('SGPM_PATH', dirname(__FILE__).SGPM_DS);
define('SGPM_URL', plugins_url('', __FILE__).'/');
define('SGPM_ASSETS_URL', SGPM_URL.'public/assets/');
define('SGPM_IMG_URL', SGPM_URL.'public/images/');
define('SGPM_ADMIN_URL', admin_url());
define('SGPM_VIEW', SGPM_PATH.'com'.SGPM_DS.'view'.SGPM_DS);
define('SGPM_CLASSES', SGPM_PATH.'com'.SGPM_DS.'classes'.SGPM_DS);
define('SGPM_VERSION', '1.214');
define('SGPM_SERVICE_URL', 'https://popupmaker.com/');
define('SGPM_POPUP_TEXT_DOMAIN', 'sgpmPopupMaker');
define('SGPM_AJAX_NONCE', 'popup-maker-ajax-nonce');
define('SGPM_POPUP_POST_TYPE', 'sgpmPopupMaker');
define('SGPM_UTM_SOURCE_URL', '?utm_source=wordpress&utm_medium=website');
define('SGPM_PLUGIN_DIRECTORY', 'popup-maker-wp');
