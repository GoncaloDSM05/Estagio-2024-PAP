<?php
include 'conecta.php';
session_start();

function buscarTarefas($codgrupo) {
    global $mysqli;
    $query = "SELECT idtarefa, titulo, descricao, datahora, estado FROM tarefasg WHERE codgrupo = ?";
    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $codgrupo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $tarefas = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tarefas[] = $row;
    }

    return $tarefas;
}

$userId = $_SESSION['idutilizador'];

$query = "SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?";
$stmt = mysqli_prepare($mysqli, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$codgrupo = null;
if ($row = mysqli_fetch_assoc($result)) {
    $codgrupo = $row['codgrupo'];
}

if ($codgrupo) {
    $tarefas = buscarTarefas($codgrupo);
    echo json_encode($tarefas);
} else {
    echo json_encode([]);
}
?>
