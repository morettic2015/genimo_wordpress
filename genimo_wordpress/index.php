<?php

require './vendor/autoload.php';

//header("Access-Control-Allow-Origin: *");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//Import Libs
include_once './config.php';
include_once './GenimoWordpress.php';
include_once './LeadMobi.php';
//require 'GuiaSynchronize.php';
//Init Objects
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ]
        ]);
//$app->view(new \JsonApiView());
//$app->add(new \JsonApiMiddleware());
//Define Routes
//Busca Eventos de Hoje
$app->get('/property/{idCompany}/{idProperty}', function (Request $request, Response $response) use ($app) {
    $idProperty = $request->getAttribute('idProperty');
    $idCompany = $request->getAttribute('idCompany');
    //echo "INIT IMPORT DATA FROM PROPERTY" . $idProperty . "<br>";
    $obj = GenimoWordpress::syncProperty($idCompany, $idProperty);
    // echo "FINISH IMPORT DATA FROM PROPERTY" . $idProperty . "<br>";
    // echo "INIT IMPORT IMAGE FROM PROPERTY" . $idProperty . "<br>";
    GenimoWordpress::copyImages($obj);
    //  echo "FINISH IMPORT IMAGE FROM PROPERTY" . $idProperty . "<br>";
});

$app->get('/sellers/{idCompany}', function (Request $request, Response $response) use ($app) {
    $idCompany = $request->getAttribute('idCompany');
    //echo "INIT IMPORT DATA FROM PROPERTY" . $idProperty . "<br>";
    $obj = GenimoWordpress::syncSellers($idCompany);
});
$app->get('/listings/{idCompany}', function (Request $request, Response $response) use ($app) {
    $idCompany = $request->getAttribute('idCompany');
    //echo "INIT IMPORT DATA FROM PROPERTY" . $idProperty . "<br>";
    GenimoWordpress::listingsLoad($idCompany);
});
$app->get('/adsitecontact/', function (Request $request, Response $response) use ($app) {
    GenimoWordpress::adSiteContact();
});
$app->get('/leadmobi/', function (Request $request, Response $response) use ($app) {
    $segmentId = LeadMobi::createSegment(MAUTIC_S_NAME_C, MAUTIC_S_SLUG_C, MAUTIC_S_DESC_C);
    $contactId = LeadMobi::createContact("my Name", "Last", "teste@mmm.com");
    $res = LeadMobi::addContactToSegment($contactId, $segmentId);
    //var_dump($res);
});
$app->run();



