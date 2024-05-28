<?php
include 'conecta.php';
session_start();

function uploadFoto($foto) {
    $diretorioDestino = "../../fotos_perfil/";
    $nomeOriginal = basename($foto['name']);
    $tipoArquivo = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

    // Verificar se o arquivo é realmente uma imagem
    $check = getimagesize($foto['tmp_name']);
    if ($check === false) {
        return false;
    }

    // Limitar tipos de arquivo
    $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($tipoArquivo, $tiposPermitidos)) {
        return false;
    }

    // Gerar um novo nome de arquivo único para evitar conflitos
    $novoNomeArquivo = uniqid() . '_' . time() . '.' . $tipoArquivo;
    $arquivoDestino = $diretorioDestino . $novoNomeArquivo;

    // Mover arquivo para o diretório de uploads
    if (move_uploaded_file($foto['tmp_name'], $arquivoDestino)) {
        return $arquivoDestino;
    } else {
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verificar se o usuário está logado
    if (!isset($_SESSION['idutilizador'])) {
        header("Location: ../../conta.html");
        exit;
    }

    $idutilizador = $_SESSION['idutilizador'];

    if ($_POST['action'] == 'alterarFoto' && isset($_FILES['foto'])) {
        // Processar a foto
        $novoCaminhoFoto = uploadFoto($_FILES['foto']);
        if ($novoCaminhoFoto === false) {
            header("Location: ../../perfil.php?notification=error&reason=erroImagem");
            exit;
        } else {
            $query = $mysqli->prepare("UPDATE utilizadores SET fotoPath = ? WHERE idutilizador = ?");
            if ($query) {
                $query->bind_param("si", $novoCaminhoFoto, $idutilizador);
                if ($query->execute()) {
                    header("Location: ../../perfil.php?notification=success&reason=sucessoImagem");
                    exit;
                } else {
                    header("Location: ../../perfil.php?notification=error&reason=erroImagem");
                    exit;
                }
            } else {
                header("Location: ../../perfil.php?notification=error&reason=erroImagem");
                exit;
            }
        }
    }
}
?>
