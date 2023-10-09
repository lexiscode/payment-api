<?php

namespace App\Controllers;

use Slim\App;
use Monolog\Logger;
use App\Model\Customer;
use App\Exception\DBException;
use Psr\Container\ContainerInterface;
use App\Repositories\CustomerRepository;
use Slim\Exception\HttpNotFoundException;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CustomerController
{
    private $customerRepository;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->customerRepository = $container->get(CustomerRepository::class);
        $this->logger = $container->get(Logger::class);
    }

    /**
     * @OA\Put(
     *     path="/v1/customers/activate/{status}",
     *     summary="Activate or deactivate customers",
     *     description="Activate or deactivate all customers based on the provided status.",
     *     operationId="activateCustomers",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Status to activate (1) or deactivate (0) customers",
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
    public function activateCustomers(Request $request, Response $response, array $args): Response
    {
        try {
            $status = (int)$args['status'];

            if ($status !== 0 && $status !== 1) {
                return new JsonResponse(['message' => 'Invalid status value. Use 0 for deactivation or 1 for activation.'], 400);
            }

            // Update the status of all customers
            $customers = $this->customerRepository->findAll();

            foreach ($customers as $customer) {
                $customer->setIsActive($status);
                $this->customerRepository->update($customer);
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
     *     path="/v1/customers",
     *     summary="Get all customers",
     *     tags={"Customers"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing all customers data",
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
    public function getAllCustomers(Request $request, Response $response): Response
    {
        try{

            $customers = $this->customerRepository->findAll();

            $customerData = [];
            foreach ($customers as $customer) {
                // Check if the customer is active before including it in the response
                if (!$customer->isActive()) {
                    return new JsonResponse(['message' => 'This customer route is deactivated'], 403);
                }
                $customerData[] = $customer->toArray();
            }

            $response->getBody()->write(json_encode($customerData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

            $responseData = [
                "success" => false,
                "error" => "Internal Server Error",
                "message" => "An error occurred while creating the customer",
                "status" => 500,
                "path" => "/v1/customers"
            ];

            return new JsonResponse($responseData, 500);
        }
        
    }

    /**
     * @OA\Get(
     *     path="/v1/customers/{id}",
     *     summary="Get a customer data by its ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer data",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing the customer data",
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
    public function getCustomerById(Request $request, Response $response, array $args): Response
    {
        try{

            $id = htmlspecialchars($args['id']);
            $customer = $this->customerRepository->findById($id);

            if ($customer === null) {

                $this->logger->info("Status 404: Customer not found with this id:$id");
                return new JsonResponse(['message' => 'Customer not found'], 404);
            }

            if (!$customer->isActive()) {
                return new JsonResponse(['message' => 'This customer route is deactivated'], 403);
            }

            $customerData = $customer->toArray();

            $response->getBody()->write(json_encode($customerData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    /**
     * @OA\Post(
     *     path="/v1/customers",
     *     summary="Create a new customer",
     *     tags={"Customers"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Customer Name"),
     *                 @OA\Property(property="address", type="string", example="Customer Address"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with customer creation status",
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
    public function createCustomer(Request $request, Response $response): Response
    {
        try {
            $jsonBody = $request->getBody();
            $customerInfo = json_decode($jsonBody, true);

            if (!$customerInfo || !isset($customerInfo['name']) || !isset($customerInfo['address'])) {
                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/customers"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            $customer = new Customer();
            $customer->setName($customerInfo['name']);
            $customer->setAddress($customerInfo['address']);

            $customerRepository = $this->customerRepository;
            $customerRepository->store($customer);

            $responseData = [
                "success" => true,
                "message" => "Customer has been created successfully",
                "status" => 200,
                "path" => "/v1/customers"
            ];
            return new JsonResponse($responseData, 200);

        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    /**
     * @OA\Put(
     *     path="/v1/customers/{id}",
     *     summary="Update all data of a specific customer by its ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Customer ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Updated Customer Name"),
     *                 @OA\Property(property="address", type="string", example="Updated Customer Address"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with customer update status",
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
    public function putCustomer(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $customerInfo = json_decode($jsonBody, true);

            // Check if JSON decoding was valid and successful
            if ($customerInfo === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/customers"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            if ($id > 0) {
                $customerRepository = $this->customerRepository;
                $customer = $customerRepository->findById($id);

                if (is_null($customer)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Customer resource not found",
                        "status" => 404,
                        "path" => "/v1/customers/$id"
                    ];

                    $this->logger->info("Status 404: Customer not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $customer->setName($customerInfo['name']);
                $customer->setAddress($customerInfo['address']);

                $customerRepository->update($customer);

                $responseData = [
                    "success" => true,
                    "message" => "Customer has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/customers/$id"
                ];

                return new JsonResponse($responseData, 200);
            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. ID must be greater than zero.",
                    "status" => 400,
                    "path" => "/v1/customers/$id"
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
     *     path="/customers/{id}",
     *     summary="Update all or a part of a specific customer by its ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Customer ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="name", type="string", example="Updated Category Name"),
     *                 @OA\Property(property="address", type="string", example="Updated Customer Address"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with customer update status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found with this ID",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function patchCustomer(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $customerInfo = json_decode($jsonBody, true);

            // Check if JSON decoding was valid and successful
            if ($customerInfo === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/customers"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            if ($id > 0) {
                $customerRepository = $this->customerRepository;
                $customer = $customerRepository->findById($id);

                if (is_null($customer)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource customer not found",
                        "status" => 404,
                        "path" => "/v1/customer/$id"
                    ];

                    $this->logger->info("Status 404: Customer not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                // Partially update category properties if provided in the request
                if (isset($customerInfo['name'])) {

                    if (!is_string($customerInfo['name'])){
                        $errorResponse = [
                            "success" => false,
                            "message" => "Request must be of type 'string'.",
                            "status" => 400,
                            "path" => "/v1/customers"
                        ];
    
                        $this->logger->alert("Status 400: Bad Request", $errorResponse);
                        return new JsonResponse($errorResponse, 400);
                    }

                    $customer->setName($customerInfo['name']);
                }

                if (isset($customerInfo['address'])) {

                    if (!is_string($customerInfo['address'])){
                        $errorResponse = [
                            "success" => false,
                            "message" => "Request must be of type 'string'.",
                            "status" => 400,
                            "path" => "/v1/customers"
                        ];
    
                        $this->logger->alert("Status 400: Bad Request", $errorResponse);
                        return new JsonResponse($errorResponse, 400);
                    }

                    $customer->setAddress($customerInfo['address']);
                }

                $customerRepository->update($customer);

                $responseData = [
                    "success" => true,
                    "message" => "Customer has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/customers/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. ID must be greater than zero.",
                    "status" => 400,
                    "path" => "/v1/customers/$id"
                ];

                $this->logger->info("Status 400: Bad Request.", $responseData);
                return new JsonResponse($responseData, 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/v1/customers/{id}",
     *     summary="Delete a specific customer by its ID",
     *     tags={"Customers"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer to be deleted",
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
    public function deleteCustomer(Request $request, Response $response, array $args): Response
    {
        try {

            $id = htmlspecialchars($args['id']);

            if ($id > 0) {
                $customerRepository = $this->customerRepository;
                $customer = $customerRepository->findById($id);

                if (is_null($customer)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource customer not found",
                        "status" => 404,
                        "path" => "/v1/customer/$id"
                    ];

                    $this->logger->info("Status 404: Customer not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                // Check if the method is deactivated
                if (!$customer->isActive()) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Cannot delete a deactivated method",
                        "status" => 403,
                        "path" => "/v1/customers/$id"
                    ];

                    $this->logger->info("Status 403: Cannot delete a deactivated method", $errorResponse);
                    return new JsonResponse($errorResponse, 403);
                }

                $customerRepository->remove($customer);

                $responseData = [
                    "success" => true,
                    "message" => "Customer has been deleted successfully.",
                    "status" => 200,
                    "path" => "/v1/customers/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. ID must be greater than zero.",
                    "status" => 400,
                    "path" => "/v1/customers/$id"
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

