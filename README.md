<h1 align="center">Payment API</h1>

<p align="center">
The Payment-API is a RESTful API designed to handle requests related to customers, methods and payments. This application programming interface is created using a secured OAuth JWT, while working with Doctrine ORM, OOP, MVC, Slim Microframework, GitHub Action continuous integration with PHPStan and PHPUnit, MySQL as the database and used Postman for API testing and Swagger for API documentation.
</p>

[![Continuous Integration PaymentAPI](https://github.com/lexiscode/payment-api/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/lexiscode/payment-api/actions/workflows/continuous-integration.yml)
![GitHub Workflow Status (with event)](https://img.shields.io/github/actions/workflow/status/lexiscode/payment-api/continuous-integration.yml)


## Installation

This app can run using the typical XAMPP configuration; ensure you have the correct PHP version. Or you can also use Docker Compose to start all the required services.

### Here's how we run it using XAMPP:

1. Ensure you have XAMPP and Composer installed.
2. Create the database `payment_api`.
3. Install the PHP dependencies.
   ````
   composer install
   ````
4. Update your .env file configuration
   ````
   MARIADB_HOST=localhost
   MARIADB_ROOT_PASSWORD=root
   MARIADB_DB_NAME=payment_api
   MARIADB_DB_USER=root
   MARIADB_DB_USER_PASSWORD=
   ````
5. Create the tables.
   ```
   php vendor/bin/doctrine orm:schema-tool:create 
   ````
6. Run the local web server.
   ```
   php -S localhost:200 -t public/
   ````

### Here's with Docker:

1. Ensure the `.env` contains the same MySQL password that the one set on [docker-compose.yml](./docker-compose.yml).
2. First time running, build the Docker Image and run it's containers in a single command below
   ````
   docker-compose up --build
   ````
3. To ensure that MariaDB has started running fully, you may need wait for approx. 2-3 mins, or you can check by using this commands below:
   ```
   >> docker exec -it payment_api_mariadb bash
   >> mariadb -u root -p 
   >> password is also "root"
   ````
NB: If you get an error like this (ERROR 2002 (HY000) or ERROR 1045 (28000)) after inputting the login details above, it means you should wait more like a minute extra for MariaDB to fully start, then try again.
4. Create the tables.
   ```
   >> docker exec -it payment_api_php bash
   >> php vendor/bin/doctrine orm:schema-tool:create 
   ````
5. Go to http://localhost:8000

## All Routes

The API can be tested using Postman. But note that you will need to first create a JWT authentication token (by creating an account) in order to gain access to the payment API resources. Once you've registered, then logged in, and a JWT Bearer Token will be given to you.

Use the following endpoint to create a user account and also login in order to generate an authorization "Bearer Token":
```
POST /register
POST /login
```
Sample JSON request body for both the registeration and login, only email and password fields:
```
{
  "email": "email@example.com",
  "password": "password",
}
```
NB: You have to remain in a particular tab where you've set the JWT token to test all requests, the token grants access to only a single Postman tab, so don't open multiple tabs. Except the token is set globally in the application. You have limited timeframe access to resources for 2 hours only.


For Customers Routes:
````
GET: /v1/customers/activate/{status}
GET: /v1/customers
GET: /v1/customers/{id}
POST: /v1/customers
PUT: /v1/customers/{id}
PATCH: /v1/customers/{id}
DELETE: /v1/customers/{id}
````

Note: The {status} can only hold a boolean value, 1 (activate) or 0 (deactivate).

For Methods Routes:
````
GET: /v1/methods/activate/{status}
GET: /v1/methods
GET: /v1/methods/{id}
POST: /v1/methods
PUT: /v1/methods/{id}
PATCH: /v1/methods/{id}
DELETE: /v1/methods/{id}
````

Note: The {status} can only hold a boolean value, 1 (activate) or 0 (deactivate).

For Payments Routes:
````
GET: /v1/payments
GET: /v1/payments/{id}
POST: /v1/payments
PUT: /v1/payments/{id}
PATCH: /v1/payments/{id}
DELETE: /v1/payments/{id}
````

## API Testing with Swagger 
For this case, ensure you've setup the project to work with your local XAMPP as detailed at the top of this README. Then run a local server inside your /public directory, using port 200. Then visit this URL: http://localhost:200/docs/

NB: For now to gain access to the resources and to bypass authentication in Swagger, first go inside my public/index.php file and "comment" code line 43 (i.e. where I wrote this: require DIR . '/../middleware/jwt_proxy.php';). In this note, for now, don't bother testing the Authentication section (i.e. the register and login sections) in the Swagger UI, focus on other routes.

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

## Heroku Deployment
Deployment URL: (https://lexispayment-api-c0593ff58537.herokuapp.com/)

