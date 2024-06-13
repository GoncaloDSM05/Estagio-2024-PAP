<?php
include 'conecta.php'; // Inclua o arquivo de conexão com o banco de dados
session_start();

$userId = $_SESSION['idutilizador'] ?? null;

if ($userId !== null) {
    // Consulta ao banco de dados para recuperar notificações do usuário
    $query = "SELECT id, tipo_notificacao, mensagem, data AS created_at, lida FROM notificacoes WHERE idutilizador = ? ORDER BY data DESC ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    $unreadCount = 0;
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if ($row['lida'] == 0) {
            $unreadCount++;
        }
    }

    // Saída das notificações como JSON
    echo json_encode(['success' => true, 'notifications' => $notifications, 'unreadCount' => $unreadCount]);
} else {
    echo json_encode(['success' => false, 'error' => 'User ID not found']);
}
?>
