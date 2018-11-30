<?php
function fhc_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
    
    wp_enqueue_script( 'script-js', get_stylesheet_directory_uri().'/js/functions.js', array( 'jquery'), NULL );
}
add_action( 'wp_enqueue_scripts', 'fhc_enqueue_styles' );
/**************************** WOOCOMMERCE *************************************/

/**
*
Move WooCommerce price
*
**/
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );

/**
 * Remove the breadcrumbs 
 */
add_action( 'init', 'woo_remove_wc_breadcrumbs' );
function woo_remove_wc_breadcrumbs() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
}

/**
 * Remove category on single page 
 */

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

/***********************IF NO PRICE SHOW REQUEST QUOTE************************

add_filter( 'woocommerce_get_price_html', 'bbloomer_price_free_zero_empty', 100, 2 );
  
function bbloomer_price_free_zero_empty( $price, $product ){
 
if ( '' === $product->get_price() || 0 == $product->get_price() ) {
    $price = '<button type="submit" name="request-quote" value="COTIZAR" class="single_add_to_cart_button button alt">COTIZAR</button>' ;
} 
 
return $price;
}
*/

add_filter( 'woocommerce_get_price_html', 'bbloomer_price_free_zero_empty', 100, 2 );
  
function bbloomer_price_free_zero_empty( $price, $product ){
 
if ( '' === $product->get_price() || 0 == $product->get_price() ) {
    add_action( 'woocommerce_single_product_summary', 'adding_modal_bot', 60 );    
    $price = '<span class="price"><ins><span class="woocommerce-Price-amount amount">'. get_field('producto_valor_uf', $price) .'&nbspUF</span></ins></span>';
				if (!empty($text)) {
				echo $text;
				}
} 
 
return $price;
}

/***************ADVANCEDCUSTOMFIELDS GET UF PRICE*********************************/

 
function adding_modal_bot() {
    
echo '<button type="button" name="request-quote" data-toggle="modal" data-target="#quote-modal" class="button alt">COTIZAR</button>

<!-- Modal -->
<div class="modal fade" id="quote-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalCenterTitle">Solicitud de Cotización</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ' .do_shortcode('[gravityform id="2" description="false" ajax="true"]').'
      </div>
    </div>
  </div>
</div>


';
}

/**************************************************BOOTSTRAP MODAL*************************************************/

function fhc_scripts() {
   
    //wp_enqueue_style( 'bootstrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/css/bootstrap.min.css', NULL );
    wp_enqueue_script( 'bootstrap-js', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0/js/bootstrap.min.js', array( 'jquery' ), NULL );    
}
add_action( 'wp_enqueue_scripts', 'fhc_scripts' );

/****************************************REMOVE DESCRIPTION TAB TITLE************************************************************************/

add_filter('woocommerce_product_description_heading', '__return_null');

/****************************************CHANGE ADD TO CART TEXT************************************************************************/

add_filter( 'woocommerce_product_single_add_to_cart_text', 'change_add_to_cart_text' );

function change_add_to_cart_text() {
        return __( 'Comprar', 'your-slug' );
}

/****************************************BOTÓN COTIZAR************************************************************************/

add_action( 'woocommerce_after_add_to_cart_button', 'add_content_after_addtocart_button_func1' );

function add_content_after_addtocart_button_func1() {
    
    echo '<button type="button" name="request-quote" data-toggle="modal" data-target="#quote-modal" class="button alt button_quote_all">COTIZAR</button>';

}

/****************************************TEXT AFTER ADD TO CART BUTTON************************************************************************/

add_action( 'woocommerce_after_add_to_cart_button', 'add_content_after_addtocart_button_func' );

function add_content_after_addtocart_button_func() {

    echo '<div class="clearfix"></div><div class="after-button">Plan incluye IVA</div>';

}

/***************************CHANGE TEXT BUTTON PROCEED TO CHECK OUT CART*********************************************/

remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 ); 
add_action('woocommerce_proceed_to_checkout', 'sm_woo_custom_checkout_button_text',20);

function sm_woo_custom_checkout_button_text() {
    $checkout_url = WC()->cart->get_checkout_url();
  ?>
       <a href="<?php echo $checkout_url; ?>" class="checkout-button button alt wc-forward"><?php  _e( 'CONTINUAR', 'woocommerce' ); ?></a> 
  <?php
} 

/********************************CUSTOM POST TYPE SUCURSALES****************************************/

if ( ! function_exists('sucursales_post_type') ) {

// Register Custom Post Type
function sucursales_post_type() {

	$labels = array(
		'name'                  => _x( 'Sucursales', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Sucursal', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Sucursales', 'text_domain' ),
		'name_admin_bar'        => __( 'Sucursales', 'text_domain' ),
		'archives'              => __( 'Item', 'text_domain' ),
		'attributes'            => __( 'Item Atributos', 'text_domain' ),
		'parent_item_colon'     => __( 'Item Superior:', 'text_domain' ),
		'all_items'             => __( 'Todos los items', 'text_domain' ),
		'add_new_item'          => __( 'Agregar nuevo item', 'text_domain' ),
		'add_new'               => __( 'Agregar nuevo', 'text_domain' ),
		'new_item'              => __( 'Nuevo Item', 'text_domain' ),
		'edit_item'             => __( 'Editar', 'text_domain' ),
		'update_item'           => __( 'Actualizar', 'text_domain' ),
		'view_item'             => __( 'Ver Item', 'text_domain' ),
		'view_items'            => __( 'Ver Items', 'text_domain' ),
		'search_items'          => __( 'Buscar', 'text_domain' ),
		'not_found'             => __( 'No Encontrado', 'text_domain' ),
		'not_found_in_trash'    => __( 'No Encontrado en papelera', 'text_domain' ),
		'featured_image'        => __( 'Imagen Destacada', 'text_domain' ),
		'set_featured_image'    => __( 'Definir Imagen Destacada', 'text_domain' ),
		'remove_featured_image' => __( 'Quitar Imagen Destacada', 'text_domain' ),
		'use_featured_image'    => __( 'Usar como imagen destacada', 'text_domain' ),
		'insert_into_item'      => __( 'Insertar en item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Subir a este item', 'text_domain' ),
		'items_list'            => __( 'Listados', 'text_domain' ),
		'items_list_navigation' => __( 'Listado de navegación', 'text_domain' ),
		'filter_items_list'     => __( 'Filtrar', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Sucursal', 'text_domain' ),
		'description'           => __( 'Sucursales', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-format-aside',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'sucursales_post_type', $args );

}
add_action( 'init', 'sucursales_post_type', 0 );

}

/************************ BOTON CONTINUAR COMPRANDO REDIRECT PLANES *******************************/

function my_woocommerce_continue_shopping_redirect( $return_to ) {
    $url = site_url( '/planes/' );
    return  $url;
}
add_filter( 'woocommerce_continue_shopping_redirect', 'my_woocommerce_continue_shopping_redirect', 20 );

/************************ HORARIO DE FUNCIONAMIENTO TIENDA *******************************/

add_action ('init', 'horario_funcionamiento');
   
function horario_funcionamiento() {
 
 add_action( 'woocommerce_before_main_content', 'tienda_disabled', 5 );
 add_action( 'woocommerce_before_cart', 'tienda_disabled', 5 );
 add_action( 'woocommerce_before_checkout_form', 'tienda_disabled', 5 );
 
}

function tienda_disabled() {

$time = current_time('H:i a');
    
$open_inicio = "08:00 am";
$open_fin = "21:30 pm";
    
$date1 = DateTime::createFromFormat('H:i a', $open_inicio);
$date2 = DateTime::createFromFormat('H:i a', $open_fin);

    if ($time >= $open_inicio && $time <= $open_fin )
    {    
    
    } else {
    wc_print_notice( '<p class="text-center">El horario de venta online es de 8:00 a 21:30.</p>', 'error');
    
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
    remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
    add_action( 'woocommerce_single_product_summary', 'quote_closed', 60 ); 

    }
} 

function quote_closed() {
    
echo '<button type="button" name="request-quote" data-toggle="modal" data-target="#quote-modal" class="button alt button_quote_all quote-closed">COTIZAR</button>';
}

/************************CAMBIO DE ORDEN PRECIO Y OFERTA EN PRODUCTOS******************************/

function mycustom_woocommerce_price_html($price, $product) {
    return preg_replace('@(<del>.*?</del>).*?(<ins>.*?</ins>)@misx', '$2 $1', $price);
}

add_filter('woocommerce_get_price_html', 'mycustom_woocommerce_price_html', 100, 2);

/************************************************************************************************/

add_action( 'woocommerce_before_shop_loop_item_title', 'add_product_cat', 25);
 function add_product_cat() {
 global $product;
 $product_cats = wp_get_post_terms($product->id, 'product_cat');
 $count = count($product_cats);
 foreach($product_cats as $key => $cat)
 {
     echo
    '<a href="'. get_term_link( $cat->term_id ) .'"><span class= "category-title">'.$cat->name.' </span></a>';
   if($key < ($count-1))
     {
         echo ' ';
     }
     else
     {
         echo ' ';
     }
 }
}

/**********************************CAMBIAR TEXTO CART TOTALS**************************************************************/

function change_cart_totals($translated) { 
  $translated = str_ireplace('Total del Carrito', 'Total del Carro', $translated);
  return $translated; 
}
add_filter('gettext', 'change_cart_totals' );