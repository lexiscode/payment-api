<?php

namespace App\Controllers;

use Slim\App;
use Monolog\Logger;
use App\Model\Auth;
use Firebase\JWT\JWT;
use App\Response\CustomResponse;
use Doctrine\ORM\EntityRepository;
use App\Validation\Validator;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;


class AuthController
{
    private $authRepository;
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->authRepository = $container->get(EntityRepository::class);
        $this->logger = $container->get(Logger::class);
    }

    public function authLogin(Request $request, Response $response): Response
    {
        // Get the JSON content from the request body
        $jsonBody = $request->getBody();
        $data = json_decode($jsonBody, true);

        // Check if JSON decoding was successful
        if ($data === null) {
            $errorResponse = array("error-message" => "Invalid JSON data");
            $this->logger->info('Status 400: Invalid JSON data (Bad request).');
            return CustomResponse::respondWithError($response, $errorResponse, 400);
        }

        $email = htmlspecialchars($data['email']);
        $password = htmlspecialchars($data['password']);

        // Instantiate Validator and CustomResponse classes
        $validator = new Validator();
        $customResponse = new CustomResponse();

        // Validate input data using the $validator
        $validator->validate($request, [
            "email" => v::notEmpty()->email(),
            "password" => v::notEmpty()
        ]);

        // If validation fails, return a 400 error response
        if ($validator->failed()) {
            $responseMessage = $validator->errors;

            $this->logger->info('Status 400: Failed validation (Bad request).');
            return $customResponse->is400Response($response, $responseMessage);
        }

        // Attempt authentication using the repository
        $authenticatedUser = $this->authRepository->authLogin($email, $password);

        if ($authenticatedUser === null) {
            $errorResponse = array(
                "status" => 401,
                "message" => "Your login credentials are invalid."
            );
            $this->logger->info('Status 401: Invalid login credentials (Unauthorized).');
            return $customResponse->respondWithError($response, $errorResponse, 401);
        }

        // Attempt authentication using the repository
        $authenticatedUser = $this->authRepository->authLogin($email, $password);

        if ($authenticatedUser === null) {
            $errorResponse = array(
                "status" => 401,
                "message" => "Your login credentials are invalid."
            );
            $this->logger->info('Status 401: Invalid login credentials (Unauthorized).');
            return $customResponse->respondWithError($response, $errorResponse, 401);
        }

        // Authentication successful, generate JWT token
        $secret_key = "SpslTAT3s09W9LjOgt9LQ7VTpSYsZoGD5Zcg0oK3x5U=";
        $payload = [
            "email" => $email,
            "exp" => time() + 7200, // Token expiration time (2 hours)
        ];
        $algorithm = 'HS256';
        $token = JWT::encode($payload, $secret_key, $algorithm);

        // Include the token in the response message
        $successResponse = array(
            "status" => 200,
            "message" => "You've logged in successfully.",
            "token" => $token,
            "user" => [
                "id" => $authenticatedUser->getId(),
                "email" => $authenticatedUser->getEmail()
            ]
        );

        return $customResponse->respondWithData($response, $successResponse, 200);
    }

    public function authRegister(Request $request, Response $response): Response
    {
        // Get the JSON content from the request body
        $jsonBody = $request->getBody();
        $data = json_decode($jsonBody, true);

        // Check if JSON decoding was successful
        if ($data === null) {
            $errorResponse = array("error-message" => "Invalid JSON data");
            $this->logger->info('Status 400: Invalid JSON data (Bad request).');
            return CustomResponse::respondWithError($response, $errorResponse, 400);
        }

        $email = htmlspecialchars($data['email']);
        $password = htmlspecialchars($data['password']);

        // Instantiate Validator and CustomResponse classes
        $validator = new Validator();
        $customResponse = new CustomResponse();

        // Validate input data using the $validator
        $validator->validate($request, [
            "email" => v::notEmpty()->email(),
            "password" => v::notEmpty()
        ]);

        // If validation fails, return a 400 response with the validation errors
        if ($validator->failed()) {
            $responseMessage = $validator->errors;

            $this->logger->info('Status 400: Failed validation (Bad request).');
            return $customResponse->is400Response($response, $responseMessage);
        }

        // Check if the email is already registered
        if ($this->authRepository->emailExists($email)) {
            $errorResponse = array(
                "status" => 400,
                "message" => "Email already registered"
            );

            $this->logger->info('Status 400: Email already registered (Bad request).');
            return $customResponse->respondWithError($response, $errorResponse, 400);
        }

        // Attempt registration using the repository
        $isRegistered = $this->authRepository->authRegister($email, $password);

        if ($isRegistered) {
            $successResponse = array(
                "status" => 200,
                "message" => "User registration successful."
            );
            return $customResponse->respondWithData($response, $successResponse, 200);
        } else {
            $errorResponse = array(
                "status" => 500,
                "message" => "An internal server error occurred while processing your request."
            );
            $this->logger->info('Status 500: Internal Server Error.');
            return $customResponse->respondWithError($response, $errorResponse, 500);
        }
    }
}
