<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Doctrine\ORM\EntityManager;
use Laminas\Diactoros\Response\JsonResponse;


class ReferenceController
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getCategoriesByName(Request $request, Response $response, array $args): Response
    {
        try {
            $name = $request->getAttribute('string');

            if (empty($name)) {
                $responseData = [
                    "success" => false,
                    "message" => "Parameter 'name' is required.",
                ];
                return new JsonResponse($responseData, 400);
            }

            $query = $this->em->createQuery('SELECT c FROM App\Model\Category c WHERE c.name LIKE :name');
            $query->setParameter('name', '%' . $name . '%');
            $categories = $query->getResult();

            $categoryData = array_map(function($category) {
                return [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'description' => $category->getDescription(),
                ];
            }, $categories);

            $responseData = [
                "success" => true,
                "category-found" => count($categories),
                "categories" => $categoryData,
            ];

            return new JsonResponse($responseData, 200);
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            error_log($e->getMessage());

            $responseData = [
                "success" => false,
                "message" => "An error occurred while fetching categories",
            ];

            return new JsonResponse($responseData, 500);
        }
    }

    public function getCategoryById(Request $request, Response $response, array $args): Response
    {
        try {
            $id = htmlspecialchars($args['id']);

            if (empty($id)) {
                $responseData = [
                    "success" => false,
                    "message" => "Parameter 'id' is required.",
                ];
                return new JsonResponse($responseData, 400);
            }

            $qb = $this->em->createQueryBuilder();
            $qb->select('c.id', 'c.name', 'c.description')
                ->from('App\Model\Category', 'c')
                ->where('c.id = :id')
                ->setParameter('id', $id);

            $query = $qb->getQuery();
            $categories = $query->getResult();

            if (empty($categories)) {
                $responseData = [
                    "success" => false,
                    "message" => "Category not found for the provided ID.",
                ];
                return new JsonResponse($responseData, 404);
            }

            $categoryData = $categories[0];

            $responseData = [
                "success" => true,
                "category-data" => $categoryData,
            ];

            return new JsonResponse($responseData, 200);
        } catch (\Exception $e) {
            // Handle exceptions and errors here
            error_log($e->getMessage());

            $responseData = [
                "success" => false,
                "message" => "An error occurred while fetching the category",
            ];

            return new JsonResponse($responseData, 500);
        }
    }
}
