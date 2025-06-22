<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require 'config.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

header('Content-Type: application/json');

$response = [
  'success' => false,
  'message' => 'Ocorreu um erro desconhecido.',
  'email' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Lê o corpo da requisição crua
  $input = file_get_contents('php://input');
  // Decodifica o JSON para um array associativo
  $data = json_decode($input, true);

  //Acessa o email a partir do array $data
  $email = trim($data['email'] ?? '');

  $connection = null;
  $channel = null;

  $emailCheck = filter_var($email, FILTER_VALIDATE_EMAIL);

  // A validação filter_var retorna o email validado ou false se inválido.
  // Se for false, significa que não é um email válido.
  if ($emailCheck === false) {
    $response['message'] = "Email inválido. Por favor, insira um email válido.";
    echo json_encode($response);
    exit;
  } else {
    try {
      $connection = new AMQPStreamConnection(
        $config['host'],
        $config['port'],
        $config['user'],
        $config['password'],
        $config['vhost']
      );
      $channel = $connection->channel();
      $channel->queue_declare('email_queue', false, true, false, false);

      $msg = new AMQPMessage(
        $email,
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
      );

      $channel->basic_publish($msg, '', 'email_queue');

      // Define a resposta de sucesso
      $response['success'] = true;
      $response['message'] = "O e-mail **$email** foi enfileirado com sucesso!";
      $response['email'] = $email;
    } catch (Exception $e) {
      // Define a resposta de erro
      $response['message'] = "Erro ao enviar e-mail para a fila: " . $e->getMessage();
    } finally {
      // Garante que a conexão seja fechada
      if ($channel) $channel->close();
      if ($connection) $connection->close();
    }
  }
} else {
  $response['message'] = "Requisição inválida (apenas POST permitido).";
}

echo json_encode($response);
exit;
