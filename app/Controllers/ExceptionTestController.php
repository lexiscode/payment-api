<?php

namespace App\Controller;

use Laminas\Diactoros\Response\JsonResponse;
use App\Exception\DBException;
use PDO;
use PDOException; // either I use this or i use "\" in the catch(), jsyk
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class ExceptionTestController
{
    /**
     * @throws DBException
     */
    public function indexAction(Request $request, Response $response): ResponseInterface
    {
        try{
            new PDO('', '', '');
        }catch(\PDOException){
            throw new DBException('DBException message! DB error!', 500);
        }
        
        return new JsonResponse(['message'=>'test'], 200);
    }
}

// NB: This is just for you to learn how to create your own custom exception class and message