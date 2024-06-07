<?php
include 'conecta.php';

define('ENCRYPTION_KEY', 'B1b8K3lS8VQy5e6A9zN5jQxE7vYh1rW3');

function encryptMessage($message) {
    $key = ENCRYPTION_KEY;
    $iv = substr($key, 0, 16);
    return openssl_encrypt($message, 'aes-256-cbc', $key, 0, $iv);
}

function decryptMessage($encryptedMessage) {
    $key = ENCRYPTION_KEY;
    $iv = substr($key, 0, 16);
    return openssl_decrypt($encryptedMessage, 'aes-256-cbc', $key, 0, $iv);
}

function buscarGrupoEMembros($userId) {
    global $mysqli;
    $query = "SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($groupResult = $result->fetch_assoc()) {
        return $groupResult['codgrupo'];
    } else {
        return null;
    }
}

function buscarNomeUsuario($userId) {
    global $mysqli;
    $query = "SELECT primeironome, ultimonome FROM utilizadores WHERE idutilizador = ?";
    $stmt = $mysqli->prepare($query);

    if (!$stmt) {
        die('Erro na consulta SQL: ' . $mysqli->error);
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($primeiroNome, $ultimoNome);
    $stmt->fetch();
    $stmt->close();

    return $primeiroNome . ' ' . $ultimoNome;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send') {
    $userId = intval($_POST['user']);
    $message = $_POST['message'];
    $groupCode = buscarGrupoEMembros($userId);

    if ($groupCode !== null) {
        $encryptedMessage = encryptMessage($message);
        $stmt = $mysqli->prepare("INSERT INTO chats (conteudo, idutilizador, codgrupo, datahora) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sis", $encryptedMessage, $userId, $groupCode);

        if ($stmt->execute()) {
            echo "Mensagem enviada com sucesso!";
        } else {
            echo "Erro: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Erro: Grupo não encontrado para o usuário.";
    }
} else {
    $userId = intval($_GET['user']);
    $groupCode = buscarGrupoEMembros($userId);

    if ($groupCode !== null) {
        $sql = "SELECT * FROM chats WHERE codgrupo = ? ORDER BY datahora";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $groupCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $user = $row['idutilizador'];
                $encryptedMessage = $row['conteudo'];
                $timestamp = $row['datahora'];
                $isCurrentUser = ($userId == $user) ? " user" : " other";

                $message = decryptMessage($row['conteudo']);
                
                $nomeUsuario = buscarNomeUsuario($user);

                echo "<div class='message$isCurrentUser'>";
                echo "<div class='username'>$nomeUsuario</div>";
                echo "<div class='content'>";
                echo "<p>$message</p>";
                echo "<div class='timestamp'>$timestamp</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "Nenhuma mensagem.";
        }

        $stmt->close();
    } else {
        echo "Erro: Grupo não especificado.";
    }
}

$mysqli->close();
?>
