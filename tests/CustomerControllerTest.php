<?php
namespace App;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Mockery;
use Monolog\Logger;
use App\Controllers\CustomerController;
use App\Repositories\CustomerRepository;
use App\Repositories\CustomerRepositoryDoctrine;
use PHPUnit\Framework\TestCase;


class CustomerControllerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $container = new Container();
        $container->set(EntityManager::class, function(Container $c) {
            return Mockery::mock('Doctrine\ORM\EntityManager');
        });

        $container->set(CustomerRepository::class, function(Container $c) {
            $em = $c->get(EntityManager::class);
            return new CustomerRepositoryDoctrine($em);
        });

        $container->set(Logger::class, function(Container $c) {
            return Mockery::mock('Monolog\Logger');
        });

        $this->container = $container;
    }
    public function testCreateInstanceOfCustomerController()
    {
        $abstractControllerObject = new CustomerController($this->container);
        $this->assertInstanceOf('App\Controllers\CustomerController', $abstractControllerObject);
    }
}
