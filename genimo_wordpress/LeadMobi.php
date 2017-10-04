<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

/**
 * Description of LeadMobi
 *
 * @author Morettic LTDA
 */
$auth = null;

class LeadMobi extends stdClass {

    /**
     * @Add Lead To Segment
     */
    public static function addContactToSegment($contactId, $segmentId) {
        $con = LeadMobi::connectMautic();
        $segmentApi = $con->api->newApi("segments", $con->auth, MAUTIC_INSTANCE_API);
        $response = $segmentApi->addContact($segmentId, $contactId);
        return $response;
    }

    /**
     * @Create Lead Contact on Mautic
     */
    public static function createContact($first, $last, $email) {
        $con = LeadMobi::connectMautic();
//echo "<pre>";
// var_dump($con);
        $contactApi = $con->api->newApi("contacts", $con->auth, MAUTIC_INSTANCE_API);
        $data = array(
            'firstname' => $first,
            'lastname' => $last,
            'email' => $email,
            'ipAddress' => $_SERVER['REMOTE_ADDR']
        );
//var_dump($data);

        $contact = $contactApi->create($data);
//var_dump($contact);
//Lead ID From Mautic
        return $contact['contact']['id'];
    }

    /**
     *  @Connect with Mautic 
     * */
    public static function connectMautic() {

        @session_start();

// ApiAuth->newAuth() will accept an array of Auth settings
        $settings = array(
            'baseUrl' => MAUTIC_INSTANCE_URL, //Base Url Instance
            'userName' => MAUTIC_USER, // Create a new user       
            'password' => MAUTIC_PASS  // Make it a secure password
        );

// Initiate the auth object specifying to use BasicAuth
        $ret = new stdClass();
        $initAuth = new ApiAuth();
//Ret Object
        $ret->auth = $initAuth->newAuth($settings, 'BasicAuth');
        $ret->api = new MauticApi();
//var_dump($api);die;
        return $ret;
    }

    /**
     * @Create Segment on Mautic and WP
     */
    public static function createSegment($name, $alias, $description) {
//DB::debugMode();
// echo "<pre>";
        $query = "SELECT post_parent FROM wp_posts WHERE guid = '$alias' and post_type = '_mtc_segment'";
        $mtc = DB::query($query);
        if (empty($mtc)) {
//Connect to Mautic
            $con = LeadMobi::connectMautic();
            $segmentApi = $con->api->newApi("segments", $con->auth, MAUTIC_INSTANCE_API);
            $data = array(
                'name' => $name,
                'alias' => $alias,
                'description' => $description,
                'isPublished' => 1
            );

            $date = date("Y-m-d H:i:s");
//Create Segment
            $segment = $segmentApi->create($data);
//Get SEgment ID From Mautic
            $segmentIdMtc = $segment['list']['id'];
            DB::insert('wp_posts', array(
                'post_author' => 1, //default for all
                'post_date' => $date, //Just now its new
                'post_date_gmt' => $date, //just now its new
                'post_content' => utf8_decode($description), //Get as String UTF 8
                'post_title' => utf8_decode($name), //Get as String UTF 8
                'post_name' => makeSlug($alias), //Get as String UTF 8
                'post_excerpt' => utf8_decode($description), //Default Empty
                'post_status' => 'publish', //Publish online / Trash offline
                'comment_status' => 'closed', //Comment closed for all default
                'ping_status' => 'closed', //Ping status closed default for all
                'post_password' => '', //Post password empty 
                'to_ping' => '', //No need for it
                'pinged' => '', //No need for it
                'post_modified' => $date, //Just now
                'post_modified_gmt' => $date, //Just now
                'post_content_filtered' => '', //No need for 
                'post_parent' => $segmentIdMtc, //No need for parent
                'guid' => makeSlug($alias), //Guid Url for Property
                'menu_order' => '0', //Default no need
                'post_type' => '_mtc_segment', //Post type listing for all property
                'post_mime_type' => '', //Default no need
                'comment_count' => '0'                                          //Default no need
            ));
//Get new Property Key from database
            $idSegment = DB::insertId();
            return $segmentIdMtc;
        } else {
            var_dump($mtc);
            return $mtc[0]['post_parent'];
        }
    }

    /**
     * site/propertyForPublication/:idCompany(/:cdMode)(/:idCategory)(/:qtBedroom)(/:idCity)(/:idNeighborhood)(/:tpFinality)(/:idFeed)(/:tpArea)(/:tpCiclo)(/:flFrenteMar)(/:flAltoPadrao)(/:flSpotlight)(/:vlMaxPrice)', function ($idCompany, $cdMode=0, $idCategory=0, $qtBedroom=0, $idCity=0, $idNeighborhood=0, $tpFinality=0, $idFeed=3, $tpArea=0, $tpCiclo=0, $flFrenteMar=0, $flAltoPadrao=0, $flSpotlight=0, $vlMaxPrice=0
      site/propertyForPublication/:idCompany(/:cdMode)(/:idCategory)(/:qtBedroom)(/:idCity)(/:idNeighborhood)(/:tpFinality)(/:idFeed)(/:tpArea)(/:tpCiclo)(/:flFrenteMar)(/:flAltoPadrao)(/:flSpotlight)(/:vlMaxPrice)
      https://genimo.com.br/api/site/propertyForPublication/41
      /site/company/:idCompany
      https://genimo.com.br/api/site/company/105
      /site/addSiteContact
      $idCompany = $app->request->params('idCompany');
      $nmPerson = $app->request->params('nmPerson');
      $dsEmail = $app->request->params('dsEmail');
      $nuPhone = $app->request->params('nuPhone');
      $dsApproach = $app->request->params('dsApproach');
      $idProperty = $app->request->params('idProperty');
      $idBookie = $app->request->params('idBookie');
      $tpContactPreference = $app->request->params('tpContactPreference');
      /site/property/:idCompany/:idProperty
      https://genimo.com.br/api/site/property/41/253
      https://genimo.com.br/api/site/addSiteContact/43/Jorge%20Paulada/email@morettic.com.br/+554896004929/Contato%20site%20genimo/1523/1/1/
     *      */
    public static function adSiteContact($idCompany, $nmPerson, $dsEmail, $nuPhone = NULL, $dsApproach = NULL, $idProperty = NULL, $idBookie = NULL, $tpContactPreference = NULL) {
//put your code here
        $url = 'https://genimo.com.br/api/site/addSiteContact';
        $fields = array(
            'idCompany' => urlencode($idCompany),
            'nmPerson' => urlencode($nmPerson),
            'dsEmail' => urlencode($dsEmail),
            'nuPhone' => urlencode($nuPhone),
            'dsApproach' => urlencode($dsApproach),
            'idProperty' => urlencode($idProperty),
            'idBookie' => urlencode($idBookie),
            'tpContactPreference' => urlencode($tpContactPreference)
        );
        echo $url;
        $fields_string = null;
//url-ify the data for the POST
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

//open connection
        $ch = curl_init();

//set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

//execute post
        $result = curl_exec($ch);

//close connection
        curl_close($ch);


        return $result;
    }

}
