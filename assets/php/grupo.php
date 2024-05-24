<?php
session_start(); // Inicia a sessão
require 'conecta.php'; // Inclui o arquivo de conexão

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
        $codigoGrupo = test_input($_POST['codgrupo']);
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
