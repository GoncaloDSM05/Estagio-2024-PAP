<?php
include 'conecta.php';

// Definir a chave de encriptação
define('ENCRYPTION_KEY', 'B1b8K3lS8VQy5e6A9zN5jQxE7vYh1rW3');

// Função para encriptar mensagem
function encryptMessage($message) {
    $key = ENCRYPTION_KEY; 
    $iv = substr($key, 0, 16); // IV (initialization vector) deve ter 16 bytes
    return openssl_encrypt($message, 'aes-256-cbc', $key, 0, $iv);
}

// Função para desencriptar mensagem
function decryptMessage($encryptedMessage) {
    $key = ENCRYPTION_KEY; 
    $iv = substr($key, 0, 16); // IV (initialization vector) deve ter 16 bytes
    return openssl_decrypt($encryptedMessage, 'aes-256-cbc', $key, 0, $iv);
}

// Função para buscar o grupo do usuário
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send') {
    // Enviar mensagem
    $userId = intval($_POST['user']);
    $message = $_POST['message'];
    $groupCode = buscarGrupoEMembros($userId);

    if ($groupCode !== null) {
        // Encriptar a mensagem
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
    // Carregar mensagens
    $groupCode = isset($_GET['group']) ? $_GET['group'] : null;

    if ($groupCode !== null) {
        $sql = "SELECT * FROM chats WHERE codgrupo = ? ORDER BY datahora";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('s', $groupCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $user = $row['idutilizador'];
                $encryptedMessage = $row['conteudo'];
                $timestamp = $row['datahora'];
                $avatar = strtoupper(substr($user, 0, 1));
                $isCurrentUser = (isset($_POST['user']) && $_POST['user'] == $user) ? " user" : "";

                // Desencriptar a mensagem
                $message = decryptMessage($encryptedMessage);

                echo "<div class='message$isCurrentUser'>";
                echo "<div class='avatar'>$avatar</div>";
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
