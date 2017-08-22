<?php

//header("Access-Control-Allow-Origin: *");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

//Import Libs
require './vendor/autoload.php';
require 'GenimoWordpress.php';
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
    echo "INIT IMPORT PROCESS FROM PROPERTY".$idProperty."<br>";
    GenimoWordpress::syncProperty($idCompany,$idProperty);
    echo "FINISH IMPORT PROCESS FROM PROPERTY".$idProperty."<br>";
});

$app->run();



