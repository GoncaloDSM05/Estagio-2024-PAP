<?php
include 'conecta.php'; // Inclua o arquivo de conexão com o banco de dados
session_start();

$userId = $_SESSION['idutilizador'] ?? null;

function buscarGrupo($userId) {
    global $mysqli;
    $query = "SELECT codgrupo FROM utilizadorgrupo WHERE idutilizador = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $groupResult = $result->fetch_assoc()) {
        return $groupResult['codgrupo'];
    } else {
        return null;
    }
}

$action = $_REQUEST['action'] ?? '';

if ($action == 'addEvent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['title'] ?? '';
    $cor = $_POST['color'] ?? '';
    $inicio = $_POST['startHour'] ?? '';
    $fim = $_POST['endHour'] ?? '';
    $selectedDate = $_POST['selectedDate'] ?? '';
    $codgrupo = buscarGrupo($userId);

    if ($titulo && $cor && $inicio && $fim && $selectedDate && $codgrupo !== null) {
        $inicioCompleto = $selectedDate . ' ' . $inicio;
        $fimCompleto = $selectedDate . ' ' . $fim;

        $query = $mysqli->prepare("INSERT INTO eventos (titulo, cor, inicio, fim, codgrupo) VALUES (?, ?, ?, ?, ?)");
        $query->bind_param("sssss", $titulo, $cor, $inicioCompleto, $fimCompleto, $codgrupo);

        if ($query->execute()) {
            header("Location: ../../dashboard.php?notification=success&reason=eventoCriado");
            exit();
        } else {
            header("Location: ../../dashboard.php?notification=error&reason=erroeventoCriado");
            exit();
        }
    } else {
        header("Location: ../../dashboard.php?notification=error&reason=camposInvalidos");
        exit();
    }
} elseif ($action == 'fetch_events' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $codgrupo = buscarGrupo($userId);

    if ($codgrupo != null) {
        $query = $mysqli->prepare("SELECT idevento, titulo, cor, inicio, fim FROM eventos WHERE codgrupo = ?");
        $query->bind_param('i', $codgrupo);
        $query->execute();
        $result = $query->get_result();

        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'id' => $row['idevento'],
                'title' => $row['titulo'],
                'start' => $row['inicio'],
                'end' => $row['fim'],
                'color' => $row['cor']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($events);
        exit();
    }
}

if ($action == 'updateEvent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $titulo = $_POST['title'] ?? '';
    $inicio = $_POST['start'] ?? '';
    $fim = $_POST['end'] ?? '';
    $cor = $_POST['color'] ?? '';

    if ($id && $titulo && $inicio && $cor) {
        $query = $mysqli->prepare("UPDATE eventos SET titulo = ?, inicio = ?, fim = ?, cor = ? WHERE idevento = ?");
        $query->bind_param("ssssi", $titulo, $inicio, $fim, $cor, $id);

        if ($query->execute()) {
            header("Location: ../../dashboard.php?notification=success&reason=eventoAtualizado");
            exit();
        } else {
            header("Location: ../../dashboard.php?notification=error&reason=erroeventoAtualizado");
            exit();
        }
    } else {
        header("Location: ../../dashboard.php?notification=error&reason=dadosInvalidos");
        exit();
    }
}

echo json_encode(['error' => 'Requisição inválida']);
exit();
