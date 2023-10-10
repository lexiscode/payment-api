<?php

namespace App\Controllers;

use Slim\App;
use Monolog\Logger;
use App\Model\Method;
use App\Exception\DBException;
use Psr\Container\ContainerInterface;
use App\Repositories\MethodRepository;
use Slim\Exception\HttpNotFoundException;
use App\Validation\Validator;
use Respect\Validation\Validator as v;
use App\Response\CustomResponse;

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

    /**
     * @OA\Put(
     *     path="/v1/methods/activate/{status}",
     *     summary="Activate or deactivate methods",
     *     description="Activate or deactivate all payment methods based on the provided status.",
     *     operationId="activateMethods",
     *     tags={"Methods"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Status to activate (1) or deactivate (0) methods",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             enum={0, 1},
     *             default=1,
     *         ),
     *         style="simple",
     *         explode=false,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success message",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Activated/Deactivated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid status value",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid status value. Use 0 for deactivation or 1 for activation.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function activateMethods(Request $request, Response $response, array $args): Response
    {
        try {
            $status = (int)$args['status'];

            if ($status !== 0 && $status !== 1) {
                return new JsonResponse(['message' => 'Invalid status value. Use 0 for deactivation or 1 for activation.'], 400);
            }

            // Update the status of all methods
            $methods = $this->methodRepository->findAll();

            foreach ($methods as $method) {
                $method->setIsActive($status);
                $this->methodRepository->update($method);
            }

            $message = $status === 1 ? 'Activated successfully.' : 'Deactivated successfully.';
            return new JsonResponse(['message' => $message], 200);
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/methods",
     *     summary="Get all methods",
     *     tags={"Methods"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing all methods data",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden Route Access",
     *     )
     * )
     */
    public function getAllMethods(Request $request, Response $response): Response
    {
        try{

            $methods = $this->methodRepository->findAll();

            $methodData = [];
            foreach ($methods as $method) {
                // Check if the method is active before including it in the response
                if (!$method->isActive()) {
                    return new JsonResponse(['message' => 'This method route is deactivated'], 403);
                }
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

    /**
     * @OA\Get(
     *     path="/v1/methods/{id}",
     *     summary="Get a method data by its ID",
     *     tags={"Methods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the method data",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing the method data",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden Route Access",
     *     )
     * 
     * )
     */
    public function getMethodById(Request $request, Response $response, array $args): Response
    {
        try{

            $id = htmlspecialchars($args['id']);
            $method = $this->methodRepository->findById($id);

            if ($method === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Method data not found",
                    "status" => 404,
                    "path" => "/v1/methods/$id"
                ];

                $this->logger->info("Status 404: Method not found with this id:$id");
                return new JsonResponse($responseData, 404);
            }

            if (!$method->isActive()) {
                return new JsonResponse(['message' => 'This method route is deactivated'], 403);
            }

            $methodData = $method->toArray();

            $response->getBody()->write(json_encode($methodData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    /**
     * @OA\Post(
     *     path="/v1/methods",
     *     summary="Create a new method",
     *     tags={"Methods"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Customer Name")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with method creation status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
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

            $methodNameRequestBody = htmlspecialchars($methodInfo['name']);

            // Instantiate Validator and CustomResponse classes
            $validator = new Validator();
            $customResponse = new CustomResponse();

            // Validate input data using the $validator
            $validator->validate($request, [
                "name" => v::notEmpty()
            ]);

            // If validation fails, return a 400 error response
            if ($validator->failed()) {
                $responseMessage = $validator->errors;

                $this->logger->info('Status 400: Failed validation (Bad request).');
                return $customResponse->is400Response($response, $responseMessage);
            }

            $method = new Method();
            $method->setName($methodNameRequestBody);

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

    /**
     * @OA\Put(
     *     path="/v1/methods/{id}",
     *     summary="Update all data of a specific method by its ID",
     *     tags={"Methods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Method ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Updated Method Name"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with method update status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found with this ID",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function putMethod(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $methodInfo = json_decode($jsonBody, true);

            // Check if JSON decoding was valid and successful
            if ($methodInfo === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/methods"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

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

                $methodNameRequestBody = htmlspecialchars($methodInfo['name']);

                // Instantiate Validator and CustomResponse classes
                $validator = new Validator();
                $customResponse = new CustomResponse();

                // Validate input data using the $validator
                $validator->validate($request, [
                    "name" => v::notEmpty()
                ]);

                // If validation fails, return a 400 error response
                if ($validator->failed()) {
                    $responseMessage = $validator->errors;

                    $this->logger->info('Status 400: Failed validation (Bad request).');
                    return $customResponse->is400Response($response, $responseMessage);
                }

                $method->setName($methodNameRequestBody);

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
                    "message" => "Bad request. ID must be greater than zero.",
                    "status" => 400,
                    "path" => "/v1/methods/$id"
                ];

                $this->logger->alert("Status 400: Bad Request", $responseData);
                return new JsonResponse($responseData, 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    /**
     * @OA\Patch(
     *     path="/methods/{id}",
     *     summary="Update all or a part of a specific method by its ID",
     *     tags={"Methods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Method ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Updated Method Name"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with method update status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Method not found with this ID",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function patchMethod(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $methodInfo = json_decode($jsonBody, true);

            // Check if JSON decoding was valid and successful
            if ($methodInfo === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/methods"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

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

                    if (!is_string($methodInfo['name'])){
                        $errorResponse = [
                            "success" => false,
                            "message" => "Request must be of type 'string'.",
                            "status" => 400,
                            "path" => "/v1/customers"
                        ];
    
                        $this->logger->alert("Status 400: Bad Request", $errorResponse);
                        return new JsonResponse($errorResponse, 400);
                    }

                    $methodNameRequestBody = htmlspecialchars($methodInfo['name']);

                    // Instantiate Validator and CustomResponse classes
                    $validator = new Validator();
                    $customResponse = new CustomResponse();

                    // Validate input data using the $validator
                    $validator->validate($request, [
                        "name" => v::notEmpty()
                    ]);

                    // If validation fails, return a 400 error response
                    if ($validator->failed()) {
                        $responseMessage = $validator->errors;

                        $this->logger->info('Status 400: Failed validation (Bad request).');
                        return $customResponse->is400Response($response, $responseMessage);
                    }

                    $method->setName($methodNameRequestBody);
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
                    "message" => "Bad request. ID must be greater than zero.",
                    "status" => 400,
                    "path" => "/v1/methods/$id"
                ];

                $this->logger->alert("Status 400: Bad Request.", $responseData);
                return new JsonResponse($responseData, 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/methods/{id}",
     *     summary="Delete a specific method by its ID",
     *     tags={"Methods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the method to be deleted",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with delete status",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     )
     * )
     */
    public function deleteMethod(Request $request, Response $response, array $args): Response
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

                // Check if the method is deactivated
                if (!$method->isActive()) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Cannot delete a deactivated method",
                        "status" => 403,
                        "path" => "/v1/methods/$id"
                    ];

                    $this->logger->info("Status 403: Cannot delete a deactivated method", $errorResponse);
                    return new JsonResponse($errorResponse, 403);
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
