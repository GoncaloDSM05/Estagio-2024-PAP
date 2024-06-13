<?php
include 'conecta.php';
session_start();

require '../../vendor/autoload.php'; // Inclua o autoload do PHPMailer aqui

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function buscarGrupo($groupId) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT nome, descricao, codgrupo FROM grupos WHERE codgrupo = ?");
    if (!$stmt) {
        echo "Erro na preparação da query: " . $mysqli->error;
        exit;
    }

    $stmt->bind_param("s", $groupId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_assoc();
    } else {
        return null;
    }
}

function buscarUsuario($userId) {
    global $mysqli;

    $stmt = $mysqli->prepare("SELECT primeironome, ultimonome FROM utilizadores WHERE idutilizador = ?");
    if (!$stmt) {
        echo "Erro na preparação da query: " . $mysqli->error;
        exit;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows > 0) {
        return $resultado->fetch_assoc();
    } else {
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email']) && isset($_POST['groupId'])) {
        $email = $_POST['email'];
        $groupId = $_POST['groupId'];
        $userId = $_SESSION['idutilizador'];

        // Verificar se o email existe no sistema
        $query = "SELECT * FROM utilizadores WHERE email = ?";
        $stmt = mysqli_prepare($mysqli, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            // Buscar informações do grupo e do remetente
            $groupInfo = buscarGrupo($groupId); // Função para buscar informações do grupo
            $senderInfo = buscarUsuario($userId); // Função para buscar informações do remetente

            // Enviar o convite por email
            $mail = new PHPMailer(true);

            try {
                // Configurações do servidor de email
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'squadforgeese@gmail.com'; // Substituir pelo seu email real
                $mail->Password = 'ijsp chba ihst yyqq'; // Substituir pela sua senha real
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Configurações do email
                $mail->setFrom('squadforgeese@gmail.com', 'SquadForge');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = 'Convite para o Grupo ' . $groupInfo['nome'];

                // Construir o corpo do email
                $link_convite = 'http://localhost/SquadForge/assets/php/entrar_grupo.php?codgrupo=' . $groupInfo['codgrupo']; // Link do convite (substituir por um link real)
                $body = '
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
                        <h2>Você foi convidado por ' . $senderInfo['primeironome'] . ' ' . $senderInfo['ultimonome'] . ' para ingressar no grupo ' . $groupInfo['nome'] . '!</h2>
                        <p>O grupo ' . $groupInfo['nome'] . ' é uma comunidade dedicada a ' . $groupInfo['descricao'] . '<br>Código do grupo: ' . $groupInfo['codgrupo'] . '.<br>Clique no botão abaixo para aceitar o convite e juntar-se a nós:</p>
                        <a href="' . $link_convite . '" class="btn">Aceitar Convite</a>
                        <div class="footer">
                                <p>Se tiver problemas ao clicar no botão, copie e cole o seguinte link no seu navegador:</p>
                                <p><a href="' . $link_convite . '">' . $link_convite . '</a></p>
                        </div>
                    </body>
                    </html>
                ';

                $mail->Body = $body;

                // Enviar o email
                $mail->send();
                
                // Adicionar notificação no banco de dados
                $tipo_notificacao = "Convite para Grupo";
                $mensagem = "Você recebeu um convite para ingressar no grupo " . $groupInfo['nome'] . ".";
                $idutilizador = $user['idutilizador'];
                $data = date('Y-m-d H:i:s');

                $stmt = $mysqli->prepare("INSERT INTO notificacoes (tipo_notificacao, mensagem, idutilizador, data) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssis", $tipo_notificacao, $mensagem, $idutilizador, $data);
                $stmt->execute();
                $stmt->close();

                // Redirecionar após o envio bem-sucedido
                header("Location: ../../dashboard.php?notification=success&reason=conviteEnviado");
                exit;
            } catch (Exception $e) {
                // Tratar erros no envio do email
                header("Location: ../../dashboard.php?notification=error&reason=erroEnvioConvite");
                exit;
            }
        } else {
            // Se o email não existir no sistema
            header("Location: ../../dashboard.php?notification=error&reason=emailNaoRegistrado");
            exit;
        }
    }
}
?>
