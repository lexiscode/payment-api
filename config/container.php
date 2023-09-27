<?php

use DI\Container;
use Monolog\Level;
use Monolog\Logger;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Monolog\Handler\StreamHandler;

use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use App\Repositories\CategoryRepository;
use App\Repositories\CategoryRepositoryDoctrine;
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

    // Use the ArrayAdapter or the FilesystemAdapter depending on the value of the 'dev_mode' setting
    // You can substitute the FilesystemAdapter for any other cache you prefer from the symfony/cache library
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

$container->set(\PDO::class, function ($c){
    $dbHost = $_ENV['MARIADB_HOST'];
    $dbName = $_ENV['MARIADB_DB_NAME'];
    $dbUser = $_ENV['MARIADB_DB_USER'];
    $dbPassword = $_ENV['MARIADB_DB_USER_PASSWORD'];
    $dsn = "mysql:host=$dbHost;dbname=$dbName";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPassword, $options);
    } catch (PDOException $exception){
        error_log($exception->getMessage());
    }

    return $pdo;
});


// Monologging
$container->set(Logger::class, function (Container $container) {
    // Monolog Example
    $logger = new Logger('logger');
    $output = "%level_name% | %datetime% > %message% | %context% %extra%\n";
    $dateFormat = "Y-m-d, H:i:s";
    $logger->pushHandler((new StreamHandler(__DIR__ . '/../logs/error.log', Level::Error))
        ->setFormatter(new LineFormatter($output, $dateFormat)));
    $logger->pushHandler((new StreamHandler(__DIR__ . '/../logs/info.log', Level::Info))
        ->setFormatter(new JsonFormatter()));
    $logger->pushHandler((new StreamHandler(__DIR__ . '/../logs/debug.log', Level::Debug))
        ->setFormatter(new LineFormatter($output, $dateFormat)));
    return $logger;
});


// Creating a container for CategoryRepository
$container->set(CategoryRepository::class, function (Container $container){
    $em = $container->get(EntityManager::class);
    return new CategoryRepositoryDoctrine($em);
});


// Define the CategoryController and inject dependencies
$container->set(App\Controllers\CategoryController::class, function (Container $c) {
    return new App\Controllers\CategoryController(
        $c->get(App\Repositories\CategoryRepository::class)
    );
});


return $container;
