<?php

namespace App\Middleware;

use Doctrine\ORM\Exception\ORMException;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Request;
use Throwable;
use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use App\Exception\DBException;

class CustomErrorHandler
{
    private Logger $logger;

    public function __construct(private App $app)
    {
        // injecting the Logger interface
        $this->logger = $this->app->getContainer()->get(Logger::class);
    }

    public function __invoke(
        ServerRequest    $request, 
        Throwable        $exception,
        bool             $displayErrorDetails,
        bool             $logErrors,
        bool             $logErrorDetails,
        ?LoggerInterface $logger = null
    )
    {

        $statusCode = 500; // Default status code
        
        $logger?->error($exception->getMessage());

        if ($exception instanceof ORMException || $exception instanceof \PDOException) {
            $this->logger->critical($exception->getMessage());
            $statusCode = 500;
        } else if($exception instanceof HttpNotFoundException){
            $this->logger->critical($exception->getMessage());
            $statusCode = 404;
        }else if($exception instanceof DBException) {
            $this->logger->alert($exception->getMessage());
            $statusCode = $exception->getCode();
        }

        $payload = [
            'message' => $exception->getMessage()
        ];

        if ($displayErrorDetails) {
            $payload['details'] = $exception->getMessage();
            $payload['trace'] = $exception->getTrace();
        }

        $response = $this->app->getResponseFactory()->createResponse();
        $response->getBody()->write(
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );

        return $response->withStatus($statusCode);
    }
}

