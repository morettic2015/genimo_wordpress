<?php

/**
 *   @Class for integrate GENIMO ERP With Wordpress Wp-casa;
 *   @Copyright @morettic.com.br
 * 
 * delete FROM imobiliaria_com.wp_postmeta where meta_key in ('_listing_id','_listing_not_available','_listing_sticky','_listing_featured','_map_address','_price','_price_period'
  ,'_listing_title','_gallery_imported','_listing_featured'
  ,'_price_offer','_geolocation_lat','_geolocation_long','_geolocation_formatted_address','_geolocation_street','_geolocation_state_short','_geolocation_state_long',
  '_geolocation_country_short','_geolocation_country_long','_geolocated','_details_1','_details_2','_details_3','_details_4','_details_5','_details_6','_details_7','_details_8'
  );
 * 
 * 
 */
class GenimoWordpress extends stdClass {

    /**
     *   @ Recupera todos os eventos apresentados hoje com todas as categorias
     */
    public static function syncProperty($idCompany, $idProperty) {
        DB::debugMode();
        // DB::startTransaction();
        echo "<pre>";
        echo "DAO INIT" . "<br>";
        //Read from url
        $urlProperty = "https://genimo.com.br/api/site/property/" . $idCompany . "/" . $idProperty;
        //Get JSON
        $json = file_get_contents($urlProperty);
        //echo  $json;
        //
        $obj = json_decode($json); //var_dump($obj);die();
        //dum memnory
        //var_dump($obj);
        //die;
        //Id property
        $idProperty = $obj->property->idProperty;
        //Recupera meta key com o IdProperty
        $metaKeyIdProperty = DB::queryOneColumn('post_id', "select * from wp_postmeta where meta_key = '_listing_id' and meta_value = '" . $idProperty . "'");
        print_r($metaKeyIdProperty);

        //Post title
        $postTitle = $obj->property->nmCategory . ", " . $obj->property->nmNeighborhood;
        //Address
        $dsAddress = GenimoWordpress::getAddress($obj->property);
        $dsAddressMap = GenimoWordpress::getMapAddress($dsAddress);
        //Use of listing
        $useOf = GenimoWordpress::getPropertyFinallity($obj->property);
        ////baker
        $dtBakerStyle = "a:1:{s:5:\"style\";s:1:\"0\";}";
        //Se idProperty for = -1 não existe a metaKey
        $idPropertyDB = empty($metaKeyIdProperty) ? $metaKeyIdProperty : 0;
        $obj->idPropertyDB = $idPropertyDB;
        //Get Guid URL
        $guid = GenimoWordpress::cleanUrl($idPropertyDB);
        //Listing not found
        if ($idPropertyDB > 0) {
            echo "INSERT<br>";
            //Get Date
            $date = date("Y-m-d H:i:s");
            //Não existe o post nem a meta key
            DB::insert('wp_posts', array(
                'post_author' => 1, //default for all
                'post_date' => $date, //Just now its new
                'post_date_gmt' => $date, //just now its new
                'post_content' => utf8_decode($obj->property->nmPropertySite), //Get as String UTF 8
                'post_title' => utf8_decode($postTitle), //Get as String UTF 8
                'post_name' => makeSlug($postTitle . '_' . $idProperty), //Get as String UTF 8
                'post_excerpt' => utf8_decode($obj->property->nmPropertySite), //Default Empty
                'post_status' => 'publish', //Publish online / Trash offline
                'comment_status' => 'closed', //Comment closed for all default
                'ping_status' => 'closed', //Ping status closed default for all
                'post_password' => '', //Post password empty 
                'to_ping' => '', //No need for it
                'pinged' => '', //No need for it
                'post_modified' => $date, //Just now
                'post_modified_gmt' => $date, //Just now
                'post_content_filtered' => '', //No need for 
                'post_parent' => 0, //No need for parent
                'guid' => $guid, //Guid Url for Property
                'menu_order' => '0', //Default no need
                'post_type' => 'listing', //Post type listing for all property
                'post_mime_type' => '', //Default no need
                'comment_count' => '0'                                          //Default no need
            ));
            //type of business
            $typeOfBusiness = GenimoWordpress::getTypeOfBusiness($obj->property);
            //Init metadata array for inserts
            $metadata = array();
            //Get new Property Key from database
            $idPropertyDB = DB::insertId();

            //Update Terms
            GenimoWordpress::updateALLPropertyTaxionomy($obj->property, $idPropertyDB);

            //prepareMeta post meta id for future update
            $metadata[] = GenimoWordpress::prepareMeta('_listing_id', $idProperty, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_not_available', 0, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_sticky', 0, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_featured', 0, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_gallery_imported', 1, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_edit_last', 0, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_listing_title', $postTitle, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('dtbaker_style', $dtBakerStyle, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_price_offer', $typeOfBusiness->tpPriceOffer, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_price_period', $typeOfBusiness->tpPricePeriod, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_price', $typeOfBusiness->vlPrice, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_map_address', $dsAddressMap, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_lat', $obj->property->vlLatitude, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_long', $obj->property->vlLongitude, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_formatted_address', $dsAddress, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_street', $obj->property->dsAddress, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_state_short', $obj->property->sgState, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_state_long', $obj->property->nmState, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_country_short', 'BR', $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_country_long', 'BRASIL', $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocated', '1', $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_1', $obj->property->amSuite + $obj->property->amRooms, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_2', $obj->property->amBathroom, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_3', $obj->property->vlPropertyTotalArea, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_4', $obj->property->vlAreaM2C, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_5', $obj->property->vlGroundArea, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_6', $obj->property->amGarage, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_7', $useOf, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_details_8', $obj->property->qtYearBuilt, $idPropertyDB);
            //Insert post id meta
            //Get type of business and values for rent and sale
            //$typeOfBusiness = getTypeOfBusiness($obj->property);
            DB::insert('wp_postmeta', $metadata);
            //Insert Listing Location
            $tax = utf8_decode($obj->property->nmNeighborhood . "," . $obj->property->nmCity);
            $taxSlug = makeSlug($tax);

            echo "\n" . $taxSlug . "\n";
            GenimoWordpress::insertTaxionomy($taxSlug, $tax, $idPropertyDB, 'location', $tax);

            //new ID
            $obj->idPropertyDB = $idPropertyDB;

            // DB::commit();
        } else {
            echo "UPDATE<br>";
            //Old ID
            $obj->idPropertyDB = $metaKeyIdProperty[0];

            echo $obj->idPropertyDB;
            //Post e meta key existem
        }

        //var_dump($types);

        DB::disconnect();
        //Internal property id on Wordpress

        return $obj;
    }

    /**
      @ update all property taxionomy
      //insertTaxionomy($slug,$name,$idProperty,$taxionomy,$desc)
     */
    public static function updateALLPropertyTaxionomy($property, $idPropertyDB) {
        //echo "Taxionomies";
        //var_dump($property);die();
        if ($property->flAcceptFunding == 1) {
            GenimoWordpress::insertTaxionomy("flAcceptFunding", "Aceita financiamento", $idPropertyDB, 'feature', utf8_decode("O imóvel aceita financiamento"));
        }
        if ($property->tpFurnished == 3) {
            GenimoWordpress::insertTaxionomy("tpFurnished", "Mobiliado", $idPropertyDB, 'feature', utf8_decode("O imóvel está mobiliado"));
        }
        if ($property->flHighStandart == 1) {
            GenimoWordpress::insertTaxionomy("flHighStandart", utf8_decode("Alto padrão"), $idPropertyDB, 'feature', utf8_decode("Imóvel de alto padrão"));
        }
        if ($property->flFacingSea == 1) {
            GenimoWordpress::insertTaxionomy("flFacingSea", "Frente para o mar", $idPropertyDB, 'feature', utf8_decode("Localizado de frente para o mar"));
        }
        if ($property->flBarbecueGrill == 1) {
            GenimoWordpress::insertTaxionomy("flBarbecueGrill", "Churrasqueira", $idPropertyDB, 'feature', utf8_decode("O imóvel possui churrasqueira na sacada"));
        }
        if ($property->flFireplace == 1) {
            GenimoWordpress::insertTaxionomy("flFireplace", "Lareira", $idPropertyDB, 'feature', utf8_decode("O imóvel possui lareira"));
        }
        if ($property->flBalcony == 1) {
            GenimoWordpress::insertTaxionomy("flBalcony", "Sacada", $idPropertyDB, 'feature', utf8_decode("O imóvel possui sacada"));
        }
        if ($property->flHeatedPool == 1) {
            GenimoWordpress::insertTaxionomy("flHeatedPool", "Piscina aquecida", $idPropertyDB, 'feature', utf8_decode("O imóvel possui piscina aquecida"));
        }
        if ($property->flGym == 1) {
            GenimoWordpress::insertTaxionomy("flGym", "Academia", $idPropertyDB, 'feature', utf8_decode("O imóvel possui academia"));
        }
        if ($property->flAccessChallengedPeople == 1) {
            GenimoWordpress::insertTaxionomy("flAccessChallengedPeople", "Acessibilidade", $idPropertyDB, 'feature', "O condominio possui acesso para portadores de necessidades especiais");
        }
        if ($property->flCollectiveBarbecue == 1) {
            GenimoWordpress::insertTaxionomy("flCollectiveBarbecue", "Churrasqueira coletiva", $idPropertyDB, 'feature', "O imóvel possui churrasqueira coletiva");
        }
        if ($property->flElevator == 1) {
            GenimoWordpress::insertTaxionomy("flElevator", "Elevador", $idPropertyDB, 'feature', "O imóvel possui elevador");
        }
        if ($property->flPlayground == 1) {
            GenimoWordpress::insertTaxionomy("flPlayground", "Playground", $idPropertyDB, 'feature', "O imóvel possui playground");
        }
        if ($property->flPool == 1) {
            GenimoWordpress::insertTaxionomy("flPool", "Piscina", $idPropertyDB, 'feature', "O imóvel tem piscina");
        }
        if ($property->flOrdinance == 1) {
            GenimoWordpress::insertTaxionomy("flOrdinance", "Portaria 24 horas", $idPropertyDB, 'feature', "O imóvel tem portaria 24 horas");
        }
        if ($property->flSportsCourt == 1) {
            GenimoWordpress::insertTaxionomy("flSportsCourt", "Quadra de esportes", $idPropertyDB, 'feature', "O imóvel possui quadra de esportes");
        }
        if ($property->flLoungeParties == 1) {
            GenimoWordpress::insertTaxionomy("flLoungeParties", "Salão de festas", $idPropertyDB, 'feature', "O imóvel tem salão de festas");
        }
        if ($property->tpSpotlight > 0) {
            GenimoWordpress::insertTaxionomy("tpSpotlight", "Oportunidade do momento", $idPropertyDB, 'feature', "Oportunidade do momento");
        }

        /**
          Casa terreno etc
         *          */
        GenimoWordpress::insertCatCat($property->idCategory, $idPropertyDB);
    }

    /**
     * @ Category
     * categories: [
      {
      idCategory: "1",
      nmCategory: "Casa"
      },
      {
      idCategory: "2",
      nmCategory: "Apartamento"
      },
      {
      idCategory: "3",
      nmCategory: "Terreno"
      },
      {
      idCategory: "4",
      nmCategory: "Quitinete"
      },
      {
      idCategory: "5",
      nmCategory: "Sala Comercial"
      },
      {
      idCategory: "6",
      nmCategory: "Galpão"
      },
      {
      idCategory: "7",
      nmCategory: "Cobertura"
      }
      ]
     */
    public static function insertCatCat($id, $idPropertyDB) {
        echo "---$id----";
        echo "$idPropertyDB-----";
        $pid = intval($id);
        switch ($pid) {
            case 1:
                GenimoWordpress::insertTaxionomy("catCasa", "Casa", $idPropertyDB, 'listing-type', "Casa");
                echo "CASA";
                break;
            case 2:
                GenimoWordpress::insertTaxionomy("catApto", "Apartamento", $idPropertyDB, 'listing-type', "Apartamento");
                echo "APTO";
                break;
            case 3:
                GenimoWordpress::insertTaxionomy("catTerreno", "Terreno", $idPropertyDB, 'listing-type', "Terreno");
                echo "TERRENO";
                break;
            case 4:
                GenimoWordpress::insertTaxionomy("catQuitinete", "Quitinete", $idPropertyDB, 'listing-type', "Quitinete");
                echo "QUITINETE";
                break;
            case 5:
                GenimoWordpress::insertTaxionomy("catSalaComercial", "Sala Comercial", $idPropertyDB, 'listing-type', "Sala Comercial");
                echo "SALA COMERCIAL";
                break;
            case 6:
                GenimoWordpress::insertTaxionomy("catGalpao", "Galpão", $idPropertyDB, 'listing-type', "Galpão");
                echo "GAlpão";
                break;
            case 7:
                GenimoWordpress::insertTaxionomy("catCobertura", "Cobertura", $idPropertyDB, 'listing-type', "Cobertura");
                echo "Cobertura";
                break;
        }

        echo "\n";
    }

    /**
     *   @ Insert Taxionomy
     */
    public static function insertTaxionomy($slug, $name, $idProperty, $taxionomy, $desc) {
        //echo "TXBS";
        $term_id = DB::queryOneField('term_id', "select term_id from wp_terms where slug=%s", $slug);

        //echo $term_id; //die();

        $term_tax_id;
        //Term is empty insert it    
        if (empty($term_id)) {
            //echo "\nNEW TERM";
            //insert term
            DB::insert('wp_terms', array(
                'term_id' => null, //primary key
                'name' => $name,
                'slug' => $slug,
                'term_group' => '0'
            ));
            //id term created
            $term_id = DB::insertId();
            // echo "\nTERM ID".$term_id;
            //term taxionomy
            DB::insert('wp_term_taxonomy', array(
                'term_taxonomy_id' => null, //primary key
                'term_id' => $term_id,
                'taxonomy' => $taxionomy,
                'description' => $desc,
                'parent' => '0',
                'count' => '1'
            ));
            //id term created
            $term_tax_id = DB::insertId();
            // echo "\nTERM TAXIONOMY ID".$term_id;
        }
        //Retrieve taxionomy from term to associate with post
        if (!isset($term_tax_id)) {

            $term_tax_id = DB::queryOneField('term_taxonomy_id', 'SELECT term_taxonomy_id FROM imobiliaria_com.wp_term_taxonomy where term_id = ' . $term_id);
            //echo $term_tax_id;
        }
        //Query One Field to see if theres already a relationship
        $term_rel_id = DB::queryOneField(
                        'object_id', 'select * from wp_term_relationships where term_taxonomy_id = ' .
                        $term_tax_id .
                        ' and object_id = ' .
                        $idProperty);
        //echo "\nQUERY DONE";
        //Vazio nao encontrou nenhum relacionamento
        if (empty($term_rel_id)) {
            DB::insert('wp_term_relationships', array(
                'object_id' => $idProperty, //primary key
                'term_taxonomy_id' => $term_tax_id,
                'term_order' => '0'
            ));
            //id term created
            $term_id = DB::insertId();
            //echo "\nTERM TAXIONOMY ID".$term_id;
        } else {
            // return null;
        }
    }

    /**
      @Get Map locations
     */
    public static function getMapAddress($dsAddress) {
        $dsAddressMap = str_replace(".", "", $dsAddress);
        $dsAddressMap = str_replace(",", "", $dsAddressMap);
        $dsAddressMap = str_replace("-", "", $dsAddressMap);
        return $dsAddressMap;
    }

    /**
     * @ Copy Images
     * 1) Pega uma imagem
     * 2) Verifica se o nome da imagem existe no caminho local
     * 3) Se Existe fim
     * 4) Se nao existe
     * 5) Copia Imagem remota para local
     * 6) Insere post do tipo Attatchment com o post_parent = idProperty
     * 7) Se for featured image
     * 8) Insere o posta attachment sem parent
     * 9) Cria um post meta onde o meta_key = 'thunbnail'
     * 10) Vincula a imagem sem o parent com o post pelo thumbbail
     * 
     * 
     * SELECT * FROM imobiliaria_com.wp_postmeta where meta_key = '_thumbnail_id '
     * 
     * SELECT * FROM imobiliaria_com.wp_term_relationships where object_id = 318771;
     *
     * SELECT distinct meta_key FROM imobiliaria_com.wp_postmeta;
     *
     * delete FROM imobiliaria_com.wp_postmeta where meta_key = '_listing_id';
     *
     * select * from wp_term_taxonomy where term_taxonomy_id in (1123,1149)
     *
     *      
     */
    public static function copyImages($property) {
        DB::debugMode();
        //echo $property->idPropertyDB . "\n";

        $images = $property->property->images;
        //var_dump($images);

        $hasSpot = false;
        $imgIds = [];
        foreach ($images as $img) {

            $path = PATH_UPLOAD . date('Y') . '/' . date("m") . '/' . $img->nmFileName;
            //echo $path . "\n";
            //if (!file_exists($path)) {
            //verify if file exists if exists skip continue
            DB::query("SELECT ID FROM wp_posts where post_type = 'attachment' and guid like '%" . $img->nmFileName . "%'");
            $counter = DB::count();

            //echo "\n Image Occurences:" . $counter;

            if ($counter > 0) {
                ///echo "\n";
                //echo $img->nmFileName . ' Exists\n';
                continue;
            }

            $urlImg = $img->dsImagePath . "/" . $img->nmFileName;
            //echo $urlImg . "\n";

            $file = file_get_contents($urlImg);
            $url = REST_MEDIA_URL;
            $ch = curl_init();
            $username = 'robogenimo';
            $password = 'robogenimo2017@';

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $file);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'Content-Disposition: form-data; filename="' . $img->nmFileName . '"',
                'Authorization: Basic ' . base64_encode($username . ':' . $password),
                    //'Content-Type: application/json',
                    //'Accept: application/json'
            ]);
            $result = null;
            $result = curl_exec($ch);
            curl_close($ch);
            // echo "\n----------------------------------------";
            $imagem = json_decode($result);

            //var_dump($imagem);
            $imgIds[] = $imagem->id;
        }
        if (count($imgIds) > 0) {
            //Cria a imagem destacada e associa as imagens com o imovel
            GenimoWordpress::insertSpotLight($property->idPropertyDB, $imgIds[0]);
            for ($i = 1; $i < count($imgIds); $i++) {
                // change Joe's password
                DB::update('wp_posts', array(
                    'post_parent' => $property->idPropertyDB
                        ), "ID=%s", $imgIds[$i]);
            }
        }
    }

    public static function insertSpotLight($idProperty, $idImg) {
        DB::insert('wp_postmeta', array(
            'post_id' => $idProperty, //default for all
            'meta_key' => '_thumbnail_id', //Just now its new
            'meta_value' => $idImg                                      //Default no need
        ));

        return DB::insertId();
    }

    /**
      @Get address formated
     */
    public static function getAddress($property) {
        return $property->nmNeighborhood . ", " . $property->nmCity . " - " . $property->sgState;
    }

    /**
      @prepare post insert
     */
    public static function prepareMeta($metaKey, $metaValue, $idPost) {
        return array(
            'post_id' => $idPost,
            'meta_key' => $metaKey,
            'meta_value' => $metaValue
        );
    }

    /**
     *   Get featured image path
     */
    public static function getPropertyFeaturedImage($property) {
        $dsPath = null;
        if (strlen($property->nmFileNameSpotlight) > 0) {
            $dsPath = $dsUrlBaseGenimo . "/media/" . $property->idProperty . "/" . $property->nmFileNameSpotlight;
        } else {
            $dsPath = $dsUrlBaseGenimo . "/img/casaAzul.png";
        }
        return $dsPath;
    }

    /*
     *   Get Property Finallity
     */

    public static function getPropertyFinallity($property) {
        $dsFinality = ($property->tpFinality == 1) ? "Comercial" : ($property->tpFinality == 2) ? "Residencial" : "Residencial ou Comercial";
        return $dsFinality;
    }

    /*
     *   Get Floor type
     */

    public static function getPropertyFloor($property) {
        $dsFloor = ($property->tpFloor == 1) ? "Madeira" : ($property->tpFloor == 2) ? "Ceramica" : ($property->tpFloor == 3) ? "Vinílico" : ($property->tpFloor == 4) ? "Laminado" : ($property->tpFloor == 5) ? "Carpete" : "";
        return $dsFloor;
    }

    /**
     *   remove all non alpha numeric chars and spaces from urls
     */
    public static function cleanUrl($id) {
        // var_dump($id);die;
        return "?post_type=listing&#038;p=" . $id[0];
    }

    /*
     * Get Type of Business [rent,sale,rent1,sale1]
     */

    public static function getTypeOfBusiness($property) {
        $typeOfBusiness = new stdClass();
        if ($property->cdMode == 1) {
            $typeOfBusiness->vlPrice = $property->vlRental;
            $typeOfBusiness->tpPriceOffer = "rent";
            $typeOfBusiness->tpPricePeriod = "rental_period_1";
        } else if ($property->cdMode == 2) {
            $typeOfBusiness->vlPrice = $property->vlSale;
            $typeOfBusiness->tpPriceOffer = "sale";
            $typeOfBusiness->tpPricePeriod = "sale";
        } else if ($property->cdMode == 4) {
            $typeOfBusiness->vlPrice = $property->vlSeasonRent;
            $typeOfBusiness->tpPriceOffer = "rent";
            $typeOfBusiness->tpPricePeriod = "rental_period_2";
        } else if ($property->cdMode == 5) {
            $typeOfBusiness->typeOfBusiness->vlPrice = $property->vlLowSeasonRent;
            $typeOfBusiness->tpPriceOffer = "rent";
            $typeOfBusiness->tpPricePeriod = "rental_period_3";
        } else {
            $typeOfBusiness->vlPrice = "";
            $typeOfBusiness->tpPriceOffer = "sale";
            $typeOfBusiness->tpPricePeriod = "sale";
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
function makeSlug($string) {
    $str = tirarAcentos($string);
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $str);
    return strtolower($slug);
}

function tirarAcentos($string) {
    return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/", "/(Ç)/"), explode(" ", "a A e E i I o O u U n N c C"), $string);
}
