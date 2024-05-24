<?php
session_start();
include "conecta.php";

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function emailExiste($mysqli, $email) {
    $stmt = $mysqli->prepare("SELECT * FROM utilizadores WHERE email = ?");
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function nomeUtilizadorExiste($mysqli, $nutilizador) {
    $stmt = $mysqli->prepare("SELECT * FROM utilizadores WHERE nomeutilizador = ?");
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("s", $nutilizador);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function verificarDados($mysqli, $identificador, $palavrapasse) {
    // A consulta verifica se o identificador corresponde a um email ou a um nome de utilizador na mesma coluna
    $stmt = $mysqli->prepare("SELECT idutilizador, palavrapasse FROM utilizadores WHERE email = ? OR nomeutilizador = ?");
    if ($stmt === false) {
        return false;
    }

    $stmt->bind_param("ss", $identificador, $identificador);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($palavrapasse, $row['palavrapasse'])) {
            return $row['idutilizador'];
        }
    }
    return false;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['action']) && $_POST['action'] == "register") {
        // Processo de Registo
        if (isset($_POST['pnome'], $_POST['unome'], $_POST['nutilizador'], $_POST['nfuncao'], $_POST['email'], $_POST['palavrapasse'])) {
            $pnome = test_input($_POST['pnome']);
            $unome = test_input($_POST['unome']);
            $nutilizador = test_input($_POST['nutilizador']);
            $nfuncao = test_input($_POST['nfuncao']);
            $email = test_input($_POST['email']);
            $palavrapasse = password_hash($_POST['palavrapasse'], PASSWORD_DEFAULT);

            if (emailExiste($mysqli, $email)) {
                header("Location: ../../conta.html?notification=error&reason=email_exists");
                exit;
            }
            
            if (nomeUtilizadorExiste($mysqli, $nutilizador)) {
                header("Location: ../../conta.html?notification=error&reason=username_exists");
                exit;
            }

            $stmt = $mysqli->prepare("INSERT INTO utilizadores (primeironome, ultimonome, nomeutilizador, nomefuncao, email, palavrapasse) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                header("Location: ../../conta.html?notification=error&error=" . urlencode("Erro na preparação do statement: " . $mysqli->error));
                exit;
            }

            $stmt->bind_param("ssssss", $pnome, $unome, $nutilizador, $nfuncao, $email, $palavrapasse);

            if ($stmt->execute()) {
                header("Location: ../../conta.html?notification=success&reason=registered");
                exit;
            } else {
                header("Location: ../../conta.html?notification=error&error=" . urlencode(addslashes($stmt->error)));
                exit;
            }
        } else {
            // Parâmetros ausentes
            header("Location: ../../conta.html?notification=error&reason=missing_parameters");
            exit;
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == "login") {
        // Processo de Login
        if (isset($_POST['emailnomeutilizador'], $_POST['palavrapasse'])) {
            $emailnomeutilizador = test_input($_POST['emailnomeutilizador']);
            $palavrapasse = $_POST['palavrapasse'];

            $idutilizador = verificarDados($mysqli, $emailnomeutilizador, $palavrapasse);
            if ($idutilizador !== false) {
                // Login bem-sucedido
                $_SESSION['idutilizador'] = $idutilizador;
                header("Location: ../../dashboard.php");
                exit;
            } else {
                // Login falhado
                header("Location: ../../conta.html?notification=error&reason=loginfailed");
                exit;
            }
        } else {
            // Parâmetros ausentes
            header("Location: ../../conta.html?notification=error&reason=missing_parameters");
            exit;
        }
    }
}

$mysqli->close();
?>
