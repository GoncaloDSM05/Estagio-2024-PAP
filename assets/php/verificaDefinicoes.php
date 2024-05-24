<?php
include 'conecta.php';
session_start();

function buscarGrupoEMembros($userId) {
    global $mysqli;
    $query = "SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($groupResult = $result->fetch_assoc()) {
        $groupId = $groupResult['codgrupo'];
        $query = "SELECT g.*, u.primeironome AS donoPrimeiroNome, u.ultimonome AS donoUltimoNome FROM grupos g INNER JOIN utilizadores u ON g.idutilizadorDono = u.idutilizador WHERE g.codgrupo = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $groupId);
        $stmt->execute();
        $groupResult = $stmt->get_result();
        $group = $groupResult->fetch_assoc();
        $isOwner = ($group['idutilizadorDono'] == $userId);

        $query = "SELECT u.idutilizador, u.primeironome, u.ultimonome, u.nomeutilizador, u.email, u.fotoPath, u.nomefuncao FROM utilizadores u JOIN utilizadorgrupo ug ON u.idutilizador = ug.idutilizador WHERE ug.codgrupo = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('i', $groupId);
        $stmt->execute();
        $membersResult = $stmt->get_result();
        $members = $membersResult->fetch_all(MYSQLI_ASSOC);

        return array('group' => $group, 'isOwner' => $isOwner, 'members' => $members);
    } else {
        return null;
    }
}

function atualizarGrupo($userId, $groupName, $groupGuidelines, $groupDescription) {
    global $mysqli;
    $query = "SELECT idutilizadorDono FROM grupos WHERE codgrupo = (SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($group = $result->fetch_assoc()) {
        if ($group['idutilizadorDono'] == $userId) {
            $query = "UPDATE grupos SET nome = ?, diretrizes = ?, descricao = ? WHERE idutilizadorDono = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param('sssi', $groupName, $groupGuidelines, $groupDescription, $userId);
            if ($stmt->execute()) {
                header("Location: ../../dashboard.php?notification=success&reason=atualizouGrupo");
                exit;
            } else {
                header("Location: ../../dashboard.php?notification=error&reason=erroAtualizaGrupo");
                exit;
            }
        } else {
            echo "Você não tem permissão para editar este grupo.";
        }
    } else {
        echo "Grupo não encontrado.";
    }
}

function verificarPalavraPasse($userId, $password) {
    global $mysqli;
    $query = "SELECT palavrapasse FROM utilizadores WHERE idutilizador = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    return password_verify($password, $user['palavrapasse']);
}

function removerMembro($adminId, $userId, $password) {
    global $mysqli;

    if ($adminId === $userId) {
        header("Location: ../../dashboard.php?notification=error&reason=naoPodeRemoverDono");
        exit;
    }

    if (!verificarPalavraPasse($adminId, $password)) {
        header("Location: ../../dashboard.php?notification=error&reason=ppIncorreta");
        exit;
    }

    $query = "SELECT idutilizadorDono FROM grupos WHERE codgrupo IN (SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($group = $result->fetch_assoc()) {
        if ($group['idutilizadorDono'] == $userId) {
            header("Location: ../../dashboard.php?notification=error&reason=naoPodeRemoverDono");
            exit;
        }
    }

    $query = "DELETE FROM utilizadorgrupo WHERE idutilizador = ? AND codgrupo IN (SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('ii', $userId, $adminId);
    if ($stmt->execute()) {
        header("Location: ../../dashboard.php?notification=success&reason=membroRemovido");
        exit;
    } else {
        header("Location: ../../dashboard.php?notification=error&reason=erroMembroRemovido");
        exit;
    }
}

function transferirAdmin($adminId, $userId, $password) {
    global $mysqli;

    if ($adminId === $userId) {
        header("Location: ../../dashboard.php?notification=error&reason=naoPodeTransferirDono");
        exit;
    }

    if (!verificarPalavraPasse($adminId, $password)) {
        header("Location: ../../dashboard.php?notification=error&reason=ppIncorreta");
        exit;
    }

    $query = "SELECT idutilizadorDono FROM grupos WHERE codgrupo IN (SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($group = $result->fetch_assoc()) {
        if ($group['idutilizadorDono'] == $userId) {
            header("Location: ../../dashboard.php?notification=error&reason=naoPodeTransferirDono");
            exit;
        }
    }

    $query = "UPDATE grupos SET idutilizadorDono = ? WHERE idutilizadorDono = ? AND codgrupo IN (SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?)";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('iii', $userId, $adminId, $adminId);
    if ($stmt->execute()) {
        header("Location: ../../dashboard.php?notification=success&reason=transferiuAdmin");
        exit;
    } else {
        header("Location: ../../dashboard.php?notification=error&reason=erroTransferiuAdmin");
        exit;
    }
}

function exitMember($userId) {
    global $mysqli;

    $queryCheckOwner = "SELECT codgrupo FROM grupos WHERE idutilizadorDono = ?";
    $stmtCheckOwner = $mysqli->prepare($queryCheckOwner);
    $stmtCheckOwner->bind_param('i', $userId);
    $stmtCheckOwner->execute();
    $stmtCheckOwner->store_result();

    if ($stmtCheckOwner->num_rows > 0) {
        header("Location: ../../dashboard.php?notification=error&reason=saidaDono");
        exit;
    }

    $queryRemoveMember = "DELETE FROM utilizadorgrupo WHERE idutilizador = ?";
    $stmtRemoveMember = $mysqli->prepare($queryRemoveMember);
    $stmtRemoveMember->bind_param('i', $userId);

    if ($stmtRemoveMember->execute()) {
        header("Location: ../../dashboard.php?notification=success&reason=saiuGrupo");
        exit;
    } else {
        header("Location: ../../dashboard.php?notification=error&reason=erroSaiuGrupo");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $userId = $_SESSION['idutilizador'];

        if ($action === 'editGroup') {
            if (isset($_POST['groupName']) && isset($_POST['groupGuidelines']) && isset($_POST['groupDescription'])) {
                $groupName = $_POST['groupName'];
                $groupGuidelines = $_POST['groupGuidelines'];
                $groupDescription = $_POST['groupDescription'];
                atualizarGrupo($userId, $groupName, $groupGuidelines, $groupDescription);
            }
        } elseif ($action === 'removeMember') {
            if (isset($_POST['userId']) && isset($_POST['password'])) {
                $userIdToRemove = $_POST['userId'];
                $password = $_POST['password'];
                removerMembro($userId, $userIdToRemove, $password);
            }
        } elseif ($action === 'transferOwnership') {
            if (isset($_POST['userId']) && isset($_POST['password'])) {
                $userIdToTransfer = $_POST['userId'];
                $password = $_POST['password'];
                transferirAdmin($userId, $userIdToTransfer, $password);
            }
        } elseif ($action === 'exitMember') {
            if (isset($_POST['userId'])) {
                $userIdToExit = $_POST['userId'];
                exitMember($userIdToExit);
            }
        }
    }
}
?>
