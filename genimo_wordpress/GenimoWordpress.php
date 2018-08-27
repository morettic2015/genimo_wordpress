<?php

/**
 *   @Class for integrate GENIMO ERP With Wordpress Wp-casa;
 *   @Copyright @morettic.com.br
 * 
 * 
 * 
 */
class GenimoWordpress extends stdClass {

    public static function adSiteContact() {
        //DB::debugMode();
        echo "<pre>";
        $adSiteContacts = DB::query("select distinct ID from wp_posts where post_type = 'nf_sub'");
        //var_dump($adSiteContacts);
        //echo "aaa";
        $registers = array();
        $rows = array();
        $i = 0;
        $segmentId = LeadMobi::createSegment(MAUTIC_S_NAME_L, MAUTIC_S_SLUG_L, MAUTIC_S_DESC_L);
        foreach ($adSiteContacts as $row) {
            $query = "select (select meta_value as mensagem from wp_postmeta where post_id in(select ID from wp_posts where id = " . $row['ID'] . ") and meta_key = '_seq_num') as id,
                    (select meta_value as nome from wp_postmeta where post_id in(select ID from wp_posts where id = " . $row['ID'] . ") and meta_key = '_field_5') as nome,
                    (select meta_value as email from wp_postmeta where post_id in(select ID from wp_posts where id = " . $row['ID'] . ") and meta_key = '_field_6') as email, 
                    (select meta_value as phone from wp_postmeta where post_id in(select ID from wp_posts where id = " . $row['ID'] . ") and meta_key = '_field_7') as phone,
                    (select meta_value as mensagem from wp_postmeta where post_id in(select ID from wp_posts where id = " . $row['ID'] . ") and meta_key = '_field_9') as msg,
                    (select meta_value as mensagem from wp_postmeta where post_id in(select ID from wp_posts where id = " . $row['ID'] . ") and meta_key = '_is_exported') as stats
                    from dual";
            //Local object
            $o = DB::query($query);
            $registers[] = $o;

            var_dump($o);

            /*  */
            //has been integrated
            if ($o[0]['stats'] == "1") {
                echo "Already integrated";
                continue;
            }

            $nome = $o[0]['nome'];
            $email = $o[0]['email'];
            $phone = $o[0]['phone'];
            $msg = $o[0]['msg'];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo $email . " - invalid";
                continue; //Invalid email
            }

            $crulRet = LeadMobi::adSiteContact(CD_IMOBILIARIA, $nome, $email, $phone, $msg, null, null, 1);
            //var_dump($crulRet);
            //Flag as exported
            $rows[] = array(
                'post_id' => $row['ID'],
                'meta_key' => '_is_exported',
                'meta_value' => '1'
            );
            //Lead from Site to Mautic
            $contactId = LeadMobi::createContact($o[0]['nome'], "", $o[0]['email']);
            $res = LeadMobi::addContactToSegment($contactId, $segmentId);
            $i++;
        }
        if (count($rows) > 0)
            DB::insert('wp_postmeta', $rows);
        //$obj = json_encode($registers); //var_dump($obj);die();
        //echo $obj;
    }

    public static function listingsLoad($idCompany) {
        $urlProperty = "https://genimo.com.br/api/site/propertyForPublication/" . $idCompany;
        $json = file_get_contents($urlProperty);
        $obj = json_decode($json); //var_dump($obj);die();
        foreach ($obj as $r) {
            $r1 = GenimoWordpress::syncProperty($idCompany, $r->idProperty);
            GenimoWordpress::copyImages($r1);
            //  echo "FINISH IMPORT IMAGE FROM PROPERTY" . $idProperty . "<br>";
            MetaSlider::makeSliders($r1);
            $r1 = null;
        }
    }

    public static function syncSellers($idCompany) {
        //DB::debugMode();
        $date = date("Y-m-d H:i:s");
        // DB::startTransaction();
        echo "<pre>";
        //echo "DAO INIT" . "<br>";
        //Read from url
        $urlProperty = "https://genimo.com.br/api/site/company/" . $idCompany;
        $json = file_get_contents($urlProperty);
        $obj = json_decode($json); //var_dump($obj);die();
        //var_dump($obj->sellers);
        //die;
        //remove all 
        DB::query("delete from wp_postmeta where wp_postmeta.post_id in (select ID from wp_posts where post_type = 'team_showcase_post')");
        $counter = DB::affectedRows();
        echo "WP_POSTMETA REMOVED:" . $counter . "\n";

        DB::query("delete from wp_posts where post_type = 'team_showcase_post'");
        $counter = DB::affectedRows();
        echo "WP_POST REMOVED:" . $counter . "\n";
        //Create SEgment
        $segmentId = LeadMobi::createSegment(MAUTIC_S_NAME_C, MAUTIC_S_SLUG_C, MAUTIC_S_DESC_C);
        foreach ($obj->sellers as $row) {
            DB::insert('wp_posts', array(
                'post_author' => 1, //default for all
                'post_date' => $date, //Just now its new
                'post_date_gmt' => $date, //just now its new
                'post_content' => utf8_decode($row->dsEmail), //Get as String UTF 8
                'post_title' => utf8_decode($row->nmPerson), //Get as String UTF 8
                'post_name' => makeSlug($row->nmPerson . '_' . $row->dsEmail), //Get as String UTF 8
                'post_excerpt' => htmlentities($row->dsEmail), //Default Empty
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
                'guid' => makeSlug($row->nmPerson . '_' . $row->dsEmail), //Guid Url for Property
                'menu_order' => '0', //Default no need
                'post_type' => 'team_showcase_post', //Post type listing for all property
                'post_mime_type' => '', //Default no need
                'comment_count' => '0'                                          //Default no need
            ));
            //Add lead to Mautic Integration
            $contactId = LeadMobi::createContact($row->nmPerson, "", $row->dsEmail);
            $res = LeadMobi::addContactToSegment($contactId, $segmentId);
            //Get new Property Key from database
            $idSeller = DB::insertId();
            $metadata[] = GenimoWordpress::prepareMeta('fifu_image_url', $row->dsAvatarPath, $idSeller);
            $metadata[] = GenimoWordpress::prepareMeta('_email_seller_', $row->dsEmail, $idSeller);
            //Insert post id meta
            //Get type of business and values for rent and sale
            //$typeOfBusiness = getTypeOfBusiness($obj->property);
        }
        DB::insert('wp_postmeta', $metadata);
    }

    /**
     *   @ Recupera todos os eventos apresentados hoje com todas as categorias
     */
    public static function syncProperty($idCompany, $idProperty) {
        DB::debugMode();
        // DB::startTransaction();
        //echo "<pre>";
        //echo "DAO INIT" . "<br>";
        //Read from url
        $urlProperty = "https://genimo.com.br/api/site/property/" . $idCompany . "/" . $idProperty;

        // echo $urlProperty;
        //Get JSON $yourString );
        $jso1 = getdataFromURL($urlProperty);
        //$jso1 = '{"foo": "bar", "cool": "attr"}';
        // echo $jso1;
        //
        // $str = str_replace(array("\r","\n"), "", $jso1);
        //$jso1 = nl2br($str);
        //  echo "<pre>";
        $obj = json_decode(($jso1));
        // var_dump($obj);die;
        // die();
        //dum memnory
        //var_dump($obj);
        //die;
        //
        //$rooms
        //Id property
        //$idProperty = $obj->property->idProperty;
        //Recupera meta key com o IdProperty
        $metaKeyIdProperty = DB::queryOneColumn('post_id', "select * from wp_postmeta where meta_key = '_listing_id' and meta_value = '" . $idProperty . "'");
        //print_r($metaKeyIdProperty);
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
        ////type of business
        $typeOfBusiness = GenimoWordpress::getTypeOfBusiness($obj->property);
        //Get Date
        $date = date("Y-m-d H:i:s");
        if ($idPropertyDB > 0) {
            //echo "INSERT<br>";
            //Get Date
            //Não existe o post nem a meta key
            DB::insert('wp_posts', array(
                'post_author' => 1, //default for all
                'post_date' => $date, //Just now its new
                'post_date_gmt' => $date, //just now its new
                'post_content' => utf8_decode($obj->property->dsPropertySite), //Get as String UTF 8
                'post_title' => utf8_decode($postTitle), //Get as String UTF 8
                'post_name' => makeSlug($postTitle . '_' . $idProperty), //Get as String UTF 8
                'post_excerpt' => htmlentities($obj->property->nmPropertySite), //Default Empty
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
            $metadata[] = GenimoWordpress::prepareMeta('_map_address', ($dsAddressMap), $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_lat', $obj->property->vlLatitude, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_long', $obj->property->vlLongitude, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_geolocation_formatted_address', ($dsAddress), $idPropertyDB);
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
            $metadata[] = GenimoWordpress::prepareMeta('_details_9', GenimoWordpress::getPropertyFloor($obj->property), $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_vl_low_season_rent', $obj->property->vlLowSeasonRent, $idPropertyDB);
           // $metadata[] = GenimoWordpress::prepareMeta('_vl_rent', $obj->property->vlRental, $idPropertyDB);
            $metadata[] = GenimoWordpress::prepareMeta('_vl_season_rent', $obj->property->vlSeasonRent, $idPropertyDB);
            //Insert post id meta
            //  var_dump($obj->property);
            //    echo $obj->property->vlSeasonRent.'/'.$obj->property->vlRental.'/'.$obj->property->vlLowSeasonRent;
            //Get type of business and values for rent and sale
            //$typeOfBusiness = getTypeOfBusiness($obj->property);
            DB::insert('wp_postmeta', $metadata);
            //Insert Listing Location
            $tax = utf8_decode($obj->property->nmNeighborhood . "," . $obj->property->nmCity);
            $taxSlug = makeSlug($tax);

            //echo "\n" . $taxSlug . "\n";
            GenimoWordpress::insertTaxionomy($taxSlug, $tax, $idPropertyDB, 'location', $tax);

            //new ID
            $obj->idPropertyDB = $idPropertyDB;

            // DB::commit();
        } else {
            $obj->idPropertyDB = $metaKeyIdProperty[0];
            //echo "UPDATE " . $obj->idPropertyDB . "<br>";
            //Old ID
            DB::update('wp_posts', array(
                'post_content' => utf8_decode($obj->property->dsPropertySite), //Get as String UTF 8
                'post_title' => utf8_decode($postTitle), //Get as String UTF 8
                'post_name' => makeSlug($postTitle . '_' . $idProperty), //Get as String UTF 8
                'post_excerpt' => utf8_decode($obj->property->nmPropertySite), //Default Empty
                'post_status' => 'publish', //Publish online / Trash offline
                'post_modified' => $date, //Just now
                'post_modified_gmt' => $date, //Just now
                    ), "ID=%s", $obj->idPropertyDB);

            //Post e meta key existem
            GenimoWordpress::updateALLPropertyTaxionomy($obj->property, $obj->idPropertyDB);
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $typeOfBusiness->tpPriceOffer, '_price_offer');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $typeOfBusiness->tpPricePeriod, '_price_period');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $typeOfBusiness->vlPrice, '_price');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->amSuite + $obj->property->amRooms, '_details_1');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->amBathroom, '_details_2');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlPropertyTotalArea, '_details_3');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlAreaM2C, '_details_4');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlGroundArea, '_details_5');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->amGarage, '_details_6');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $useOf, '_details_7');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->qtYearBuilt, '_details_8');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, GenimoWordpress::getPropertyFloor($obj->property), '_details_9');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, ($dsAddressMap), '_map_address');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlLatitude, '_geolocation_lat');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlLongitude, '_geolocation_long');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, ($dsAddress), '_geolocation_formatted_address');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->dsAddress, '_geolocation_street');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->sgState, '_geolocation_state_short');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->nmState, '_geolocation_state_long');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlLowSeasonRent, '_vl_low_season_rent');
            //GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlRental, '_vl_rent');
            GenimoWordpress::updatePostMeta($obj->idPropertyDB, $obj->property->vlSeasonRent, '_vl_season_rent');

            //  var_dump($obj->property);
            //echo $obj->property->vlSeasonRent.'/'.$obj->property->vlRental.'/'.$obj->property->vlLowSeasonRent;
            //GenimoWordpress::prepareMeta('_vl_rent', $obj->property->vlRental, $idPropertyDB);
        }

        //var_dump($types);

        DB::disconnect();
        //Internal property id on Wordpress

        return $obj;
    }

    /**

     * */
    public static function removePropertyTaxionomyRelationship($idProperty, $term_slug) {
        //Get Term ID
        $result = DB::queryFirstRow("SELECT term_id FROM wp_terms WHERE slug=%s", $term_slug);
        $term_id = $result['term_id'];
        //Get Term Relatioship ID
        $result = DB::queryFirstRow("SELECT term_taxonomy_id FROM wp_term_taxonomy where term_id=%s", $term_id);
        $term_taxonomy_id = $result['term_taxonomy_id'];
        if (empty($term_taxonomy_id))
            return;
        //Remove relatioships not used anymore
        DB::query("delete FROM wp_term_relationships where object_id=$idProperty and term_taxonomy_id =$term_taxonomy_id");
        $counter = DB::affectedRows();
        //echo "\n wp_term_relationships".$counter.'\n';
    }

    /**
      @ update all property taxionomy
      //insertTaxionomy($slug,$name,$idProperty,$taxionomy,$desc)
     */
    public static function updateALLPropertyTaxionomy($property, $idPropertyDB) {
        //echo "Taxionomies";
        //var_dump($property);die();
        if ($property->flLaundry == 1) {
            GenimoWordpress::insertTaxionomy("flLaundry", "Lavanderia", $idPropertyDB, 'feature', utf8_decode("Lavanderia"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flLaundry");
        }
        if ($property->flLavatory == 1) {
            GenimoWordpress::insertTaxionomy("flLavatory", "Lavabo", $idPropertyDB, 'feature', utf8_decode("Lavabo"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flLavatory");
        }
        if ($property->flCloset == 1) {
            GenimoWordpress::insertTaxionomy("flMaidRoom", "Dependencia Empregada", $idPropertyDB, 'feature', utf8_decode("Dependência Empregada"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flMaidRoom");
        }
        if ($property->flCloset == 1) {
            GenimoWordpress::insertTaxionomy("flCloset", "Closet", $idPropertyDB, 'feature', utf8_decode("Closet"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flCloset");
        }
        if ($property->flInhabited == 1) {
            GenimoWordpress::insertTaxionomy("flInhabited", "Habitado", $idPropertyDB, 'feature', utf8_decode("O imovel  está habitado"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flInhabited");
        }
        if ($property->flAcceptFunding == 1) {
            GenimoWordpress::insertTaxionomy("flAcceptFunding", "Aceita financiamento", $idPropertyDB, 'feature', utf8_decode("O imóvel aceita financiamento"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flAcceptFunding");
        }
        if ($property->tpFurnished == 3) {
            GenimoWordpress::insertTaxionomy("tpFurnished", "Mobiliado", $idPropertyDB, 'feature', utf8_decode("O imóvel está mobiliado"));
        }
        if ($property->flHighStandart == 1) {
            GenimoWordpress::insertTaxionomy("flHighStandart", utf8_decode("Alto padrão"), $idPropertyDB, 'feature', utf8_decode("Imóvel de alto padrão"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flHighStandart");
        }
        if ($property->flFacingSea == 1) {
            GenimoWordpress::insertTaxionomy("flFacingSea", "Frente para o mar", $idPropertyDB, 'feature', utf8_decode("Localizado de frente para o mar"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flFacingSea");
        }
        if ($property->flBarbecueGrill == 1) {
            GenimoWordpress::insertTaxionomy("flBarbecueGrill", "Churrasqueira", $idPropertyDB, 'feature', utf8_decode("O imóvel possui churrasqueira na sacada"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flBarbecueGrill");
        }
        if ($property->flFireplace == 1) {
            GenimoWordpress::insertTaxionomy("flFireplace", "Lareira", $idPropertyDB, 'feature', utf8_decode("O imóvel possui lareira"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flFireplace");
        }
        if ($property->flBalcony == 1) {
            GenimoWordpress::insertTaxionomy("flBalcony", "Sacada", $idPropertyDB, 'feature', utf8_decode("O imóvel possui sacada"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flBalcony");
        }
        if ($property->flHeatedPool == 1) {
            GenimoWordpress::insertTaxionomy("flHeatedPool", "Piscina aquecida", $idPropertyDB, 'feature', utf8_decode("O imóvel possui piscina aquecida"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flHeatedPool");
        }
        if ($property->flGym == 1) {
            GenimoWordpress::insertTaxionomy("flGym", "Academia", $idPropertyDB, 'feature', utf8_decode("O imóvel possui academia"));
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flGym");
        }
        if ($property->flAccessChallengedPeople == 1) {
            GenimoWordpress::insertTaxionomy("flAccessChallengedPeople", "Acessibilidade", $idPropertyDB, 'feature', "O condominio possui acesso para portadores de necessidades especiais");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flAccessChallengedPeople");
        }
        if ($property->flCollectiveBarbecue == 1) {
            GenimoWordpress::insertTaxionomy("flCollectiveBarbecue", "Churrasqueira coletiva", $idPropertyDB, 'feature', "O imóvel possui churrasqueira coletiva");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flCollectiveBarbecue");
        }
        if ($property->flElevator == 1) {
            GenimoWordpress::insertTaxionomy("flElevator", "Elevador", $idPropertyDB, 'feature', "O imóvel possui elevador");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flElevator");
        }
        if ($property->flPlayground == 1) {
            GenimoWordpress::insertTaxionomy("flPlayground", "Playground", $idPropertyDB, 'feature', "O imóvel possui playground");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flPlayground");
        }
        if ($property->flPool == 1) {
            GenimoWordpress::insertTaxionomy("flPool", "Piscina", $idPropertyDB, 'feature', "O imóvel tem piscina");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flPool");
        }
        if ($property->flOrdinance == 1) {
            GenimoWordpress::insertTaxionomy("flOrdinance", "Portaria 24 horas", $idPropertyDB, 'feature', "O imóvel tem portaria 24 horas");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flOrdinance");
        }
        if ($property->flSportsCourt == 1) {
            GenimoWordpress::insertTaxionomy("flSportsCourt", "Quadra de esportes", $idPropertyDB, 'feature', "O imóvel possui quadra de esportes");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flSportsCourt");
        }
        if ($property->flLoungeParties == 1) {
            GenimoWordpress::insertTaxionomy("flLoungeParties", "Salão de festas", $idPropertyDB, 'feature', "O imóvel tem salão de festas");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "flLoungeParties");
        }
        if ($property->tpSpotlight > 0) {
            GenimoWordpress::insertTaxionomy("tpSpotlight", "Oportunidade do momento", $idPropertyDB, 'feature', "Oportunidade do momento");
        } else {
            GenimoWordpress::removePropertyTaxionomyRelationship($idPropertyDB, "tpSpotlight");
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
        // echo "---$id----";
        // echo "$idPropertyDB-----";
        $pid = intval($id);
        switch ($pid) {
            case 1:
                GenimoWordpress::insertTaxionomy("catCasa", "Casa", $idPropertyDB, 'listing-type', "Casa");
                // echo "CASA";
                break;
            case 2:
                GenimoWordpress::insertTaxionomy("catApto", "Apartamento", $idPropertyDB, 'listing-type', "Apartamento");
                //  echo "APTO";
                break;
            case 3:
                GenimoWordpress::insertTaxionomy("catTerreno", "Terreno", $idPropertyDB, 'listing-type', "Terreno");
                //  echo "TERRENO";
                break;
            case 4:
                GenimoWordpress::insertTaxionomy("catQuitinete", "Quitinete", $idPropertyDB, 'listing-type', "Quitinete");
                //   echo "QUITINETE";
                break;
            case 5:
                GenimoWordpress::insertTaxionomy("catSalaComercial", "Sala Comercial", $idPropertyDB, 'listing-type', "Sala Comercial");
                //  echo "SALA COMERCIAL";
                break;
            case 6:
                GenimoWordpress::insertTaxionomy("catGalpao", "Galpão", $idPropertyDB, 'listing-type', "Galpão");
                //   echo "GAlpão";
                break;
            case 7:
                GenimoWordpress::insertTaxionomy("catCobertura", "Cobertura", $idPropertyDB, 'listing-type', "Cobertura");
                //   echo "Cobertura";
                break;
        }

        //echo "\n";
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

            $term_tax_id = DB::queryOneField('term_taxonomy_id', 'SELECT term_taxonomy_id FROM wp_term_taxonomy where term_id = ' . $term_id);
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
            //echo "<br>$slug, $name, $idProperty, $taxionomy, $desc";
            // return null;
        }
    }

    /**
      @Get Map locations
     */
    public static function getMapAddress($dsAddress) {
        /* $dsAddressMap = str_replace(".", "", $dsAddress);
          $dsAddressMap = str_replace(",", "", $dsAddressMap);
          $dsAddressMap = str_replace("-", "", $dsAddressMap); */
        return ($dsAddress);
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
     * SELECT * FROM wp_postmeta where meta_key = '_thumbnail_id '
     * 
     * SELECT * FROM wp_term_relationships where object_id = 318771;
     *
     * SELECT distinct meta_key FROM wp_postmeta;
     *
     * delete FROM wp_postmeta where meta_key = '_listing_id';
     *
     * select * from wp_term_taxonomy where term_taxonomy_id in (1123,1149)
     *
     *      
     */
    public static function copyImages($property) {
        // DB::debugMode();
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
            //Image Name
            $imageNameForLike = str_replace("[", "", $img->nmFileName);
            $imageNameForLike = str_replace("]", "", $imageNameForLike);
            $pimgId = DB::query("SELECT ID FROM wp_posts where post_type = 'attachment' and guid like '%" . $imageNameForLike . "%'");
            $counter = DB::count();
            //var_dump($pimgId);
            //echo "\n Image Occurences:" . $counter;
            //echo $pimgId['ID'];
            if ($counter > 0) {
                ///echo "\n";
                //echo $img->nmFileName . ' Exists\n';
                $imgIds[] = $pimgId[0]['ID'];
                continue;
            } else {

                $urlImg = $img->dsImagePath . "/" . $img->nmFileName;
                //  echo $urlImg . "\n";

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
                // $result = null;
                $result = curl_exec($ch);

                // var_dump($result);
                curl_close($ch);
                // echo "\n----------------------------------------";
                $imagem = json_decode($result);

                //  var_dump($imagem);
                $imgIds[] = $imagem->id;
            }
        }
        //Clean empty trash
        $imgIds = GenimoWordpress::trimArrayFromNull($imgIds);
        //var_dump($imgIds);
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

    /**
     * @Fix bug from importing missing images
     */
    public static function trimArrayFromNull($arr) {
        $vet = [];
        foreach ($arr as $key => $val) {
            if (empty($val)) {
                continue;
            }
            $vet[] = $val;
        }
        return $vet;
    }

    /**
     * @Insert spotlight visible only inside admin
     */
    public static function insertSpotLight($idProperty, $idImg) {
        //remove old image thumb
        $query = "delete from wp_postmeta where post_id = $idProperty and meta_key = '_thumbnail_id'";
        DB::query($query);
        //Add new one
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
        // var_dump($property);
        $original = $property->nmNeighborhood . ", " . $property->nmCity . " - " . $property->sgState . ", " . $property->dsAddress. ", " . $property->dsAddress2; 
        $html = htmlentities($original);
        return $html;
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
        $cod = intval($property->tpFloor);
        $dsFloor = "";
        switch ($cod) {
            case 1:
                $dsFloor = "Madeira";
                break;
            case 2:
                $dsFloor = "Ceramica";
                break;
            case 3:
                $dsFloor = "Vinílico";
                break;
            case 4:
                $dsFloor = "Laminado";
                break;
            case 5:
                $dsFloor = "Carpete";
                break;
        }


        //   echo $dsFloor . "------------------------\n";

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
        //var_dump($property);die;
        $tpOffer = intval($property->cdMode);
        switch ($tpOffer) {
            case 1:
                $typeOfBusiness->vlPrice = $property->vlRental;
                $typeOfBusiness->tpPriceOffer = "rent";
                $typeOfBusiness->tpPricePeriod = "rental_period_3";
                break;
            case 2:
                $typeOfBusiness->vlPrice = $property->vlSale;
                $typeOfBusiness->tpPriceOffer = "sale";
                $typeOfBusiness->tpPricePeriod = "sale";
                break;
            case 4:
                $typeOfBusiness->vlPrice = $property->vlSeasonRent;
                $typeOfBusiness->tpPriceOffer = "rent";
                $typeOfBusiness->tpPricePeriod = "rental_period_3";
                break;
            case 5:
                $typeOfBusiness->typeOfBusiness->vlPrice = $property->vlLowSeasonRent;
                $typeOfBusiness->tpPriceOffer = "rent";
                $typeOfBusiness->tpPricePeriod = "rental_period_3";
            default:
                $typeOfBusiness->vlPrice = $property->vlSale;
                $typeOfBusiness->tpPriceOffer = "sale";
                $typeOfBusiness->tpPricePeriod = "sale";
                break;
        }


        //var_dump($typeOfBusiness);

        return $typeOfBusiness;
    }

    //update wp_postmeta  set meta_value=%s where meta_key =%s and post_id =$i;
    public static function updatePostMeta($ID, $meta_value, $meta_key) {
        $idMetaKeyLocal = DB::queryFirstRow("select meta_id from wp_postmeta where meta_key = '$meta_key' and post_id = '$ID'");
        if (empty($idMetaKeyLocal['meta_id'])) {
            DB::insert('wp_postmeta', array(
                'meta_key' => $meta_key,
                'meta_value' => $meta_value,
                'post_id' => $ID
            ));
        } else {
            //if()
            DB::query("update wp_postmeta  set meta_value=%s where meta_key =%s and post_id =%i", $meta_value, $meta_key, $ID);
        }
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

  -- SELECT * FROM wp_postmeta where meta_key = '_details_1'

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

function getdataFromURL($url, $args = false) {
    $options = array(
        CURLOPT_RETURNTRANSFER => true, // return web page
        CURLOPT_HEADER => false, // don't return headers
        CURLOPT_FOLLOWLOCATION => true, // follow redirects
        CURLOPT_MAXREDIRS => 10, // stop after 10 redirects
        CURLOPT_ENCODING => "ISO-8859-1", // handle compressed
        CURLOPT_USERAGENT => "test", // name of client
        CURLOPT_AUTOREFERER => true, // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120, // time-out on connect
        CURLOPT_TIMEOUT => 120, // time-out on response
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content = curl_exec($ch);

    curl_close($ch);

    return $content;
}
