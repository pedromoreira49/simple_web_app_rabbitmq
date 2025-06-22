<?php

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return [
  'host'     => 'rabbitmq',
  'port'     => 5672,
  'user'     => $_ENV['RABBITMQ_USER'],
  'password' => $_ENV['RABBITMQ_PASSWORD'],
  'vhost'    => '/',
];
