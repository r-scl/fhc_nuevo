<?php
/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/home/fhc/web/funerariahogardecristo.cl/public_html/wp-content/plugins/wp-super-cache/' );
define('DB_NAME', 'wordprie_fhc2v');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'wordprie_fhc2v');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', 'fhc123');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'VM 1gN>k%$DK>JOJ4;lJb[Kk+>^ci]x&mK|II3uTb=oVfd}2[PG#SgovZPmqvz~~');
define('SECURE_AUTH_KEY',  '(UUx*Kz9-zsT!TRgU3l#-T2(DL/<3hloND{Kx-#g:I4-Tus~(8s(d:AVM(xCYxgj');
define('LOGGED_IN_KEY',    'EHbT@-PD?Y:I)Ahfg9)Jx@Tcr`pOZ3&TGazn/!}=l6 Ml8Vo-6iMRO:~I$HVM(bw');
define('NONCE_KEY',        'z@0s#._b[f+Rd&d*|hJ|o)8nz{d0ZOFJF7vEYk]1|MaMvKtUgaL)B+D5#-DJL=h.');
define('AUTH_SALT',        'zgTS|s?zpE#[o4|+2}-G#xq8GDZQ +KB-VEsmLr!2{@7gTHC8KT:{bZ83orZx=9G');
define('SECURE_AUTH_SALT', 'nN~qg=na$|Kep4daF=4&; ]B-JtH`>Ye.hz1@>RI4b_g%,ckPzmZwoFv/d9tp^T1');
define('LOGGED_IN_SALT',   ')q_=cv+O-U!fV0nZK+Kaz< ;F*F/k*>~K%@)YL/vxt%aHJt11a&|kZyB5^7qPxy&');
define('NONCE_SALT',       'XK%4_<m(Ji&c{AT-^ti{a_IlRkd7&pm1+)&eAno-kY+$F<+EuZczH+-0v,,$4>C)');
/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'fhc18_wsdesp_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

