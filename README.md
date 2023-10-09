<h1 align="center">Payment API</h1>

<p align="center">
The Payment-API is a RESTful API designed to handle requests related to customers, methods and payments. This application programming interface is created using a secured OAuth JWT, while working with Doctrine ORM, OOP, MVC, Slim Microframework, GitHub Action continuous integration with PHPStan and PHPUnit, MySQL as the database and used Postman for API testing and Swagger for API documentation.
</p>

[![Continuous Integration PaymentAPI](https://github.com/lexiscode/payment-api/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/lexiscode/payment-api/actions/workflows/continuous-integration.yml)
[![Build]](https://img.shields.io/github/actions/workflow/status/lexiscode/payment-api/continuous-integration.yml)

## Installation

This app can run using the typical XAMPP configuration; ensure you have the correct PHP version. Or you can also use Docker Compose to start all the required services.

### Here's how we run it using XAMPP:

1. Ensure you have XAMPP and Composer installed.
2. Create the database `payment_api`.
3. Install the PHP dependencies.
   ````
   composer install
   ````
4. Create the tables.
   ```
   php vendor/bin/doctrine orm:schema-tool:create 
   ````
5. Run the local web server.
   ```
   php -S localhost:8000 -t public/
   ````

### Here's with Docker:

1. Ensure the `.env` contains the same MySQL password that the one set on [docker-compose.yml](./docker-compose.yml).
2. First time running, build the Docker Image and run it's containers in a single command below
   ````
   docker-compose up --build
   ````
3. Create the tables.
   ```
   docker exec -it [fpm-container-id] php vendor/bin/doctrine orm:schema-tool:create 
   ````
4. Go to http://localhost:8000

## Quality Tools

Note: If you are using only the Docker containers, remember to include the prefix `docker exec -it [fpm-container-id] ` to all the PHP commands, similar to one above.

- Run the unit tests with PHPUnit
  ```
  php vendor/bin/phpunit tests/ --colors
  ```
- Run the static analysis with PHPStan
  ```
  php vendor/bin/phpstan analyse app/
  ```

