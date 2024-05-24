<?php
include 'conecta.php'; 
require '../../vendor/autoload.php';

// Constantes para mensagens de notificação e razões
define('NOTIFICATION_ERROR', 'error');
define('NOTIFICATION_SUCCESS', 'success');
define('REASON_PASSWORD_MISMATCH', 'password_mismatch');
define('REASON_UPDATE_SUCCESS', 'update_success');
define('REASON_UPDATE_FAILED', 'update_failed');
define('REASON_TOKEN_EXPIRED_OR_INVALID', 'token_expired_or_invalid');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se os parâmetros esperados existem antes de acessá-los
    if (isset($_POST['pp1'], $_POST['pp2'], $_POST['token'])) {
        $pp1 = $_POST['pp1'];
        $pp2 = $_POST['pp2'];
        $token = $_POST['token'];

        // Verifica se as pps são iguais
        if ($pp1 != $pp2) {
            // As pps não coincidem
            header("Location: ../../redefinirpp.html?notification=" . NOTIFICATION_ERROR . "&reason=" . REASON_PASSWORD_MISMATCH);
            exit;
        }

        // Limpa os parâmetros antes de usá-los em consultas SQL
        $pp1 = mysqli_real_escape_string($mysqli, $pp1);
        $pp2 = mysqli_real_escape_string($mysqli, $pp2);
        $token = mysqli_real_escape_string($mysqli, $token);

        // Procura o `idutilizador` e verifica a validade do token
        $stmt = $mysqli->prepare("SELECT idutilizador FROM redefinirpp WHERE token = ? AND expira_em > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $idutilizador = $row['idutilizador']; // Obtém o idutilizador do resultado

            // Token válido, prosseguir com a atualização da pp
            $ppHash = password_hash($pp1, PASSWORD_DEFAULT); // Criptografia da pp

            $stmt = $mysqli->prepare("UPDATE utilizadores SET palavrapasse = ? WHERE idutilizador = ?");
            $stmt->bind_param("si", $ppHash, $idutilizador);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // pp atualizada com sucesso
                // Excluir o pedido de redefinição de senha da base de dados
                $stmt_delete = $mysqli->prepare("DELETE FROM redefinirpp WHERE token = ?");
                $stmt_delete->bind_param("s", $token);
                $stmt_delete->execute();

                header("Location: ../../conta.html?notification=" . NOTIFICATION_SUCCESS . "&reason=" . REASON_UPDATE_SUCCESS);
                
            } else {
                // Erro ao atualizar a pp
                header("Location: ../../redefinirpp.html?notification=" . NOTIFICATION_ERROR . "&reason=" . REASON_UPDATE_FAILED);
            }
        } else {
            // Token inválido ou expirado
            header("Location: ../../redefinirpp.html?notification=" . NOTIFICATION_ERROR . "&reason=" . REASON_TOKEN_EXPIRED_OR_INVALID);
        }
    } else {
        // Parâmetros ausentes
        echo "Parâmetros ausentes.";
    }
} else {
    // Método não suportado
    echo "Método de requisição não suportado.";
}
?>
