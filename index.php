<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;

require_once './vendor/autoload.php';
require_once './Clases/AccesoDatos.php';
require_once './Clases/Usuario.php';
require_once './Clases/Media.php';
require_once './Clases/MW.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

//*********************************************************************************************//
//INICIALIZO EL APIREST
//*********************************************************************************************//
$app = new \Slim\App(["settings" => $config]);
//*********************************************************************************************//

$app->post('/', \Media::class .'::AltaDeMediaDBAPI');

$app->get('/', \Usuario::class .'::TraerTodosLosUsuariosAPI');

$app->put('/', \Media::class .'::ModificarMediaPorIDAPI')->add(\MW::class . ':VerificarEncargado'
                                                        )->add(\MW::class . ':VerificarToken');

$app->delete('/', \Media::class .'::BorrarMediaPorIDAPI')->add(\MW::class . '::VerificarPropietario'
                                                        )->add(\MW::class . ':VerificarToken');

$app->get('/medias', \Media::class .'::TraerTodasLasMediasAPI');

$app->post('/usuarios', \Usuario::class .'::AltaDeUsuarioDBAPI');

$app->group('/login', function () {

    $this->post('/', \Usuario::class .'::GenerarJWTAPI')->add(\MW::class . '::VerificarExistencia'
                    )->add(\MW::class . '::VerificarVacios')->add(\MW::class . ':VerificarSeteo');
 
    $this->get('/', \Usuario::class .'::VerificarJWTAPI');
     
});

$app->group('/listados', function () {

    $this->get('/datos', \MW::class . ':MostrarDatosLimitados')->add(\MW::class . ':VerificarEncargado'
                                                        )->add(\MW::class . ':VerificarToken');
 
    $this->get('/colores', \MW::class .'::MostrarColoresDistintos')->add(\MW::class . ':VerificarEncargado'
                                                                    )->add(\MW::class . ':VerificarToken');

    $this->get('/[{id}]', \MW::class .'::MostrarPorID')->add(\MW::class . '::VerificarPropietario'
                                                            )->add(\MW::class . ':VerificarToken');
     
});



$app->run();