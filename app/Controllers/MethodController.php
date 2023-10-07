<?php

namespace App\Controllers;

use Slim\App;
use Monolog\Logger;
use App\Model\Method;
use App\Exception\DBException;
use Psr\Container\ContainerInterface;
use App\Repositories\MethodRepository;
use Slim\Exception\HttpNotFoundException;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MethodController
{
    private $methodRepository;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->methodRepository = $container->get(MethodRepository::class);
        $this->logger = $container->get(Logger::class);
    }
    
    public function getAllMethods(Request $request, Response $response): Response
    {
        try{

            $methods = $this->methodRepository->findAll();

            $methodData = [];
            foreach ($methods as $method) {
                $methodData[] = $method->toArray();
            }

            $response->getBody()->write(json_encode($methodData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

            $responseData = [
                "success" => false,
                "error" => "Internal Server Error",
                "message" => "An error occurred while creating the method",
                "status" => 500,
                "path" => "/v1/methods"
            ];

            return new JsonResponse($responseData, 500);
        }
        
    }

    public function getMethodById(Request $request, Response $response, array $args): Response
    {
        try{

            $id = htmlspecialchars($args['id']);
            $method = $this->methodRepository->findById($id);

            if ($method === null) {

                $this->logger->info("Status 404: Method not found with this id:$id");
                return new JsonResponse(['message' => 'Method not found'], 404);
            }

            $methodData = $method->toArray();

            $response->getBody()->write(json_encode($methodData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    public function createMethod(Request $request, Response $response): Response
    {
        try {
            $jsonBody = $request->getBody();
            $methodInfo = json_decode($jsonBody, true);

            if (!$methodInfo || !isset($methodInfo['name'])) {
                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/methods"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            $method = new Method();
            $method->setName($methodInfo['name']);

            $methodRepository = $this->methodRepository;
            $methodRepository->store($method);

            $responseData = [
                "success" => true,
                "message" => "Method has been created successfully",
                "status" => 200,
                "path" => "/v1/methods"
            ];
            return new JsonResponse($responseData, 200);

        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }


    public function putMethod(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $methodInfo = json_decode($jsonBody, true);

            if ($id > 0) {
                $methodRepository = $this->methodRepository;
                $method = $methodRepository->findById($id);

                if (is_null($method)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource method not found",
                        "status" => 404,
                        "path" => "/v1/methods/$id"
                    ];

                    $this->logger->info("Status 404: Method not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $method->setName($methodInfo['name']);

                $methodRepository->update($method);

                $responseData = [
                    "success" => true,
                    "message" => "Method has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/methods/$id"
                ];

                return new JsonResponse($responseData, 200);
            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. Please provide a valid method ID.",
                    "status" => 400,
                    "path" => "/v1/methods/$id"
                ];

                $this->logger->info("Status 400: Bad Request", $responseData);
                return new JsonResponse($responseData, 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    public function patchMethod(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $methodInfo = json_decode($jsonBody, true);

            if ($id > 0) {
                $methodRepository = $this->methodRepository;
                $method = $methodRepository->findById($id);

                if (is_null($method)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource method not found",
                        "status" => 404,
                        "path" => "/v1/method/$id"
                    ];

                    $this->logger->info("Status 404: Method not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                // Partially update category properties if provided in the request
                if (isset($methodInfo['name'])) {
                    $method->setName($methodInfo['name']);
                }

                if (isset($methodInfo['address'])) {
                    $method->setDescription($methodInfo['address']);
                }

                $methodRepository->update($method);

                $responseData = [
                    "success" => true,
                    "message" => "Method has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/methods/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. Please provide a valid method ID.",
                    "status" => 400,
                    "path" => "/v1/methods/$id"
                ];

                $this->logger->info("Status 400: Bad Request.", $responseData);
                return new JsonResponse($responseData, 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);
        }
    }

    public function deleteMethod(array $args): Response
    {
        try {

            $id = htmlspecialchars($args['id']);

            if ($id > 0) {
                $methodRepository = $this->methodRepository;
                $method = $methodRepository->findById($id);

                if (is_null($method)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource method not found",
                        "status" => 404,
                        "path" => "/v1/method/$id"
                    ];

                    $this->logger->info("Status 404: Method not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $methodRepository->remove($method);

                $responseData = [
                    "success" => true,
                    "message" => "Method has been deleted successfully.",
                    "status" => 200,
                    "path" => "/v1/methods/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. Please provide a valid method ID.",
                    "status" => 400,
                    "path" => "/v1/methods/$id"
                ];

                $this->logger->info("Status 400: Bad Request!", $responseData);
                return new JsonResponse($responseData, 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new \Exception('Internal Server Error', 500);

        }
    }

}
