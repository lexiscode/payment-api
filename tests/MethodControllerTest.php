<?php
namespace App;

use DI\Container;
use Doctrine\ORM\EntityManager;
use Mockery;
use Monolog\Logger;
use App\Controllers\MethodController;
use App\Repositories\MethodRepository;
use App\Repositories\MethodRepositoryDoctrine;
use PHPUnit\Framework\TestCase;


class MethodControllerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $container = new Container();
        $container->set(EntityManager::class, function(Container $c) {
            return Mockery::mock('Doctrine\ORM\EntityManager');
        });

        $container->set(MethodRepository::class, function(Container $c) {
            $em = $c->get(EntityManager::class);
            return new MethodRepositoryDoctrine($em);
        });

        $container->set(Logger::class, function(Container $c) {
            return Mockery::mock('Monolog\Logger');
        });

        $this->container = $container;
    }
    public function testCreateInstanceOfMethodController()
    {
        $abstractControllerObject = new MethodController($this->container);
        $this->assertInstanceOf('App\Controllers\MethodController', $abstractControllerObject);
    }
}