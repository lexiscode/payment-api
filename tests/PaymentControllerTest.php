<?php
namespace App;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Mockery;
use Monolog\Logger;
use App\Controllers\PaymentController;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentRepositoryDoctrine;
use PHPUnit\Framework\TestCase;


class PaymentControllerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $container = new Container();
        $container->set(EntityManager::class, function(Container $c) {
            return Mockery::mock('Doctrine\ORM\EntityManager');
        });

        $container->set(PaymentRepository::class, function(Container $c) {
            $em = $c->get(EntityManager::class);
            return new PaymentRepositoryDoctrine($em);
        });

        $container->set(Logger::class, function(Container $c) {
            return Mockery::mock('Monolog\Logger');
        });

        $this->container = $container;
    }
    public function testCreateInstanceOfPaymentController()
    {
        $abstractControllerObject = new PaymentController($this->container);
        $this->assertInstanceOf('App\Controllers\PaymentController', $abstractControllerObject);
    }
}

