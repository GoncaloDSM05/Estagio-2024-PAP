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
        return $groupResult['codgrupo'];
    } else {
        return null;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['title'], $_POST['description'], $_POST['dueDate'], $_POST['dueTime'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $dueDate = $_POST['dueDate'];
        $dueTime = $_POST['dueTime'];

        // Obter o código do grupo do usuário logado
        $userId = $_SESSION['idutilizador'];
        $codgrupo = buscarGrupoEMembros($userId);

        if (!$codgrupo) {
            header("Location: ../../dashboard.php?notification=error&reason=missingCodGrupo");
            exit;
        }

        // Converter a data e hora para um formato adequado para inserção no banco de dados
        $dataHora = $dueDate . ' ' . $dueTime;
        $dataHoraFormatada = date('Y-m-d H:i:s', strtotime($dataHora));

        // Verificar se a data e hora são anteriores à data e hora atual
        $currentDateTime = date('Y-m-d H:i:s');
        $maxDueDate = date('Y-m-d H:i:s', strtotime('+3 year')); // Data máxima permitida, 3 anos a partir da data atual
        if ($dataHoraFormatada < $currentDateTime) {
            header("Location: ../../dashboard.php?notification=error&reason=dataInvalida");
            exit;
        } else if ($dataHoraFormatada > $maxDueDate) {
            header("Location: ../../dashboard.php?notification=error&reason=dataInvalida2");
            exit;
        }

        $insert_query = "INSERT INTO tarefasg (titulo, descricao, datahora, estado, codgrupo) VALUES (?, ?, ?, ?, ?)";

        $stmt = $mysqli->prepare($insert_query);

        if ($stmt) {
            $default_status = 'criada';
            // Bind dos parâmetros
            $stmt->bind_param("sssss", $title, $description, $dataHoraFormatada, $default_status, $codgrupo);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    header("Location: ../../dashboard.php?notification=success&reason=tarefaCriada");
                    exit;
                } else {
                    header("Location: ../../dashboard.php?notification=error&reason=falhaInserirTarefa");
                    exit;
                }
            } else {
                header("Location: ../../dashboard.php?notification=error&reason=erroExecutarStmt");
                exit;
            }
        } else {
            header("Location: ../../dashboard.php?notification=error&reason=erroPrepararStmt");
            exit;
        }
    } elseif (isset($_POST['idtarefa'])) {
        $idtarefa = $_POST['idtarefa'];

        $delete_query = "DELETE FROM tarefasg WHERE idtarefa = ?";

        $stmt = $mysqli->prepare($delete_query);

        if ($stmt) {
            $stmt->bind_param("i", $idtarefa);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    header("Location: ../../dashboard.php?notification=success&reason=tarefaRemovida");
                    exit;
                } else {
                    header("Location: ../../dashboard.php?notification=error&reason=falhaRemoverTarefa");
                    exit;
                }
            } else {
                header("Location: ../../dashboard.php?notification=error&reason=erroExecutarStmt");
                exit;
            }
        } else {
            header("Location: ../../dashboard.php?notification=error&reason=erroPrepararStmt");
            exit;
        }
    }
}
?>
