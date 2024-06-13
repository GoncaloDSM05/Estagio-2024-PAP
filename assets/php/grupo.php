<?php
session_start(); // Inicia a sessão
require 'conecta.php'; // Inclui o arquivo de conexão

// Função para adicionar uma notificação
function adicionarNotificacao($tipo_notificacao, $mensagem, $idutilizador) {
    global $mysqli;

    $data = date('Y-m-d H:i:s');

    $stmt = $mysqli->prepare("INSERT INTO notificacoes (tipo_notificacao, mensagem, idutilizador, data) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $tipo_notificacao, $mensagem, $idutilizador, $data);
    $stmt->execute();
    $stmt->close();
}

function gerarCodigoGrupo($length = 10) {
    $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $caracteresLength = strlen($caracteres);
    $codigoAleatorio = '';
    for ($i = 0; $i < $length; $i++) {
        $codigoAleatorio .= $caracteres[rand(0, $caracteresLength - 1)];
    }
    return $codigoAleatorio;
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se o formulário de entrar em um grupo foi submetido
    if (isset($_POST['enviarCriarGrupo'])) {
        // Código para criação do grupo
        $codigoGrupo = gerarCodigoGrupo(10);
        $nome = test_input($_POST['nome']);
        $diretrizes = test_input($_POST['diretrizes']);
        $descricao = test_input($_POST['descricao']);
        $idUtilizador = $_SESSION['idutilizador'];

        $stmt = $mysqli->prepare("INSERT INTO grupos (codgrupo, nome, diretrizes, descricao, idutilizadordono) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $codigoGrupo, $nome, $diretrizes, $descricao, $idUtilizador);
        if ($stmt->execute()) {
            $stmt2 = $mysqli->prepare("INSERT INTO utilizadorgrupo (idutilizador, codgrupo) VALUES (?, ?)");
            $stmt2->bind_param("is", $idUtilizador, $codigoGrupo);
            if ($stmt2->execute()) {
                $_SESSION['codgrupo'] = $codigoGrupo;
                header("Location: ../../dashboard.php?notification=success&reason=criouGrupo");
                exit;
            } else {
                header("Location: ../../dashboard.php?notification=error&reason=erroInesperadoC");
                exit;
            }
            $stmt2->close();
        } else {
            header("Location: ../../dashboard.php?notification=error&reason=erroInesperadoC");
            exit;
        }
        $stmt->close();
    } elseif (isset($_POST['enviarEntrarGrupo'])) {
        // Código para entrar em um grupo
        $codigoGrupo = $_POST['codgrupo'];
        $idUtilizador = $_SESSION['idutilizador'];

        $stmt = $mysqli->prepare("SELECT codgrupo FROM grupos WHERE codgrupo = ?");
        $stmt->bind_param("s", $codigoGrupo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt2 = $mysqli->prepare("INSERT INTO utilizadorgrupo (idutilizador, codgrupo) VALUES (?, ?)");
            $stmt2->bind_param("is", $idUtilizador, $codigoGrupo);
            if ($stmt2->execute()) {
                $_SESSION['codgrupo'] = $codigoGrupo;

                // Adicionar notificação de membro entrando no grupo para o dono do grupo
                $groupOwnerStmt = $mysqli->prepare("SELECT idutilizadordono FROM grupos WHERE codgrupo = ?");
                $groupOwnerStmt->bind_param("s", $codigoGrupo);
                $groupOwnerStmt->execute();
                $groupOwnerStmt->bind_result($groupOwnerId);
                $groupOwnerStmt->fetch();
                $groupOwnerStmt->close();

                if ($groupOwnerId != $idUtilizador) {
                    $groupOwnerNotificationMessage = "Um novo membro entrou no grupo.";
                    adicionarNotificacao("Membro Entrou no Grupo", $groupOwnerNotificationMessage, $groupOwnerId);
                }

                header("Location: ../../dashboard.php?notification=success&reason=entrouGrupo");
                exit;
            } else {
                header("Location: ../../dashboard.php?notification=error&reason=erroInesperado");
                exit;
            }
            $stmt2->close();
        } else {
            header("Location: ../../dashboard.php?notification=error&reason=codigoInválido");
            exit;
        }
        $stmt->close();
    }
}
$mysqli->close();
?>

