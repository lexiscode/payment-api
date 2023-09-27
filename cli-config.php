<?php

/**
 * This finds the EntityManager and pass it to ConsoleRunner::createHelperSet()
 * through which this would help us run database migrations, validate class annotations 
 * and so on ...
 */

use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

/** @var Container $container */
$container = require_once __DIR__ . '/config/container.php';

return ConsoleRunner::createHelperSet($container->get(EntityManager::class));
