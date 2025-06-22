<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Validação simples
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Método não permitido']);
  exit;
}

$email = $_POST['email'] ?? null;

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['error' => 'Email inválido']);
  exit;
}

// Config RabbitMQ
$config = require __DIR__ . '/../app/config.php';

$connection = new AMQPStreamConnection(
  $config['host'],
  $config['port'],
  $config['user'],
  $config['password'],
  $config['vhost']
);

$channel = $connection->channel();
$channel->queue_declare('email_queue', false, true, false, false);

// Cria mensagem
$msg = new AMQPMessage($email, [
  'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
]);

$channel->basic_publish($msg, '', 'email_queue');

$channel->close();
$connection->close();

echo json_encode(['status' => 'Email enviado para fila', 'email' => $email]);
