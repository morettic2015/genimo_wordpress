<?php

//header("Access-Control-Allow-Origin: *");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//Import Libs
require './vendor/autoload.php';
include_once './config.php';
include_once './GenimoWordpress.php';
//require 'GuiaSynchronize.php';
//Init Objects
$app = new \Slim\App;
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

$app->run();



