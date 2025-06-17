<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Conexão RabbitMQ
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('email_queue', false, true, false, false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Email inválido.";
    exit;
  }

  $msg = new AMQPMessage(
    $email,
    ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
  );

  $channel->basic_publish($msg, '', 'email_queue');

  echo "Email enfileirado com sucesso para: $email";
  $channel->close();
  $connection->close();
  exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Envio de Email com RabbitMQ</title>
</head>

<body>
  <h1>Cadastro de Email</h1>
  <form method="post">
    <input type="email" name="email" placeholder="Digite seu email" required>
    <button type="submit">Enviar</button>
  </form>
</body>

</html>