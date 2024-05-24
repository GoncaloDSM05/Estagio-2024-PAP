<?php
include 'conecta.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validação de entrada
    if (isset($_POST['idtarefa']) && isset($_POST['estado'])) {
        $idtarefa = $_POST['idtarefa'];
        $estado = $_POST['estado'];

        // Verificar se os valores são válidos
        if (is_numeric($idtarefa) && is_string($estado)) {
            $query = "UPDATE tarefasg SET estado = ? WHERE idtarefa = ?";
            $stmt = mysqli_prepare($mysqli, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $estado, $idtarefa);
                $success = mysqli_stmt_execute($stmt);

                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erro ao executar a consulta.']);
                }
                mysqli_stmt_close($stmt);
            } else {
                echo json_encode(['success' => false, 'error' => 'Erro ao preparar a consulta.']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Dados de entrada inválidos.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Dados de entrada ausentes.']);
    }
}
mysqli_close($mysqli);
?>
