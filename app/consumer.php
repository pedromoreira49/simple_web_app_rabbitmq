<?php
// app/consumer.php
require_once 'vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPMailer\PHPMailer\PHPMailer;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('email_queue', false, true, false, false);

echo "[*] Esperando mensagens...\n";

$callback = function ($msg) {
  $email = $msg->body;
  echo "[x] Enviando email para: $email\n";

  $mail = new PHPMailer(true);
  try {
    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USERNAME'];
    $mail->Password   = $_ENV['SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['SMTP_PORT'];

    $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress($email);
    $mail->Subject = ''; //TO-DO: Adicionar conteúdo do título da mensagem
    $mail->Body    = ''; //TO-DO: Adicionar conteúdo do corpo da mensagem

    $mail->send();
    echo "[✔] Email enviado!\n";
  } catch (Exception $e) {
    echo "[!] Erro ao enviar: {$mail->ErrorInfo}\n";
  }
};

$channel->basic_consume('email_queue', '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
  $channel->wait();
}
