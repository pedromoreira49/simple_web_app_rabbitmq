<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Envio de E-mail com RabbitMQ</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* Seus estilos CSS permanecem inalterados */
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

    h1 {
      color: var(--primary-color);
      font-size: 24px;
      margin-bottom: 24px;
      font-weight: 500;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

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
      display: inline-block;
    }

    .back-button:hover {
      background-color: #4a4d50;
    }

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
  <div class="container" id="main-content">
    <h1>Cadastro de E-mail</h1>
    <p>Informe seu e-mail para receber uma surpresa!</p>
    <p class="error-message" id="form-error-message" style="display:none;"></p>
    <form id="email-form" method="post" action="app/producer.php">
      <input type="email" name="email" id="email-input" placeholder="Digite seu e-mail" required>
      <button type="submit" id="submit-button">Enviar</button>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('email-form');
      const mainContent = document.getElementById('main-content');
      const formErrorMessage = document.getElementById('form-error-message');
      const emailInput = document.getElementById('email-input');
      const submitButton = document.getElementById('submit-button');

      form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Limpa mensagens de erro anteriores e desabilita o botão
        formErrorMessage.style.display = 'none';
        formErrorMessage.textContent = '';
        submitButton.disabled = true;
        submitButton.textContent = 'Enviando...';

        const dataToSend = {
          "email": emailInput.value
        }

        const contentJson = JSON.stringify(dataToSend);

        try {
          // Envia a requisição AJAX para o producer.php
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: contentJson
          });

          // Espera a resposta JSON do producer.php
          const data = await response.json();

          if (data.success) {
            // Se for sucesso, atualiza o conteúdo do container para a mensagem de agradecimento
            mainContent.innerHTML = `
              <h1>Obrigado!</h1>
              <p class="message success-message">
                O e-mail <strong>${data.email}</strong> foi enfileirado com sucesso!
              </p>
              <a href="#" class="back-button" onclick="location.reload();">Voltar</a>
              `;
            document.title = 'Obrigado pelo Cadastro!'; // Atualiza o título da aba
          } else {
            // Se houver erro, exibe a mensagem de erro acima do formulário
            formErrorMessage.textContent = data.message;
            formErrorMessage.style.display = 'block';
            submitButton.disabled = false; // Habilita o botão novamente
            submitButton.textContent = 'Enviar'; // Restaura o texto do botão
          }
        } catch (error) {
          // Captura erros de rede ou se a resposta não for um JSON válido
          formErrorMessage.textContent = "Erro na comunicação com o servidor. Tente novamente.";
          formErrorMessage.style.display = 'block';
          submitButton.disabled = false;
          submitButton.textContent = 'Enviar';
          console.error('Erro ao enviar formulário:', error); // Para depuração no console do navegador
        }
      });
    });
  </script>
</body>

</html>