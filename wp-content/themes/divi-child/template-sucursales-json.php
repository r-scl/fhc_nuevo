<?php
/*
Template Name: Sucursales JSON
*/
?>

<?php 
$args = array(
        'post_type' => 'sucursales_post_type',
        'numberposts' => -1,
        'order' => 'DESC',
        'orderby' => 'meta_value'
);

        /*{
        "id": "1",
        "name": "Punto de Venta Machali Rancagua ",
        "lat": "-34.181866",
        "lng": "-70.736708",
        "address": "Calle Padre Hurtado, N 3",
        "address2": "",
        "city": "Rancagua",
        "state": "",
        "postal": "",
        "phone": "72 2602205",
        "email": "machali@fhc.cl",
        "web": "http:\/\/",
        "hours1": "",
        "hours2": "",
        "hours3": ""
    }*/
        $branch_posts = get_posts($args);
        $array_response = array();
        $i = 1;
        foreach($branch_posts as $post){
            setup_postdata( $post );
            $tmp['id'] = $i;
            $tmp['name'] = get_the_title();
            $tmp["address"] = get_field('calle');
            $tmp["address2"] = get_field('referencia');
            $tmp["lat"] = get_field('lat');
            $tmp["lng"] = get_field('long');
            $tmp["city"] = get_field('ciudad');
            $tmp["state"] = '';
            $tmp["lng"] = get_field('long');
            $tmp["phone"] = get_field('telefono');
            $tmp["email"] = get_field('email');
            $tmp["web"] = '';
            $tmp["hours1"] = '';
            $tmp["hours2"] = '';
            $tmp["hours3"] = '';
            $array_response[] = $tmp;
            $i++;
        }
        
        echo json_encode($array_response); die();
?>
