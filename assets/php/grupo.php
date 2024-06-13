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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar se o formulário de entrar em um grupo foi submetido
    if (isset($_POST['enviarEntrarGrupo'])) {
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

