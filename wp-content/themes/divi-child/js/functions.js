jQuery(document).ready(function($) {
    
    $('#bot-crematorios').attr('data-toggle','modal');
    $('#bot-crematorios').attr('data-target','#modal-crematorios');
    $('#bot-coronas').attr('data-toggle','modal');
    $('#bot-coronas').attr('data-target','#modal-coronas');
    $('.bot-crematorios2 a').attr('data-toggle','modal');
    $('.bot-crematorios2 a').attr('data-target','#modal-crematorios');
    $('.bot-defunciones a').attr('data-toggle','modal');
    $('.bot-defunciones a').attr('data-target','#modal-defunciones');
    
    $("button[name = 'update_cart']").attr('value', 'Actualizar Carro');
    $("button[name = 'update_cart']").html('Actualizar Carro');
    
(function(){
    // Slide In Panel - by CodyHouse.co
    var panelTriggers = document.getElementsByClassName('js-cd-panel-trigger');
    if( panelTriggers.length > 0 ) {
        for(var i = 0; i < panelTriggers.length; i++) {
            (function(i){
                var panelClass = 'js-cd-panel-'+panelTriggers[i].getAttribute('data-panel'),
                    panel = document.getElementsByClassName(panelClass)[0];
                // open panel when clicking on trigger btn
                panelTriggers[i].addEventListener('click', function(event){
                    event.preventDefault();
                    addClass(panel, 'cd-panel--is-visible');
                });
                //close panel when clicking on 'x' or outside the panel
                panel.addEventListener('click', function(event){
                    if( hasClass(event.target, 'js-cd-close') || hasClass(event.target, panelClass)) {
                        event.preventDefault();
                        removeClass(panel, 'cd-panel--is-visible');
                    }
                });
            })(i);
        }
    }
    
    function hasClass(el, className) {
          if (el.classList) return el.classList.contains(className);
          else return !!el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
    }
    function addClass(el, className) {
         if (el.classList) el.classList.add(className);
         else if (!hasClass(el, className)) el.className += " " + className;
    }
    function removeClass(el, className) {
          if (el.classList) el.classList.remove(className);
          else if (hasClass(el, className)) {
            var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
            el.className=el.className.replace(reg, ' ');
          }
    }
})();


/************************************************************************************************/

/*
const today = new Date();
    
var hora1 = '8:00';
var hora2 = '21:30';
var inicio = today.getHours() + ':' + today.getMinutes();
    
    console.log(today);
    
if ( inicio >= hora1 && inicio <= hora2 ) {
  //$('.cd-btn').show();
    console.log('mostrar');
} else {
  //$('.cd-btn').hide();
    console.log('quitar');
}
*/

var date = new Date();
var hours = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
var minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
hora_tienda = hours + ":" + minutes;
      
var hora1 = '08:00';
var hora2 = '21:30';
    
if ( hora_tienda >= hora1 && hora_tienda <= hora2 ) {
  $('.cd-btn').show();
} else {
  $('.cd-btn').hide();
}    
/************************************************************************************************/
});
/************************************************************************************************/

jQuery(document).on('gform_post_render', function(){
    
     var RegionesYcomunas = {

    "regiones": [{
            "NombreRegion": "Arica y Parinacota",
            "comunas": ["Arica", "Camarones", "General Lagos", "Putre"]
    },
        {
            "NombreRegion": "Tarapacá",
            "comunas": ["Alto Hospicio", "Camiña", "Colchane", "Huara", "Iquique", "Pica", "Pozo Almonte"]
    },
        {
            "NombreRegion": "Antofagasta",
            "comunas": ["Antofagasta", "Calama", "María Elena", "Mejillones", "Ollagüe", "San Pedro de Atacama", "Sierra Gorda", "Taltal", "Tocopilla"]
    },
        {
            "NombreRegion": "Atacama",
            "comunas": ["Alto del Carmen", "Caldera", "Chañaral", "Copiapó", "Diego de Almagro", "Freirina", "Huasco", "Tierra Amarilla", "Vallenar"]
    },
        {
            "NombreRegion": "Coquimbo",
            "comunas": ["Andacollo", "Canela", "Combarbalá", "Coquimbo", "Illapel", "La Higuera", "La Serena", "Los Vilos", "Monte Patria", "Ovalle", "Paiguano", "Punitaqui", "Río Hurtado", "Salamanca", "Vicuña"]
    },
        {
            "NombreRegion": "Valparaíso",
            "comunas": ["Algarrobo", "Cabildo", "Calera", "Calle Larga", "Cartagena", "Casablanca", "Catemu", "Concón", "El Quisco", "El Tabo", "Hijuelas", "Isla de Pascua", "Juan Fernández", "La Cruz", "La Ligua", "Limache", "Llaillay", "Los Andes", "Nogales", "Olmué", "Panquehue", "Papudo", "Petorca", "Puchuncaví", "Putaendo", "Quillota", "Quilpué", "Quintero", "Rinconada", "San Antonio", "San Esteban", "San Felipe", "Santa María", "Santo Domingo", "Valparaíso", "Villa Alemana", "Viña del Mar", "Zapallar"]
    },
        {
            "NombreRegion": "Región del Libertador Gral. Bernardo O’Higgins",
            "comunas": ["Chimbarongo", "Chépica", "Codegua", "Coinco", "Coltauco", "Doñihue", "Graneros", "La Estrella", "Las Cabras", "Litueche", "Lolol", "Machalí", "Malloa", "Marchihue", "Mostazal", "Nancagua", "Navidad", "Olivar", "Palmilla", "Paredones", "Peralillo", "Peumo", "Pichidegua", "Pichilemu", "Placilla", "Pumanque", "Quinta de Tilcoco", "Rancagua", "Rengo", "Requínoa", "San Fernando", "San Vicente", "Santa Cruz"]
    },
        {
            "NombreRegion": "Región del Maule",
            "comunas": ["Cauquenes", "Chanco", "Colbún", "ConsVtución", "Curepto", "Curicó", "Empedrado", "Hualañé", "Licantén", "Linares", "Longaví", "Maule", "Molina", "Parral", "Pelarco", "Pelluhue", "Pencahue", "Rauco", "ReVro", "Romeral", "Río Claro", "Sagrada Familia", "San Clemente", "San Javier", "San Rafael", "Talca", "Teno", "Vichuquén", "Villa Alegre", "Yerbas Buenas"]
    },
        {
            "NombreRegion": "Región del Biobío",
            "comunas": ["Alto Biobío", "Antuco", "Arauco", "Bulnes", "Cabrero", "Cañete", "Chiguayante", "Chillán", "Chillán Viejo", "Cobquecura", "Coelemu", "Coihueco", "Concepción", "Contulmo", "Coronel", "Curanilahue", "El Carmen", "Florida", "Hualpén", "Hualqui", "Laja", "Lebu", "Los Álamos", "Los Ángeles", "Lota", "Mulchén", "Nacimiento", "Negrete", "Ninhue", "Pemuco", "Penco", "Pinto", "Portezuelo", "Quilaco", "Quilleco", "Quillón", "Quirihue", "Ránquil", "San Carlos", "San Fabián", "San Ignacio", "San Nicolás", "San Pedro de la Paz", "San Rosendo", "Santa Bárbara", "Santa Juana", "Talcahuano", "Tirúa", "Tomé", "Treguaco", "Tucapel", "Yumbel", "Yungay", "Ñiquén"]
    },
        {
            "NombreRegion": "Región de la Araucanía",
            "comunas": ["Angol", "Carahue", "Cholchol", "Collipulli", "Cunco", "Curacautín", "Curarrehue", "Ercilla", "Freire", "Galvarino", "Gorbea", "Lautaro", "Loncoche", "Lonquimay", "Los Sauces", "Lumaco", "Melipeuco", "Nueva Imperial", "Padre las Casas", "Perquenco", "Pitrufquén", "Pucón", "Purén", "Renaico", "Saavedra", "Temuco", "Teodoro Schmidt", "Toltén", "Traiguén", "Victoria", "Vilcún", "Villarrica"]
    },
        {
            "NombreRegion": "Región de Los Ríos",
            "comunas": ["Corral", "Futrono", "La Unión", "Lago Ranco", "Lanco", "Los Lagos", "Mariquina", "Máfil", "Paillaco", "Panguipulli", "Río Bueno", "Valdivia"]
    },
        {
            "NombreRegion": "Región de Los Lagos",
            "comunas": ["Ancud", "Calbuco", "Castro", "Chaitén", "Chonchi", "Cochamó", "Curaco de Vélez", "Dalcahue", "Fresia", "FruVllar", "Futaleufú", "Hualaihué", "Llanquihue", "Los Muermos", "Maullín", "Osorno", "Palena", "Puerto Montt", "Puerto Octay", "Puerto Varas", "Puqueldón", "Purranque", "Puyehue", "Queilén", "Quellón", "Quemchi", "Quinchao", "Río Negro", "San Juan de la Costa", "San Pablo"]
    },
        {
            "NombreRegion": "Región Aisén del Gral. Carlos Ibáñez del Campo",
            "comunas": ["Aisén", "Chile Chico", "Cisnes", "Cochrane", "Coyhaique", "Guaitecas", "Lago Verde", "O’Higgins", "Río Ibáñez", "Tortel"]
    },
        {
            "NombreRegion": "Región de Magallanes y de la Antártica Chilena",
            "comunas": ["Antártica", "Cabo de Hornos (Ex Navarino)", "Laguna Blanca", "Natales", "Porvenir", "Primavera", "Punta Arenas", "Río Verde", "San Gregorio", "Timaukel", "Torres del Paine"]
    },
        {
            "NombreRegion": "Región Metropolitana de Santiago",
            "comunas": ["Alhué", "Buin", "Calera de Tango" ,"Cerrillos", "Cerro Navia", "Colina", "Conchalí", "Curacaví", "El Bosque", "El Monte", "Estación Central", "Huechuraba", "Independencia", "Isla de Maipo","La Cisterna", "La Florida", "La Granja", "La Pintana", "La Reina", "Lampa","Las Condes", "Lo Barnechea", "Lo Espejo", "Lo Prado", "Macul", "Maipú", "María Pinto","Ñuñoa", "Padre Hurtado", "Paine","Pedro Aguirre Cerda", "Peñaflor", "Peñalolén","Pirque", "Providencia", "Pudahuel", "Puente Alto","Quilicura", "Quinta Normal", "Recoleta", "Renca", "San Bernardo","San Joaquín", "San José de Maipo","San Miguel", "San Ramón", "Santiago", "Talagante", "Tiltil","Vitacura"]
    }]
}

    var iRegion = 0;
    var htmlRegion = '<option value="sin-region">Región</option><option value="sin-region">--</option>';
    var htmlComunas = '<option value="sin-region">Comuna</option><option value="sin-region">--</option>';

    jQuery.each(RegionesYcomunas.regiones, function () {
        htmlRegion = htmlRegion + '<option value="' + RegionesYcomunas.regiones[iRegion].NombreRegion + '">' + RegionesYcomunas.regiones[iRegion].NombreRegion + '</option>';
        iRegion++;
    });

    jQuery('#et_pb_contact_region_1, #gform_1 #input_1_2, #gform_3 #input_3_6').html(htmlRegion);
    jQuery('#et_pb_contact_comuna_1, #gform_1 #input_1_4, #gform_3 #input_3_7').html(htmlComunas);

    jQuery('#et_pb_contact_region_1, #gform_1 #input_1_2, #gform_3 #input_3_6').change(function () {
        var iRegiones = 0;
        var valorRegion = jQuery(this).val();
        var htmlComuna = '<option value="sin-comuna">Seleccione comuna</option><option value="sin-comuna">--</option>';
        jQuery.each(RegionesYcomunas.regiones, function () {
            if (RegionesYcomunas.regiones[iRegiones].NombreRegion == valorRegion) {
                var iComunas = 0;
                jQuery.each(RegionesYcomunas.regiones[iRegiones].comunas, function () {
                    htmlComuna = htmlComuna + '<option value="' + RegionesYcomunas.regiones[iRegiones].comunas[iComunas] + '">' + RegionesYcomunas.regiones[iRegiones].comunas[iComunas] + '</option>';
                    iComunas++;
                });
            }
            iRegiones++;
        });
        jQuery('#et_pb_contact_comuna_1, #gform_1 #input_1_4, #gform_3 #input_3_7').html(htmlComuna);
    });
    jQuery('#et_pb_contact_comuna_1, #gform_1 #input_1_4, #gform_3 #input_3_7').change(function () {
        if (jQuery(this).val() == 'sin-region') {
            alert('selecciones Región');
        } else if (jQuery(this).val() == 'sin-comuna') {
            alert('selecciones Comuna');
        }
    });
    jQuery('#et_pb_contact_region_1, #gform_1 #input_1_2, #gform_3 #input_3_6').change(function () {
        if (jQuery(this).val() == 'sin-region') {
            alert('selecciones Región');
        }
    });

});
/************************************************************************************************/

jQuery(function($){
$( "a.woocommerce-terms-and-conditions-link" ).unbind( "click" );
$( "body" ).on('click', 'a.woocommerce-terms-and-conditions-link', function( event ) {
	
	 $(this).attr("target", "_blank");
    window.open( $(this).attr("href"));

    return false;
});

});