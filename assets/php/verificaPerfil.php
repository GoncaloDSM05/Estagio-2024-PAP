<?php
include 'conecta.php';
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['idutilizador'])) {
    header("Location: ../../conta.html");
    exit;
}

$idutilizador = $_SESSION['idutilizador'];

// Funções de verificação de existência
function emailExiste($mysqli, $email, $idutilizador) {
    $stmt = $mysqli->prepare("SELECT 1 FROM utilizadores WHERE email = ? AND idutilizador <> ?");
    $stmt->bind_param("si", $email, $idutilizador);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

function nomeUtilizadorExiste($mysqli, $nutilizador, $idutilizador) {
    $stmt = $mysqli->prepare("SELECT 1 FROM utilizadores WHERE nomeutilizador = ? AND idutilizador <> ?");
    $stmt->bind_param("si", $nutilizador, $idutilizador);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

// Processa o formulário quando ele é enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Atualizar informações de perfil
    if (isset($_POST['action']) && $_POST['action'] == 'updateProfile') {
        $primeiroNome = $_POST['firstName'];
        $ultimoNome = $_POST['lastName'];
        $funcao = $_POST['nomefuncao'];
        $email = $_POST['email'];
        $nomeutilizador = $_POST['nomeutilizador'];

        if (emailExiste($mysqli, $email, $idutilizador)) {
            header("Location: ../../perfil.php?notification=error&reason=emailExists");
            exit;
        }

        if (nomeUtilizadorExiste($mysqli, $nomeutilizador, $idutilizador)) {
            header("Location: ../../perfil.php?notification=error&reason=usernameExists");
            exit;
        }

        $query = $mysqli->prepare("UPDATE utilizadores SET primeironome = ?, ultimonome = ?, nomefuncao = ?, email = ?, nomeutilizador = ? WHERE idutilizador = ?");
        $query->bind_param("sssssi", $primeiroNome, $ultimoNome, $funcao, $email, $nomeutilizador, $idutilizador);

        if ($query->execute()) {
            header("Location: ../../perfil.php?notification=success&reason=updatedProfile");
            exit;
        } else {
            header("Location: ../../perfil.php?notification=error&reason=updateFailed");
            exit;
        }
    }

    // Alterar senha do usuário
    if (isset($_POST['action']) && $_POST['action'] == 'alterarPP') {
        $palavrapasseAtual = $_POST['ppAtual'];
        $novapalavrapasse = $_POST['novaPP'];
        $confirmarNovaPP = $_POST['confirmNewPP'];

        if ($novapalavrapasse !== $confirmarNovaPP) {
            header("Location: ../../perfil.php?notification=error&reason=passwordDiferentes");
            exit;
        }

        $query = $mysqli->prepare("SELECT palavrapasse FROM utilizadores WHERE idutilizador = ?");
        $query->bind_param("i", $idutilizador);
        $query->execute();
        $resultado = $query->get_result();
        $dadosUsuario = $resultado->fetch_assoc();

        if (!password_verify($palavrapasseAtual, $dadosUsuario['palavrapasse'])) {
            header("Location: ../../perfil.php?notification=error&reason=passwordErrada");
            exit;
        }

        $palavrapasseCriptografada = password_hash($novapalavrapasse, PASSWORD_DEFAULT);
        $updateQuery = $mysqli->prepare("UPDATE utilizadores SET palavrapasse = ? WHERE idutilizador = ?");
        $updateQuery->bind_param("si", $palavrapasseCriptografada, $idutilizador);

        if ($updateQuery->execute()) {
            header("Location: ../../perfil.php?notification=success&reason=sucessoAPP");
            exit;
        } else {
            header("Location: ../../perfil.php?notification=error&reason=erroPassword");
            exit;
        }
    }
}
?>
