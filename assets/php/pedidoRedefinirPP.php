<?php
include 'conecta.php'; 
require '../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['email'])) { // Usar !empty para garantir que 'email' não seja vazio
    $email = trim($_POST['email']); // Usando trim para remover espaços desnecessários
    
    // Preparando a query para evitar SQL Injection
    $stmt = $mysqli->prepare("SELECT idutilizador FROM utilizadores WHERE email = ?");
    if (!$stmt) {
        // Tratamento de erro na preparação da query
        echo "Erro na preparação da query: " . $mysqli->error;
        exit;
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        $utilizador = $resultado->fetch_assoc();

        // Verificar se já existe um token válido para este utilizador
        $stmt = $mysqli->prepare("SELECT token FROM redefinirpp WHERE idutilizador = ? AND expira_em > NOW()");
        $stmt->bind_param("i", $utilizador['idutilizador']);
        $stmt->execute();
        $resultadoToken = $stmt->get_result();

        if ($resultadoToken && $resultadoToken->num_rows > 0) {
            // Já existe um token válido, não enviar outro
            header("Location: ../../pedidoredefinirpp.html?notification=error&reason=existingToken");
            exit;
        } else {
            // Não existe um token válido, prosseguir com a criação de um novo
            $token = bin2hex(random_bytes(50));

            $stmt = $mysqli->prepare("INSERT INTO redefinirpp (idutilizador, token, expira_em) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
            if (!$stmt) {
                echo "Erro na preparação da query: " . $mysqli->error;
                exit;
            }
        }

        $stmt->bind_param("is", $utilizador['idutilizador'], $token);
        $stmt->execute();

        $mail = new PHPMailer(true);

        try {
            // Configurações do servidor de email
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'squadforgeese@gmail.com'; // Substituir pelo seu email real
            $mail->Password = 'ijsp chba ihst yyqq'; // Substituir pela sua pp real
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('squadforgeese@gmail.com', 'SquadForge'); // Substituir pelo seu email real
            $mail->addAddress($email); // Destinatário

            // Conteúdo do email
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Redefinição da Palavra-Passe';
            $link_redefinicao = "http://localhost/SquadForge/redefinirpp.html?token=$token";
            $mail->Body = '
                    <html>
                    <head>
                        <style>
                            body {
                                font-family: Arial, sans-serif;
                                margin: 0;
                                padding: 20px;
                                color: #333;
                            }
                            .container {
                                max-width: 600px;
                                margin: auto;
                                background: #f8f8f8;
                                padding: 20px;
                                border-radius: 8px;
                                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                            }
                            .btn {
                                display: inline-block;
                                padding: 10px 20px;
                                color: #fff !important;
                                background-color: #007bff;
                                border-radius: 5px;
                                text-decoration: none !important;
                                font-weight: bold;
                            }

                            .footer {
                                margin-top: 20px;
                                font-size: 0.9em;
                                text-align: center;
                                color: #666;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h2>Redefinição de Palavra-Passe</h2>
                            <p>Recebemos um pedido para redefinir a sua palavra-passe. Se não foi você que pediu, por favor ignore este e-mail. Caso contrário, clique no botão abaixo para prosseguir:</p>
                            <a href="' . $link_redefinicao . '" class="btn">Redefinir Palavra-Passe</a>
                            <div class="footer">
                                <p>Se tiver problemas ao clicar no botão, copie e cole o seguinte link no seu navegador:</p>
                                <p><a href="' . $link_redefinicao . '">' . $link_redefinicao . '</a></p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ';

            $mail->send();
            header("Location: ../../pedidoredefinirpp.html?notification=success");
        } catch (Exception $e) {
            // Tratamento de falha no envio
            echo 'Erro ao enviar email: ' . $e->getMessage();
        }
    } else {
        // Tratamento para quando o email não é encontrado
        header("Location: ../../pedidoredefinirpp.html?notification=error&reason=notexistingEmail");
    }
} else {
    // Tratamento para quando o método não é POST ou o campo email está vazio
    echo "Requisição inválida.";
}
?>