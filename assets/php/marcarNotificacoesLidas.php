<?php
include 'conecta.php'; // Inclua o arquivo de conexão com o banco de dados
session_start();

$userId = $_SESSION['idutilizador'] ?? null;

if ($userId !== null) {
    // Atualiza todas as notificações do usuário para marcar como lidas
    $query = "UPDATE notificacoes SET lida = 1 WHERE idutilizador = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update notifications']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'User ID not found']);
}
?>
