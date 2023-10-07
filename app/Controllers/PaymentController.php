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
    
    public function getAllCustomers(Request $request, Response $response): Response
    {
        try{

            $customers = $this->customerRepository->findAll();

            $customerData = [];
            foreach ($customers as $customer) {
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

    public function getCategoryById(Request $request, Response $response, array $args): Response
    {
        try{

            $id = htmlspecialchars($args['id']);
            $customer = $this->customerRepository->findById($id);

            if ($customer === null) {

                $this->logger->info("Status 404: Customer not found with this id:$id");
                return new JsonResponse(['message' => 'Customer not found'], 404);
            }

            $customerData = $customer->toArray();

            $response->getBody()->write(json_encode($customerData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    public function createCategory(Request $request, Response $response): Response
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


    public function putCategory(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $customerInfo = json_decode($jsonBody, true);

            if ($id > 0) {
                $customerRepository = $this->customerRepository;
                $customer = $customerRepository->findById($id);

                if (is_null($customer)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource category not found",
                        "status" => 404,
                        "path" => "/v1/customers/$id"
                    ];

                    $this->logger->info("Status 404: Customer not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $customer->setName($customerInfo['name']);
                $customer->setDescription($customerInfo['description']);

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
                    "message" => "Bad request. Please provide a valid category ID.",
                    "status" => 400,
                    "path" => "/v1/customers/$id"
                ];

                $this->logger->info("Status 400: Bad Request", $responseData);
                return new JsonResponse($responseData, 400);
            }
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    public function patchCategory(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $customerInfo = json_decode($jsonBody, true);

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
                    $customer->setName($customerInfo['name']);
                }

                if (isset($customerInfo['address'])) {
                    $customer->setDescription($customerInfo['address']);
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
                    "message" => "Bad request. Please provide a valid category ID.",
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

    public function deleteCategory(array $args): Response
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
                    "message" => "Bad request. Please provide a valid category ID.",
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
