<?php
/*
Template Name: Obituarios
*/
get_header();

$is_page_builder_used = et_pb_is_pagebuilder_used( get_the_ID() ); ?>

<style>

/* ==== GRID SYSTEM ==== */

.contain {
  width: 100%;
  margin-left: auto;
  margin-right: auto;
}

.row {
  position: relative;
  width: 100%;
}

.row [class^="col"] {
  float: left;
  margin: 0.5rem 2%;
  min-height: 0.125rem;
}

.col-1,
.col-2,
.col-3,
.col-4,
.col-5,
.col-6,
.col-7,
.col-8,
.col-9,
.col-10,
.col-11,
.col-12 {
  width: 96%;
}

.col-1-sm {
  width: 4.33%;
}

.col-2-sm {
  width: 12.66%;
}

.col-3-sm {
  width: 21%;
}

.col-4-sm {
  width: 29.33%;
}

.col-5-sm {
  width: 37.66%;
}

.col-6-sm {
  width: 46%;
}

.col-7-sm {
  width: 54.33%;
}

.col-8-sm {
  width: 62.66%;
}

.col-9-sm {
  width: 71%;
}

.col-10-sm {
  width: 79.33%;
}

.col-11-sm {
  width: 87.66%;
}

.col-12-sm {
  width: 96%;
}

.row::after {
	content: "";
	display: table;
	clear: both;
}

.hidden-sm {
  display: none;
}

@media only screen and (min-width: 33.75em) {  /* 540px */
  .contain {
    width: 80%;
  }
}

@media only screen and (min-width: 45em) {  /* 720px */
  .col-1 {
    width: 4.33%;
  }

  .col-2 {
    width: 12.66%;
  }

  .col-3 {
    width: 21%;
  }

  .col-4 {
    width: 29.33%;
  }

  .col-5 {
    width: 37.66%;
  }

  .col-6 {
    width: 46%;
  }

  .col-7 {
    width: 54.33%;
  }

  .col-8 {
    width: 62.66%;
  }

  .col-9 {
    width: 71%;
  }

  .col-10 {
    width: 79.33%;
  }

  .col-11 {
    width: 87.66%;
  }

  .col-12 {
    width: 96%;
  }

  .hidden-sm {
    display: block;
  }
}

@media only screen and (min-width: 60em) { /* 960px */
  .contain {
    width: 80%;
  }
}
</style>

<div id="main-content">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

					<div class="entry-content">
					<?php
						the_content();
                        ?>

						
                        <div class="contain">
                            <div class="row">
                            
        <!-- ******************************************************************************************** -->
<?php

        $wsdlUrl = 'http://200.68.17.42:3362/BdCliente.asmx?wsdl';
        $client = new SoapClient($wsdlUrl, array("trace"=>1, 'cache_wsdl' => WSDL_CACHE_NONE, 'use' => SOAP_LITERAL, 'soap_version'=>SOAP_1_1));
        $auth = new stdClass();
        $auth->Usuario = 'RmhjMjAxN05ld0xhYg==';
        $auth->Password = 'TmV3TGFiRmhjMjAxNy4s';
        $header = new SoapHeader('http://tempuri.org/','SecuredToken',$auth,false);
        $client->__setSoapHeaders($header);

        $token = ($client->AuthenticationUser());
        $token = $token->AuthenticationUserResult;
        $auth->AuthenticationToken = $token;

        $header = new SoapHeader('http://tempuri.org/','SecuredToken',$auth,false);
        $client->__setSoapHeaders($header);
        $obituario = ($client->Obituario());
        
        $pattern = '/<xs:schema.*<\/xs:schema>/';
        $xml = preg_replace($pattern, '', $obituario->ObituarioResult->any);

        $response = simplexml_load_string($xml);

        function sortFunction( $a, $b ) {
            return strtotime($a["FECHAOBITUARIO"]) - strtotime($b["FECHAOBITUARIO"]);
        }
        class SecuredToken{
            var $Usuario = '';
            var $Password = '';
            var $AuthenticationToken = '';
            public function __construct($Usuario,$Password, $AuthenticationToken){
                $this->Usuario = $Usuario;
                $this->Password = $Password;
                $this->AuthenticationToken = $AuthenticationToken;
            }
        }
        $responseArray = $response->NewDataSet->Obituario;
        $json = json_encode($response->NewDataSet);
        $array = json_decode($json,TRUE);

        $def = array();

        foreach($array as $date){
            
            $cleaned_list = array_unique($date, SORT_REGULAR);
            
            foreach($cleaned_list as $row){
                
                $def[strtotime($row['FECHAOBITUARIO'])][] = $row;
                
            }
        }
?>
          
<?php foreach( $def as $tmp):  ?>

<?php 
date_default_timezone_set('America/Santiago');
$date1=date('d-m-Y');
$date2=date('d-m-Y', strtotime("-1 days"));
$date3=date('d-m-Y', strtotime("-2 days"));

$fecha_obi = strtotime($tmp[0]['FECHAOBITUARIO']);
$obi_date = date('d-m-Y', $fecha_obi);
                                
$obi_day = date('j \d\e F \d\e\l Y', $fecha_obi);

$fecha_funeral = strtotime($tmp[0]['FECHAFUNERAL']);
$funeral_date = date('d-m-Y', $fecha_funeral);

if( $obi_date == $date1 || $obi_date == $date2 || $obi_date == $date3 ) :?>

<div class="col-12 obi-container-day">
    
    <div class="obi-day"><h3><?php echo $obi_day; ?></h3></div>
    
    <?php 
    $counter = 1;
    foreach($tmp as $obt) : 
    if ($counter%2 == 1){ echo '<div class="row">';}
    ?>
    
    <div class="col-12-sm col-6 obi-block">
                
        <h4><strong><?php echo $obt['NOMBRE'];?></strong></h4>
        
        <?php if( isset($obt['NOMBRE']) ): ?>
            <p><strong>Lugar Velatorio:</strong> <?php echo $obt['NOMBRE'];?></p>
        <?php endif; ?>
                                
        <?php if( isset($obt['DIRECCIONVELATORIO']) ): ?>
            <p><strong>Dirección Velatorio:</strong> <?php echo $obt['DIRECCIONVELATORIO']; ?></p>
        <?php endif; ?>

        <?php if( isset($obt['FECHAFUNERAL']) ): ?>
            <p><strong>Fecha Funeral:</strong> <?php echo $funeral_date; ?></p>
        <?php endif; ?>

        <?php if( isset($obt['HORAFUNERAL']) ): ?>
            <p><strong>Hora Funeral:</strong> <?php echo $obt['HORAFUNERAL']; ?></p>
        <?php endif; ?>

        <?php if( isset($obt['LUGARFUNERAL']) ): ?>
            <p><strong>Lugar Funeral:</strong> <?php echo $obt['LUGARFUNERAL']; ?></p>
        <?php endif; ?>

        <?php if( isset($obt['DIRECCIONFUNERAL']) ): ?>
            <p><strong>Dirección Funeral:</strong> <?php echo $obt['DIRECCIONFUNERAL']; ?></p>
        <?php endif; ?>
            
    </div>	
    
    <?php 
    if ($counter%2 == 0 || end($tmp) === $obt ) { echo '</div>'; } $counter++; 
    endforeach; 
    ?>
                         
</div>
<?php endif;?>

<?php endforeach; ?>
<!-- ******************************************************************************************** -->
                            
                            </div>
                        </div>
                        
                        
					</div> <!-- .entry-content -->

			

				</article> <!-- .et_pb_post -->

			<?php endwhile; ?>



</div> <!-- #main-content -->

<?php

get_footer();

