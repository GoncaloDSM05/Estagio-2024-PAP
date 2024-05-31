<?php
session_start(); // Inicia a sessão
require 'conecta.php'; // Inclui o arquivo de conexão

if (!isset($_GET['codgrupo'])) {
    die('Código do grupo não especificado.');
}

$codigoGrupo = $_GET['codgrupo'];
$idUtilizador = $_SESSION['idutilizador'];

if (!$idUtilizador) {
    die('Utilizador não está logado.');
}

// Verificar se o grupo existe
$stmt = $mysqli->prepare("SELECT codgrupo FROM grupos WHERE codgrupo = ?");
$stmt->bind_param("s", $codigoGrupo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    // Adicionar o utilizador ao grupo
    $stmt2 = $mysqli->prepare("INSERT INTO utilizadorgrupo (idutilizador, codgrupo) VALUES (?, ?)");
    $stmt2->bind_param("is", $idUtilizador, $codigoGrupo);
    if ($stmt2->execute()) {
        $_SESSION['codgrupo'] = $codigoGrupo;
        header("Location: ../../dashboard.php?notification=success&reason=entrouGrupo");
        exit;
    } else {
        header("Location: ../../dashboard.php?notification=error&reason=erroInesperado");
        exit;
    }
    $stmt2->close();
} else {
    header("Location: ../../dashboard.php?notification=error&reason=codigoInvalido");
    exit;
}

$stmt->close();
$mysqli->close();

?>
