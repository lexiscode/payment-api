<?php

namespace App\Controllers;

use Slim\App;
use Monolog\Logger;
use App\Model\Payment;
use App\Exception\DBException;
use Psr\Container\ContainerInterface;
use App\Repositories\PaymentRepository;
use Slim\Exception\HttpNotFoundException;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PaymentController
{
    private $paymentRepository;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->paymentRepository = $container->get(PaymentRepository::class);
        $this->logger = $container->get(Logger::class);
    }
    
    /**
     * @OA\Get(
     *     path="/v1/payments",
     *     summary="Get all payments",
     *     tags={"Payments"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response containing all payments data",
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
    public function getAllPayments(Request $request, Response $response): Response
    {
        try{

            $payments = $this->paymentRepository->findAll();

            $paymentData = [];
            foreach ($payments as $payment) {
                $paymentData[] = $payment->toArray();
            }

            $response->getBody()->write(json_encode($paymentData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

            $responseData = [
                "success" => false,
                "error" => "Internal Server Error",
                "message" => "An error occurred while creating the payment.",
                "status" => 500,
                "path" => "/v1/payments"
            ];

            return new JsonResponse($responseData, 500);
        }
        
    }

     /**
     * @OA\Get(
     *     path="/v1/payments/{id}",
     *     summary="Get a payment data by its ID",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment data",
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
    public function getPaymentById(Request $request, Response $response, array $args): Response
    {
        try{

            $id = htmlspecialchars($args['id']);
            $payment = $this->paymentRepository->findById($id);

            if ($payment === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Payment data not found",
                    "status" => 404,
                    "path" => "/v1/payments/$id"
                ];

                $this->logger->info("Status 404: Payment not found with this id:$id");
                return new JsonResponse($responseData, 404);
            }

            $paymentData = $payment->toArray();

            $response->getBody()->write(json_encode($paymentData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

        }
    }

    /**
     * @OA\Post(
     *     path="/v1/payments",
     *     summary="Create a new payment",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="sum", type="float", example="Payment Sum")
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with payment creation status",
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
    public function createPayment(Request $request, Response $response): Response
    {
        try {
            $jsonBody = $request->getBody();
            $paymentInfo = json_decode($jsonBody, true);

            if (!$paymentInfo || !isset($paymentInfo['sum'])) {
                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/payments"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            $paymentRequestBody = filter_var($paymentInfo['sum'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);;
            
            $payment = new Payment();
            $payment->setSum($paymentRequestBody);

            $paymentRepository = $this->paymentRepository;
            $paymentRepository->store($payment);

            $responseData = [
                "success" => true,
                "message" => "Payment has been created successfully",
                "status" => 200,
                "path" => "/v1/payments"
            ];
            return new JsonResponse($responseData, 200);

        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/v1/payments/{id}",
     *     summary="Update all data of a specific payment by its ID",
     *     tags={"Methods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="sum", type="float", example="Updated Payment Sum"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with payment update status",
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
    public function putPayment(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $paymentInfo = json_decode($jsonBody, true);

            // Check if JSON decoding was valid and successful
            if ($paymentInfo === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/payments"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            if ($id > 0) {
                $paymentRepository = $this->paymentRepository;
                $payment = $paymentRepository->findById($id);

                if (is_null($payment)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Payment resource not found",
                        "status" => 404,
                        "path" => "/v1/payments/$id"
                    ];

                    $this->logger->info("Status 404: Payment not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                if (!is_float($paymentInfo['sum']) && !is_integer($paymentInfo['sum'])){
                    $errorResponse = [
                        "success" => false,
                        "message" => "Request must be either a float or an integer only.",
                        "status" => 400,
                        "path" => "/v1/payments"
                    ];

                    $this->logger->alert("Status 400: Bad Request", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $paymentRequestBody = filter_var($paymentInfo['sum'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $payment->setSum($paymentRequestBody);

                $paymentRepository->update($payment);

                $responseData = [
                    "success" => true,
                    "message" => "Payment has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/payments/$id"
                ];

                return new JsonResponse($responseData, 200);
            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. ID must be greater than zero.",
                    "status" => 400,
                    "path" => "/v1/payments/$id"
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
     *     path="/payments/{id}",
     *     summary="Update all or a part of a specific method by its ID",
     *     tags={"Payments"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="sum", type="float", example="Updated Payment Sum"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with payment update status",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request or invalid JSON data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found with this ID",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function patchPayment(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);
            $jsonBody = $request->getBody();
            $paymentInfo = json_decode($jsonBody, true);

            // Check if JSON decoding was valid and successful
            if ($paymentInfo === null) {

                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/payments"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            if ($id > 0) {
                $paymentRepository = $this->paymentRepository;
                $payment = $paymentRepository->findById($id);

                if (is_null($payment)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Payment resource not found",
                        "status" => 404,
                        "path" => "/v1/payment/$id"
                    ];

                    $this->logger->info("Status 404: Payment not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                // Partially update category properties if provided in the request
                if (isset($paymentInfo['sum'])) {

                    if (!is_float($paymentInfo['sum']) && !is_integer($paymentInfo['sum'])){
                        $errorResponse = [
                            "success" => false,
                            "message" => "Request must be either a float or an integer only.",
                            "status" => 400,
                            "path" => "/v1/payments"
                        ];
    
                        $this->logger->alert("Status 400: Bad Request", $errorResponse);
                        return new JsonResponse($errorResponse, 400);
                    }

                    $paymentRequestBody = filter_var($paymentInfo['sum'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);;
                    $payment->setSum($paymentRequestBody);
                }

                $paymentRepository->update($payment);

                $responseData = [
                    "success" => true,
                    "message" => "Payment has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/payments/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. Please provide a valid payment ID.",
                    "status" => 400,
                    "path" => "/v1/payments/$id"
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
     *     path="/v1/payments/{id}",
     *     summary="Delete a specific payment by its ID",
     *     tags={"Methods"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the payment to be deleted",
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
    public function deletePayment(Request $request, Response $response, array $args): Response
    {
        try {

            $id = htmlspecialchars($args['id']);

            if ($id > 0) {
                $paymentRepository = $this->paymentRepository;
                $payment = $paymentRepository->findById($id);

                if (is_null($payment)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Payment resource not found",
                        "status" => 404,
                        "path" => "/v1/payments/$id"
                    ];

                    $this->logger->info("Status 404: Payment not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $paymentRepository->remove($payment);

                $responseData = [
                    "success" => true,
                    "message" => "Payment has been deleted successfully.",
                    "status" => 200,
                    "path" => "/v1/payments/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. ID must be greater than zero.",
                    "status" => 400,
                    "path" => "/v1/payments/$id"
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

