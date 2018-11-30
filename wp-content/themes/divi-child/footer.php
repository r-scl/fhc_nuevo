<?php
/**
 * Fires after the main content, before the footer is output.
 *
 * @since 3.10
 */
do_action( 'et_after_main_content' );

if ( 'on' == et_get_option( 'divi_back_to_top', 'false' ) ) : ?>

	<span class="et_pb_scroll_top et-pb-icon"></span>

<?php endif;

if ( ! is_page_template( 'page-template-blank.php' ) ) : ?>

			<footer id="main-footer">
                
                <?php if (is_checkout()) {?>
 
                <!-- Modal WEBPAY -->
<div class="modal fade" id="modal-cuota" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="exampleModalLabel"><strong>Cuota Mortuoria (CM)</strong></h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

<h3>¿Qué es la Cuota Mortuoria (CM) y para qué me sirve?</h3>
<p>
La CM es un beneficio que entregan el Instituto de Previsión Social (IPS), las AFP y las Compañías de Seguros, la que tiene por objetivo alivianar los costos del proceso y/o del funeral. Los valores que cubren actualmente estas instituciones son:</p>
<ul class="list-cm">
	<li>1.	IPS: UF 15</li>
	<li>2.	AFP y Compañías de Seguros: UF 15, calculadas al día de la defunción.</li>
</ul>

<p><strong>Mayor información Ir a :<a href="https://www.spensiones.cl/portal/orientacion/580/w3-propertyvalue-6160.html" target="blank"> Superintendencia de Pensiones (CM)</a></strong></p>

<h3>¿Cómo saber si la persona fallecida tiene derecho a cuota mortuoria?</h3>
<p>
Para realizar las consultas respectivas, se deberá comunicar vía telefónica o acercarse directamente a la institución en la que el fallecido(a) poseía sus cotizaciones o pago de jubilación; indicando el RUT del fallecido. El contacto telefónico se debe realizar en los siguientes números:
</p>
<ul class="list-cm">
	<li>1.	IPS: Teléfono 101 opción 9</li>
	<li>2.	AFP y Compañías de Seguros: Contactarse a los números 600 respectivos de cada institución.</li>
</ul>
<h3>¿Cómo cobrar la cuota mortuoria en la institución que tiene el derecho?</h3>
<p>
Es importante que se acerque a la institución respectiva la persona que acredite (debidamente), haberse hecho cargo del pago del servicio funerario. Para esto, dicha persona deberá presentar la factura a su nombre, indicando el contratante y el beneficiario(a).</p>
<p>
La documentación solicitada por las distintas instituciones es al siguiente:</p>
<ul class="list-cm">
    <li>1.	IPS: presentarse con la factura, la persona a quien se facturo el o los servicio(s) funerario(s).</li>
    <li>
2.	AFP: presentarse con factura y certificado de defunción con causa de muerte, en la AFP que corresponda al fallecido. Dependiendo de AFP se deberá completar un formulario anexo por cada persona que se hiciese cargo del servicio(s) funerario(s).</li>
    <li>
3.	Compañías de Seguro: presentarse con factura y certificado de defunción con causa de muerte, la persona que se hizo cargo del pago del o los servicio(s) funerario(s).</li>
</ul>



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
                <?php } ?>
                
				<?php get_sidebar( 'footer' ); ?>


		<?php
			if ( has_nav_menu( 'footer-menu' ) ) : ?>

				<div id="et-footer-nav">
					<div class="container">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer-menu',
								'depth'          => '1',
								'menu_class'     => 'bottom-nav',
								'container'      => '',
								'fallback_cb'    => '',
							) );
						?>
					</div>
				</div> <!-- #et-footer-nav -->

			<?php endif; ?>

				<div id="footer-bottom">
					<div class="container clearfix">
				<?php
					if ( false !== et_get_option( 'show_footer_social_icons', true ) ) {
						get_template_part( 'includes/social_icons', 'footer' );
					}

					echo et_get_footer_credits();
				?>
					</div>	<!-- .container -->
				</div>
			</footer> <!-- #main-footer -->
		</div> <!-- #et-main-area -->

<?php endif; // ! is_page_template( 'page-template-blank.php' ) ?>

	</div> <!-- #page-container -->

 
<?php if (is_woocommerce() || is_page(895) || is_cart() || is_page(1075) ) { ?>
 
<?php echo do_shortcode('[et_pb_section global_module="470"][/et_pb_section]'); ?>
<?php echo do_shortcode('[et_pb_section global_module="481"][/et_pb_section]'); ?>
<?php echo do_shortcode('[et_pb_section global_module="71"][/et_pb_section]'); ?>
 
<?php } ?>

<?php if (!is_checkout()){ ?>

<!-- MODAL CREMATORIOS 
<div class="modal fade modales" id="modal-crematorios" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Crematorios en Santiago - Parque del recuerdo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe src="https://parquedelrecuerdo.cl/cinerario/" id="chat_closed" class="chat" scrolling="yes" style="border:0 none; width: 100%; height: 600px;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>-->

<!-- MODAL CORONAS 
<div class="modal fade modales" id="modal-coronas" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Crematorios en Santiago - Parque del recuerdo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe src="https://www.hogardecristo.cl/coronascaridad/" id="chat_closed" class="chat" scrolling="yes" style="border:0 none; width: 100%; height: 600px;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>-->

<!-- MODAL DEFUNCIONES 
<div class="modal fade modales" id="modal-defunciones" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Crematorios en Santiago - Parque del recuerdo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe src="https://www.emol.com/servicios/defunciones/" id="chat_closed" class="chat" scrolling="yes" style="border:0 none; width: 100%; height: 600px;"></iframe>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
-->

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
       <?php gravity_form(2, false, false, false, '', true, 12); ?>
      </div>
    </div>
  </div>
</div>

<?php } ?>



<!-- **********************************CHAT MENU*************************************************************************-->


<a href="#0" class="cd-btn js-cd-panel-trigger" data-panel="main"><img src="<?php bloginfo('url');?>/wp-content/uploads/2018/10/chatbtn.png" ></a>

<div class="cd-panel cd-panel--from-right js-cd-panel-main">
		<header class="cd-panel__header">
			<h3>¿Te podemos ayudar?</h3>
			<a href="#0" class="cd-panel__close js-cd-close">Cerrar</a>
		</header>

		<div class="cd-panel__container">
			<div class="cd-panel__content">
				
                <iframe src="https://was-chile.crossnet.la/WebAPI812/FHC/index.htm" id="chat_closed" class="chat" scrolling="no" style="border:0 none; width: 290px; height: 380px;"></iframe>
                
			</div> <!-- cd-panel__content -->
		</div> <!-- cd-panel__container -->
	</div> <!-- cd-panel -->

<!-- ***********************************************************************************************************-->


	<?php wp_footer(); ?>
</body>
</html>
