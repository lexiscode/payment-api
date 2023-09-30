<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repositories\CategoryRepository;
use Laminas\Diactoros\Response\JsonResponse;
use Slim\Exception\HttpNotFoundException;
use App\Model\Category;
use App\Exception\DBException;

use Monolog\Logger;
use Slim\App;

class CategoryController
{
    private $categoryRepository;
    private $logger;

    public function __construct(CategoryRepository $categoryRepository, Logger $logger)
    {
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
    }
    

    public function getAllCategories(Request $request, Response $response): Response
    {
        try{

            $categories = $this->categoryRepository->findAll();

            $categoryData = [];
            foreach ($categories as $category) {
                $categoryData[] = $category->toArray();
            }

            $response->getBody()->write(json_encode($categoryData));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            // Handle exceptions and errors here
            throw new DBException('Internal Server Error', 500);

            $responseData = [
                "success" => false,
                "error" => "Internal Server Error",
                "message" => "An error occurred while creating the category",
                "status" => 500,
                "path" => "/v1/category"
            ];

            return new JsonResponse($responseData, 500);
        }
        
    }

    public function getCategoryById(Request $request, Response $response, array $args): Response
    {
        try{

            $id = htmlspecialchars($args['id']);
            $category = $this->categoryRepository->findById($id);

            if ($category === null) {

                $this->logger->info("Status 404: Category not found with this id:$id");
                return new JsonResponse(['message' => 'Category not found'], 404);
            }

            $categoryData = $category->toArray();

            $response->getBody()->write(json_encode($categoryData));
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
            $categoryInfo = json_decode($jsonBody, true);

            if (!$categoryInfo || !isset($categoryInfo['name']) || !isset($categoryInfo['description'])) {
                $responseData = [
                    "success" => false,
                    "message" => "Invalid or incomplete JSON data",
                    "status" => 400,
                    "path" => "/v1/category"
                ];

                $this->logger->info('Status 400: Invalid JSON data (Bad request).', $responseData);
                return new JsonResponse($responseData, 400);
            }

            $category = new Category();
            $category->setName($categoryInfo['name']);
            $category->setDescription($categoryInfo['description']);

            $categoryRepository = $this->categoryRepository;
            $categoryRepository->store($category);

            $responseData = [
                "success" => true,
                "message" => "Category has been created successfully",
                "status" => 200,
                "path" => "/v1/category"
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
            $categoryInfo = json_decode($jsonBody, true);

            if ($id > 0) {
                $categoryRepository = $this->categoryRepository;
                $category = $categoryRepository->findById($id);

                if (is_null($category)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource category not found",
                        "status" => 404,
                        "path" => "/v1/category/$id"
                    ];

                    $this->logger->info("Status 404: Category not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $category->setName($categoryInfo['name']);
                $category->setDescription($categoryInfo['description']);

                $categoryRepository->update($category);

                $responseData = [
                    "success" => true,
                    "message" => "Category has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/category/$id"
                ];

                return new JsonResponse($responseData, 200);
            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. Please provide a valid category ID.",
                    "status" => 400,
                    "path" => "/v1/category/$id"
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
            $categoryInfo = json_decode($jsonBody, true);

            if ($id > 0) {
                $categoryRepository = $this->categoryRepository;
                $category = $categoryRepository->findById($id);

                if (is_null($category)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource category not found",
                        "status" => 404,
                        "path" => "/v1/category/$id"
                    ];

                    $this->logger->info("Status 404: Category not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                // Partially update category properties if provided in the request
                if (isset($categoryInfo['name'])) {
                    $category->setName($categoryInfo['name']);
                }

                if (isset($categoryInfo['description'])) {
                    $category->setDescription($categoryInfo['description']);
                }

                $categoryRepository->update($category);

                $responseData = [
                    "success" => true,
                    "message" => "Category has been updated successfully.",
                    "status" => 200,
                    "path" => "/v1/category/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. Please provide a valid category ID.",
                    "status" => 400,
                    "path" => "/v1/category/$id"
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
                $categoryRepository = $this->categoryRepository;
                $category = $categoryRepository->findById($id);

                if (is_null($category)) {
                    $errorResponse = [
                        "success" => false,
                        "message" => "Resource category not found",
                        "status" => 404,
                        "path" => "/v1/category/$id"
                    ];

                    $this->logger->info("Status 404: Category not found with this id:$id", $errorResponse);
                    return new JsonResponse($errorResponse, 404);
                }

                $categoryRepository->remove($category);

                $responseData = [
                    "success" => true,
                    "message" => "Category has been deleted successfully.",
                    "status" => 200,
                    "path" => "/v1/category/$id"
                ];

                return new JsonResponse($responseData, 200);

            } else {
                $responseData = [
                    "success" => false,
                    "message" => "Bad request. Please provide a valid category ID.",
                    "status" => 400,
                    "path" => "/v1/category/$id"
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

