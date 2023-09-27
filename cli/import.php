<?php

$pdo = new \PDO('mysql:host=slim_api_mariadb;dbname=slim_docker_api', 'root', 'root');
$sql = file_get_contents(__DIR__ . '/import.sql');
$pdo->exec($sql);

echo 'Database created!';
