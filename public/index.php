<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Slim\Factory\AppFactory;
use App\Middleware\CustomErrorHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/container.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->safeLoad();


$app = AppFactory::create(container: $container);
$container = $app->getContainer(); // used in the normal Doctrine queries


$app->get('/', function (Request $request, Response $response, array $args) {
    $name = 'Welcome to Lexis Payment-API!';
    $response->getBody()->write("Hello! $name");
    return $response;
});


// Define routes using controllers
require "../routes/api.php";


// Default slim error message four route that doesn't exists
$displayErrors = $_ENV['APP_ENV'] != 'production';
$errorMiddleware = $app->addErrorMiddleware($displayErrors, true, true);

// To setup customer logger error handling
$customErrorHandler = new CustomErrorHandler($app);
$errorMiddleware->setDefaultErrorHandler($customErrorHandler);


$app->run();

