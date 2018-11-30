<?php
/*
Template Name: Sucursales
*/
?>

<link type="text/css" rel="stylesheet" href="<?php echo get_stylesheet_directory_uri() ?>/js/plugins/storelocator/css/map.css" />

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="<?php echo get_stylesheet_directory_uri() ?>/js/plugins/storelocator/select2.min.js"></script>
<script src="https://maps.google.com/maps/api/js?key=AIzaSyDXP96_yGYKEpJwArA2FE27gzWXJrgD260" type="text/javascript"></script>
<script src="<?php echo get_stylesheet_directory_uri() ?>/js/plugins/storelocator/js/handlebars-1.0.0.js"></script>
<script src="<?php echo get_stylesheet_directory_uri() ?>/js/plugins/storelocator/js/jquery.storelocator.js"></script>


<script>
        var url = "<?php echo get_site_url(); ?>";
        var url_site = "<?php echo get_site_url(); ?>";
        $(function() {
          /*$('#map-container').storeLocator({ slideMap : false,
					defaultLoc: true,
					defaultLat: "-33.417678",
					defaultLng : "-70.657080" , nameSearch: true , 'dataType': 'json', 'dataLocation': '/~wordprie/fhc/sucursales-json'});*/
            $('#bh-sl-map-container').storeLocator({ 'nameSearch': true });
           
        });
      </script>


<?php

get_header();

$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() );

?>

<div id="main-content">

<?php if ( ! $is_page_builder_used ) : ?>

	<div class="container">
		<div id="content-area" class="clearfix">
			<div id="left-area">

<?php endif; ?>

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( ! $is_page_builder_used ) : ?>

					<h1 class="entry-title main_title"><?php the_title(); ?></h1>
				<?php
					$thumb = '';

					$width = (int) apply_filters( 'et_pb_index_blog_image_width', 1080 );

					$height = (int) apply_filters( 'et_pb_index_blog_image_height', 675 );
					$classtext = 'et_featured_image';
					$titletext = get_the_title();
					$thumbnail = get_thumbnail( $width, $height, $classtext, $titletext, $titletext, false, 'Blogimage' );
					$thumb = $thumbnail["thumb"];

					if ( 'on' === et_get_option( 'divi_page_thumbnails', 'false' ) && '' !== $thumb )
						print_thumbnail( $thumb, $thumbnail["use_timthumb"], $titletext, $width, $height );
				?>

				<?php endif; ?>

					<div class="entry-content">
					<?php
						the_content();

						if ( ! $is_page_builder_used )
							wp_link_pages( array( 'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'Divi' ), 'after' => '</div>' ) );
					?>
                        



    <div class="et_pb_row et_pb_row_0">
        <div id="content-area" class="clearfix">
                      <div class="vspace"></div>
                    <div class="row">
                        <div class="col-sm-12 ">
                            
                            
                            
                            <!--**************************************************-->
                            <div class="bh-sl-container">
                                <div class="bh-sl-form-container">
                                        <form id="bh-sl-user-location" method="post" action="#" class="form-inline">
                                                <div class="form-group">
                                                        <div class="">
                                                                <label for="bh-sl-address" class="location-text">Seleccionar sucursal</label>
                                                                <select id="bh-sl-address" name="bh-sl-address" class="js-example-placeholder-single form-control select2 ">
                                                                     <option></option>
                                                                     <?php 
                                                                        $args = array(
                                                                                'post_type' => 'sucursales_post_type',
                                                                                'numberposts' => 1,
                                                                                'order' => 'DESC',
                                                                                'orderby' => 'meta_value'
                                                                        );

                                                                                $branch_posts = get_posts($args); 
                                                                                foreach($branch_posts as $post) : 
                                                                                setup_postdata( $post ); 
                                                                                $field = get_field_object('region');
                                                                                
                                                                                foreach($field['choices'] as $key => $value):
                                                                        ?>
                                                                        <option><?php echo $value ?></option>
                                                                        <?php endforeach; endforeach; wp_reset_postdata(); ?>
                                                                </select>
                                                                <button id="bh-sl-submit" type="submit" class="btn btn-primary">Ver</button>
                                                                <!--<label for="bh-sl-address">Enter Address or Zip Code:</label>
                                                                <input type="text" id="bh-sl-address" name="bh-sl-address" />-->
                                                        </div>
                                                </div>
                                        </form>
                                        <div id="bh-sl-map-container" class="bh-sl-map-container">
                                                <div id="bh-sl-map" class="bh-sl-map"></div>
                                                <div class="bh-sl-loc-list">
                                                        <ul class="list"></ul>
                                                </div>
                                        </div>
                                </div>
                            </div>
                            
                            
                            
                            
                            
                            

                        </div>
                    </div>
                

        </div>
    </div>

                            <!--***************************************-->
                        
                        
                        
                         <!-- ******************************************************************** -->    
                        
                        
                        
                        
					</div> <!-- .entry-content -->

				<?php
					if ( ! $is_page_builder_used && comments_open() && 'on' === et_get_option( 'divi_show_pagescomments', 'false' ) ) comments_template( '', true );
				?>

				</article> <!-- .et_pb_post -->

			<?php endwhile; ?>

<?php if ( ! $is_page_builder_used ) : ?>
                
                
                
            
                
                

			</div> <!-- #left-area -->

			<?php get_sidebar(); ?>
		</div> <!-- #content-area -->
	</div> <!-- .container -->

<?php endif; ?>

</div> <!-- #main-content -->

<?php

get_footer();

