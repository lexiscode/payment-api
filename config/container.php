<?php

use DI\Container;
use Monolog\Level;
use Monolog\Logger;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Monolog\Handler\StreamHandler;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use App\Repositories\CustomerRepository;
use App\Repositories\CustomerRepositoryDoctrine;
use App\Repositories\MethodRepository;
use App\Repositories\MethodRepositoryDoctrine;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentRepositoryDoctrine;
use App\Repositories\AuthRepository;
use App\Repositories\AuthRepositoryDoctrine;
use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->safeLoad();

$container = new Container;

const APP_ROOT = __DIR__ . "/..";
$container->set('settings', function ($container) {
    return [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => false,

        'doctrine' => [
            // Enables or disables Doctrine metadata caching
            // for either performance or convenience during development.
            'dev_mode' => true,

            // Path where Doctrine will cache the processed metadata
            // when 'dev_mode' is false.
            'cache_dir' => APP_ROOT . '/var/doctrine',

            // List of paths where Doctrine will search for metadata.
            // Metadata can be either YML/XML files or PHP classes annotated
            // with comments or PHP8 attributes.
            'metadata_dirs' => [APP_ROOT . '/app'], //place your autoload dir here

            // The parameters Doctrine needs to connect to your database.
            // These parameters depend on the driver (for instance the 'pdo_sqlite' driver
            // needs a 'path' parameter and doesn't use most of the ones shown in this example).
            // Refer to the Doctrine documentation to see the full list
            // of valid parameters: https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/configuration.html
            'connection' => [
                'driver' => 'pdo_mysql',
                'host' => $_ENV['MARIADB_HOST'], // use mariadb container_name in your .yml
                'port' => 3306,
                'dbname' => $_ENV['MARIADB_DB_NAME'], // create this database manually in phpMyAdmin
                'user' => $_ENV['MARIADB_DB_USER'],
                'password' => $_ENV['MARIADB_DB_USER_PASSWORD']
            ]
        ]

    ];
});

$container->set(EntityManager::class, function (Container $c): EntityManager {
    /** @var array $settings */
    $settings = $c->get('settings');

    $cache = $settings['doctrine']['dev_mode'] ?
        DoctrineProvider::wrap(new ArrayAdapter()) :
        DoctrineProvider::wrap(new FilesystemAdapter(directory: $settings['doctrine']['cache_dir']));

    $config = Setup::createAttributeMetadataConfiguration(
        $settings['doctrine']['metadata_dirs'],
        $settings['doctrine']['dev_mode'],
        null,
        $cache
    );

    return EntityManager::create($settings['doctrine']['connection'], $config);
});


// Monologging
$container->set(Logger::class, function (Container $container){
    $logger = new Logger('payment-api');

    $output = "%level_name% | %datetime% > %message% | %context% %extra%\n";
    $dateFormat = "Y-m-d, H:i:s";
    $logger->pushHandler((new StreamHandler(__DIR__ . '/../logs/alert.log', Level::Alert))
        ->setFormatter(new LineFormatter($output, $dateFormat)));
    $logger->pushHandler((new StreamHandler(__DIR__ . '/../logs/critical.log', Level::Critical))
        ->setFormatter(new JsonFormatter()));
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/error.log', Level::Error));
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/warning.log', Level::Warning));
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/notice.log', Level::Notice));
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/info.log', Level::Info));
    $logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/debug.log', Level::Debug));
    return $logger;
});


// Creating a container instance for CustomerRepository
$container->set(CustomerRepository::class, function (Container $container){
    $em = $container->get(EntityManager::class);
    return new CustomerRepositoryDoctrine($em);
});

// Creating a container instance for PaymentRepositoryDoctrine
$container->set(PaymentRepository::class, function (Container $container){
    $em = $container->get(EntityManager::class);
    return new PaymentRepositoryDoctrine($em);
});

// Creating a container instance for MethodRepositoryDoctrine 
$container->set(MethodRepository::class, function (Container $container){
    $em = $container->get(EntityManager::class);
    return new MethodRepositoryDoctrine($em);
});

// Creating a container instance for AuthRepositoryDoctrine 
$container->set(EntityRepository::class, function (Container $container){
    $em = $container->get(EntityManager::class);
    return new AuthRepositoryDoctrine($em);
});


return $container;
