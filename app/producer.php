<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = null;
$channel = null;

try {
  $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
  $channel = $connection->channel();
  $channel->queue_declare('email_queue', false, true, false, false);
} catch (Exception $e) {
  $connection_error = true;
  $error_message = "Erro ao conectar-se ao RabbitMQ: " . $e->getMessage();
}


$display_form = true;
$thank_you_message = '';
$error_message_display = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error_message_display = "Email inválido. Por favor, insira um email válido.";
  } else if (isset($connection_error) && $connection_error) {
    $error_message_display = $error_message; // Exibe o erro de conexão
  } else {
    try {
      $msg = new AMQPMessage(
        $email,
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
      );

      $channel->basic_publish($msg, '', 'email_queue');

      $thank_you_message = "O e-mail **$email** foi enfileirado com sucesso!";
      $display_form = false;
    } catch (Exception $e) {
      $error_message_display = "Erro ao enviar e-mail para a fila: " . $e->getMessage();
    } finally {
      if ($channel) $channel->close();
      if ($connection) $connection->close();
    }
  }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $display_form ? 'Envio de E-mail com RabbitMQ' : 'Obrigado pelo Cadastro!'; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* Variáveis CSS para cores e fontes */
    :root {
      --primary-color: #1a73e8;
      /* Azul Google */
      --primary-dark: #174ea6;
      /* Azul mais escuro para hover */
      --text-color: #202124;
      /* Texto principal */
      --secondary-text-color: #5f6368;
      /* Texto secundário */
      --background-color: #f8f9fa;
      /* Fundo claro */
      --surface-color: #ffffff;
      /* Fundo de cards/formulários */
      --border-color: #dadce0;
      /* Borda de inputs */
      --shadow-color: rgba(60, 64, 67, 0.15);
      /* Sombra suave */
      --font-family: 'Roboto', sans-serif;
      --success-color: #34a853;
      /* Verde Google para sucesso */
      --error-color: #ea4335;
      /* Vermelho Google para erro */
    }

    /* Reset básico e estilos globais (Mobile First) */
    body {
      margin: 0;
      padding: 0;
      font-family: var(--font-family);
      color: var(--text-color);
      background-color: var(--background-color);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      box-sizing: border-box;
    }

    /* Container principal para centralizar e adicionar sombra */
    .container {
      background-color: var(--surface-color);
      padding: 24px;
      margin: 24px;
      border-radius: 8px;
      box-shadow: 0 4px 6px var(--shadow-color);
      text-align: center;
      width: 100%;
      max-width: 380px;
      box-sizing: border-box;
    }

    /* Título */
    h1 {
      color: var(--primary-color);
      font-size: 24px;
      margin-bottom: 24px;
      font-weight: 500;
    }

    /* Formulário */
    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    /* Input de e-mail */
    input[type="email"] {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-size: 16px;
      color: var(--text-color);
      box-sizing: border-box;
      transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    input[type="email"]::placeholder {
      color: var(--secondary-text-color);
    }

    input[type="email"]:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
    }

    /* Botão */
    button[type="submit"] {
      background-color: var(--primary-color);
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.3s ease, box-shadow 0.3s ease;
      letter-spacing: 0.25px;
    }

    button[type="submit"]:hover {
      background-color: var(--primary-dark);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    button[type="submit"]:active {
      background-color: var(--primary-dark);
      box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* Mensagens de sucesso/erro */
    .message {
      font-size: 18px;
      margin-top: 16px;
      line-height: 1.5;
    }

    .message strong {
      font-weight: 500;
    }

    .success-message {
      color: var(--success-color);
    }

    .error-message {
      color: var(--error-color);
      font-size: 15px;
      margin-top: 10px;
    }

    .back-button {
      background-color: var(--secondary-text-color);
      /* Um cinza mais neutro */
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: 20px;
      text-decoration: none;
      /* Remove sublinhado do link */
      display: inline-block;
      /* Permite padding e margin */
    }

    .back-button:hover {
      background-color: #4a4d50;
    }


    /* Media Queries para Responsividade (adaptando para telas maiores) */
    @media (min-width: 600px) {
      .container {
        padding: 32px 40px;
        max-width: 450px;
      }

      h1 {
        font-size: 28px;
        margin-bottom: 32px;
      }

      input[type="email"] {
        padding: 14px 18px;
        font-size: 17px;
      }

      button[type="submit"] {
        padding: 14px 28px;
        font-size: 17px;
      }

      .message {
        font-size: 20px;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <?php if ($display_form) : ?>
      <h1>Cadastro de E-mail</h1>
      <p>Informe seu e-mail para receber uma surpresa!</p>
      <?php if ($error_message_display) : ?>
        <p class="error-message"><?php echo $error_message_display; ?></p>
      <?php endif; ?>
      <form method="post">
        <input type="email" name="email" placeholder="Digite seu e-mail" required>
        <button type="submit">Enviar</button>
      </form>
    <?php else : ?>
      <h1>Obrigado!</h1>
      <p class="message success-message">
        <?php echo nl2br($thank_you_message); ?>
      </p>
      <a href="index.php" class="back-button">Voltar</a>
    <?php endif; ?>
  </div>
</body>

</html>