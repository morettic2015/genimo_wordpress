<?php
include_once('../wp-config.php');
//Database configuration
DB::$user = DB_USER;
DB::$password = DB_PASSWORD;
DB::$dbName = DB_NAME;
DB::$host = DB_HOST;
DB::$port = '3306';
DB::$error_handler = 'my_error_handler';
/**
    *   @Class for integrate GENIMO ERP With Wordpress Wp-casa;
    *   @Copyright @morettic.com.br
*/
class GenimoWordpress extends stdClass {
    /**
     *   @ Recupera todos os eventos apresentados hoje com todas as categorias
     */
    public static function syncProperty($idCompany,$idProperty) {
        DB::debugMode();
       // DB::startTransaction();
        echo "<pre>";
        echo "DAO INIT"."<br>";
        //Read from url
        $urlProperty = "https://genimo.com.br/api/site/property/".$idCompany."/".$idProperty;
         //Get JSON
        $json = file_get_contents($urlProperty);
        //echo  $json;

    //
        $obj = json_decode($json);//var_dump($obj);die();
        //Id property
        $idProperty = $obj->property->idProperty;
        //Recupera meta key com o IdProperty
        $metaKeyIdProperty = DB::queryOneColumn('meta_value', "select * from wp_postmeta where meta_key = '_listing_id' and meta_value = '".$idProperty."'");
        //Post title
        $postTitle = $obj->property->nmCategory.", ".$obj->property->nmNeighborhood;
        //Address
        $dsAddress = GenimoWordpress::getAddress($obj->property);
		$dsAddressMap = GenimoWordpress::getMapAddress($dsAddress);
		//Use of listing
        $useOf = GenimoWordpress::getPropertyFinallity($obj->property);
        ////baker
        $dtBakerStyle = "a:1:{s:5:\"style\";s:1:\"0\";}";
        //Se idProperty for = -1 não existe a metaKey
        $idPropertyDB = empty($metaKeyIdProperty)?$metaKeyIdProperty:0;
        //Get Guid URL
        $guid = GenimoWordpress::cleanUrl($idPropertyDB);
        //Listing not found
        if($idPropertyDB>0){
            echo "INSERT<br>";
            //Get Date
            $date = date("Y-m-d H:i:s");
            //Não existe o post nem a meta key
            DB::insert( 'wp_posts', array(
                        'post_author' => 1,                                             //default for all
                        'post_date' => $date,                                           //Just now its new
                        'post_date_gmt' => $date,                                       //just now its new
                        'post_content' => utf8_decode($obj->property->nmPropertySite),  //Get as String UTF 8
                        'post_title' => utf8_decode($postTitle),                        //Get as String UTF 8
                        'post_name' => makeSlug(utf8_decode($postTitle).$date),         //Get as String UTF 8
                        'post_excerpt' => utf8_decode($obj->property->nmPropertySite),  //Default Empty
                        'post_status' => 'publish',                                     //Publish online / Trash offline
                        'comment_status' => 'closed',                                   //Comment closed for all default
                        'ping_status' => 'closed',                                      //Ping status closed default for all
                        'post_password' => '',                                          //Post password empty 
                        'to_ping' => '',                                                //No need for it
                        'pinged' => '',                                                 //No need for it
                        'post_modified' => $date,                                       //Just now
                        'post_modified_gmt' => $date,                                   //Just now
                        'post_content_filtered' => '',                                  //No need for 
                        'post_parent' => 0,                                             //No need for parent
                        'guid' => $guid,                                                //Guid Url for Property
                        'menu_order' => '0',                                            //Default no need
                        'post_type' => 'listing',                                       //Post type listing for all property
                        'post_mime_type' => '',                                         //Default no need
                        'comment_count' => '0'                                          //Default no need
            ));
            //type of business
            $typeOfBusiness = GenimoWordpress::getTypeOfBusiness($obj->property);
            //Init metadata array for inserts
            $metadata = array();
            //Get new Property Key from database
            $idPropertyDB = DB::insertId();

            //Update Terms
            GenimoWordpress::updateALLPropertyTaxionomy($obj->property,$idPropertyDB);
   
            //prepareMeta post meta id for future update
            $metadata[] = GenimoWordpress::prepareMeta('_listing_id',$idProperty,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_not_available',0,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_sticky',0,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_featured',0,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_gallery_imported',1,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_edit_last',0,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_title',$postTitle,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('dtbaker_style',$dtBakerStyle,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_price_offer',$typeOfBusiness->tpPriceOffer,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_price_period',$typeOfBusiness->tpPricePeriod,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_price',$typeOfBusiness->vlPrice,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_map_address',$dsAddressMap,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_lat',$obj->property->vlLatitude,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_long',$obj->property->vlLongitude,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_formatted_address',$dsAddress,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_street',$obj->property->dsAddress,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_state_short',$obj->property->sgState,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_state_long',$obj->property->nmState,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_country_short','BR',$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_country_long','BRASIL',$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocated','1',$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_1',$obj->property->amSuite+$obj->property->amRooms,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_2',$obj->property->amBathroom,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_3',$obj->property->vlPropertyTotalArea,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_4',$obj->property->vlAreaM2C,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_5',$obj->property->vlGroundArea,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_6',$obj->property->amGarage,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_7',$useOf,$idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_8',$obj->property->qtYearBuilt,$idPropertyDB);
            //Insert post id meta
            //Get type of business and values for rent and sale
            //$typeOfBusiness = getTypeOfBusiness($obj->property);
            DB::insert('wp_postmeta', $metadata);
            
            

           // DB::commit();

        }else{
             echo "UPDATE<br>";
            //Post e meta key existem
        }

        //var_dump($types);
     
        DB::disconnect();
        return null;
    }
    /**
        @ update all property taxionomy
        //insertTaxionomy($slug,$name,$idProperty,$taxionomy,$desc)
    */
    public static function updateALLPropertyTaxionomy($property,$idPropertyDB){
        //echo "Taxionomies";
        //var_dump($property);die();
        if ($property->flAcceptFunding==1) {
            GenimoWordpress::insertTaxionomy("flAcceptFunding","Aceita financiamento",$idPropertyDB,'feature',utf8_decode("O imóvel aceita financiamento"));
        }
        if ($property->tpFurnished==3) {
            GenimoWordpress::insertTaxionomy("tpFurnished","Mobiliado",$idPropertyDB,'feature',utf8_decode("O imóvel está mobiliado"));
        }
        if ($property->flHighStandart==1) {
            GenimoWordpress::insertTaxionomy("flHighStandart",utf8_decode("Alto padrão"),$idPropertyDB,'feature',utf8_decode("Imóvel de alto padrão"));
        }
        if ($property->flFacingSea==1) {
            GenimoWordpress::insertTaxionomy("flFacingSea","Frente para o mar",$idPropertyDB,'feature',utf8_decode("Localizado de frente para o mar"));
        }
        if ($property->flBarbecueGrill==1) {
            GenimoWordpress::insertTaxionomy("flBarbecueGrill","Churrasqueira",$idPropertyDB,'feature',utf8_decode("O imóvel possui churrasqueira na sacada"));
        }
        if ($property->flFireplace==1) {
            GenimoWordpress::insertTaxionomy("flFireplace","Lareira",$idPropertyDB,'feature',utf8_decode("O imóvel possui lareira"));
        }
        if ($property->flBalcony==1) {
            GenimoWordpress::insertTaxionomy("flBalcony","Sacada",$idPropertyDB,'feature',utf8_decode("O imóvel possui sacada"));
        }
        if ($property->flHeatedPool==1) {
            GenimoWordpress::insertTaxionomy("flHeatedPool","Piscina aquecida",$idPropertyDB,'feature',utf8_decode("O imóvel possui piscina aquecida"));
        }
        if ($property->flGym==1) {
            GenimoWordpress::insertTaxionomy("flGym","Academia",$idPropertyDB,'feature',utf8_decode("O imóvel possui academia"));
        }
        if ($property->flAccessChallengedPeople==1) {
            GenimoWordpress::insertTaxionomy("flAccessChallengedPeople","Acessibilidade",$idPropertyDB,'feature',"O condominio possui acesso para portadores de necessidades especiais");
        }
        if ($property->flCollectiveBarbecue==1) {
            GenimoWordpress::insertTaxionomy("flCollectiveBarbecue","Churrasqueira coletiva",$idPropertyDB,'feature',"O imóvel possui churrasqueira coletiva");
        }
        if ($property->flElevator==1) {
            GenimoWordpress::insertTaxionomy("flElevator","Elevador",$idPropertyDB,'feature',"O imóvel possui elevador");
        }
        if ($property->flPlayground==1) {
            GenimoWordpress::insertTaxionomy("flPlayground","Playground",$idPropertyDB,'feature',"O imóvel possui playground");
        }
        if ($property->flPool==1) {
            GenimoWordpress::insertTaxionomy("flPool","Piscina",$idPropertyDB,'feature',"O imóvel tem piscina");
        }
        if ($property->flOrdinance==1) {
            GenimoWordpress::insertTaxionomy("flOrdinance","Portaria 24 horas",$idPropertyDB,'feature',"O imóvel tem portaria 24 horas");
        }
        if ($property->flSportsCourt==1) {
            GenimoWordpress::insertTaxionomy("flSportsCourt","Quadra de esportes",$idPropertyDB,'feature',"O imóvel possui quadra de esportes");
        }
        if ($property->flLoungeParties==1) {
            GenimoWordpress::insertTaxionomy("flLoungeParties","Salão de festas",$idPropertyDB,'feature',"O imóvel tem salão de festas");
        }
        if ($property->tpSpotlight>0) {
            GenimoWordpress::insertTaxionomy("tpSpotlight","Oportunidade do momento",$idPropertyDB,'feature',"Oportunidade do momento");
        }
				
    }

    /**
    *   @ Insert Taxionomy
    */
    public static function insertTaxionomy($slug,$name,$idProperty,$taxionomy,$desc){
        //echo "TXBS";
        $term_id = DB::queryOneField('term_id', "select term_id from wp_terms where slug=%s", $slug);

        //echo $term_id;die();

        $term_tax_id;
        //Term is empty insert it    
        if(empty($term_id)){
            //echo "\nNEW TERM";
            //insert term
            DB::insert('wp_terms', array(
                'term_id' => null, //primary key
                'name' => $name,
                'slug' => $slug,
                'term_group'=> '0'
                ));
            //id term created
            $term_id = DB::insertId();
           // echo "\nTERM ID".$term_id;
            //term taxionomy
            DB::insert('wp_term_taxonomy', array(
                'term_taxonomy_id' => null, //primary key
                'term_id' => $term_id,
                'taxonomy' => $taxionomy,
                'description'=> $desc,
                'parent' => '0',
                'count' => '0'
                ));
            //id term created
            $term_tax_id = DB::insertId();
           // echo "\nTERM TAXIONOMY ID".$term_id;
        }
        //Retrieve taxionomy from term to associate with post
        if(empty($term_tax_id)){
            $term_tax_id = DB::queryOneField('term_taxonomy_id','SELECT term_taxonomy_id FROM imobiliaria_com.wp_term_taxonomy where term_id = '. $term_id);
        }
        //Query One Field to see if theres already a relationship
        $term_rel_id = DB::queryOneField(
            'object_id',
            'select * from wp_term_relationships where term_taxonomy_id = '.
            $term_tax_id.
            ' and object_id = '.
            $idProperty);
        //echo "\nQUERY DONE";
        //Vazio nao encontrou nenhum relacionamento
        if(empty($term_rel_id)){
             DB::insert('wp_term_relationships', array(
                'object_id' => $idProperty, //primary key
                'term_taxonomy_id' => $term_tax_id,
                'term_order' => '0'
                ));
            //id term created
            $term_id = DB::insertId();
            //echo "\nTERM TAXIONOMY ID".$term_id;
        }else{
           // return null;
        }
    }

    /**
        @Get Map locations
    */
    public static function getMapAddress($dsAddress){
        $dsAddressMap = str_replace(".", "", $dsAddress);
        $dsAddressMap = str_replace(",", "", $dsAddressMap);
        $dsAddressMap = str_replace("-", "", $dsAddressMap);
        return $dsAddressMap;
    }
    /**
        @Get address formated
    */
    public static function getAddress($property){
        return $property->nmNeighborhood.", ".$property->nmCity." - ".$property->sgState;
    }
    /**
        @prepare post insert
    */
    public static function prepareMeta($metaKey,$metaValue,$idPost){
        return array(
            'post_id' => $idPost,
            'meta_key' => $metaKey,
            'meta_value' => $metaValue
        );
    }

    /**
    *   Get featured image path
    */
    public static function getPropertyFeaturedImage($property){
        $dsPath = null;
        if (strlen($property->nmFileNameSpotlight)>0) {
			$dsPath = $dsUrlBaseGenimo."/media/".$property->idProperty."/".$property->nmFileNameSpotlight;
		} else {
			$dsPath = $dsUrlBaseGenimo."/img/casaAzul.png";
		}
        return $dsPath;
    }

    /*
    *   Get Property Finallity
    */
    public static function getPropertyFinallity($property){
        $dsFinality = ($property->tpFinality==1)?"Comercial":($property->tpFinality==2)?"Residencial":"Residencial ou Comercial";
        return $dsFinality;
    }
    /*
    *   Get Floor type
    */
    public static function getPropertyFloor($property){
        $dsFloor = ($property->tpFloor==1)?"Madeira":($property->tpFloor==2)?"Ceramica":($property->tpFloor==3)?"Vinílico":($property->tpFloor==4)?"Laminado":($property->tpFloor==5)?"Carpete":"";
        return $dsFloor;
    }


    /**
    *   remove all non alpha numeric chars and spaces from urls
    */
    public static function cleanUrl($id){
       // var_dump($id);die;
        return "?post_type=listing&#038;p=";
    }
    /*
    * Get Type of Business [rent,sale,rent1,sale1]
    */
    public static function getTypeOfBusiness($property){
        $typeOfBusiness = new stdClass();
        if ($property->cdMode==1) {
            $typeOfBusiness->vlPrice 		= $property->vlRental;
            $typeOfBusiness->tpPriceOffer 	= "rent";
            $typeOfBusiness->tpPricePeriod 	= "rental_period_1";
        } else if ($property->cdMode==2) {
            $typeOfBusiness->vlPrice 		= $property->vlSale;
            $typeOfBusiness->tpPriceOffer 	= "sale";
            $typeOfBusiness->tpPricePeriod 	= "sale";
        } else if ($property->cdMode==4) {
            $typeOfBusiness->vlPrice 		= $property->vlSeasonRent;
            $typeOfBusiness->tpPriceOffer 	= "rent";
            $typeOfBusiness->tpPricePeriod 	= "rental_period_2";
        } else if ($property->cdMode==5) {
            $typeOfBusiness->typeOfBusiness->vlPrice 		= $property->vlLowSeasonRent;
            $typeOfBusiness->tpPriceOffer 	= "rent";
            $typeOfBusiness->tpPricePeriod 	= "rental_period_3";
        } else {
            $typeOfBusiness->vlPrice 		= "";
            $typeOfBusiness->tpPriceOffer 	= "sale";
            $typeOfBusiness->tpPricePeriod 	= "sale";
        }

        //var_dump($typeOfBusiness);

        return $typeOfBusiness;
    }

}

/* * ****
 *    Database Handlers
 */

function my_error_handler($params) {
    echo "Error: " . $params['error'] . "<br>\n";
    echo "Query: " . $params['query'] . "<br>\n";
    die; // don't want to keep going if a query broke
}



/**
    Manual teste to be run after all

-- SELECT * FROM imobiliaria_com.wp_postmeta where meta_key = '_details_1'

-- select max(ID) from wp_posts

-- delete from wp_posts where ID =318687;

-- select * from wp_posts where ID = '318689'

-- select * from wp_postmeta where post_id = 318689;

-- delete from wp_postmeta where post_id = 318689;

-- delete from wp_posts where post_type = 'listing'

-- delete from wp_postmeta where meta_key = 'idProperty';

*/


function makeSlug( $string )
{
    $string = iconv( "utf-8", "us-ascii//translit//ignore", $string ); // transliterate
    $string = str_replace( "'", "", $string );
    $string = preg_replace( "~[^\pL\d]+~u", "-", $string ); // replace non letter or non digits by "-"
    $string = preg_replace( "~[^-\w]+~", "", $string ); // remove unwanted characters
    $string = preg_replace( "~-+~", "-", $string ); // remove duplicate "-"
    $string = trim( $string, "-" ); // trim "-"
    $string = trim( $string ); // trim
    $string = mb_strtolower( $string, "utf-8" ); // lowercase
    $string = urlencode( $string ); // safe
    return $string;
}