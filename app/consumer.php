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
    $mail->CharSet    = "UTF-8";
    $mail->isHTML(true);

    $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress($email);
    $mail->Subject = 'Querido colega...'; //TO-DO: Adicionar conteúdo do título da mensagem
    $mail->Body    = "
      Quero aproveitar este momento para expressar minha profunda gratidão a cada um de vocês, meus colegas do mestrado. 
      É realmente uma honra poder compartilhar essa jornada com pessoas tão especiais. Agradeço não apenas pela troca constante de conhecimentos, desafios e aprendizados, mas também pelos momentos de descontração, companheirismo e amizade que estamos construindo ao longo desse caminho. 
      É muito gratificante perceber que, além de colegas, estamos formando uma verdadeira família, que se apoia, se fortalece e cresce junta. Cada conversa, cada risada, cada apoio nos momentos difíceis e cada conquista compartilhada tornam essa experiência única e muito mais significativa. 
      Que possamos seguir juntos, não só como profissionais, mas também como amigos que levam daqui memórias e aprendizados para toda a vida.
      Meu sincero obrigado a cada um de vocês!
    ";

    $mail->send();
    echo "[✔] Email enviado!\n";
    $msg->ack();
  } catch (Exception $e) {
    echo "[!] Erro ao enviar: {$mail->ErrorInfo}\n";
    $msg->nack(false, true);
  }
};

$channel->basic_consume('email_queue', '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
  $channel->wait();
}
