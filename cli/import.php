<?php

$pdo = new \PDO('mysql:host=payment_api_mariadb;dbname=payment_api', 'root', 'root');
$sql = file_get_contents(__DIR__ . '/import.sql');
$pdo->exec($sql);

