<?php
include 'conecta.php'; // Inclua o arquivo de conexão com o banco de dados
session_start();

$userId = $_SESSION['idutilizador'] ?? null;

function buscarGrupo($userId)
{
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

// Verifica se a variável de ação está definida e se é uma solicitação POST
$action = $_POST['action'] ?? '';

if ($action == 'addEvent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar Evento
    $titulo = $_POST['title'] ?? '';
    $cor = $_POST['color'] ?? '';
    $inicio = $_POST['startHour'] ?? '';
    $fim = $_POST['endHour'] ?? '';
    $selectedDate = $_POST['selectedDate'] ?? ''; // Nova variável para a data selecionada
    $codgrupo = buscarGrupo($userId);

    if ($titulo && $cor && $inicio && $fim && $selectedDate && $codgrupo !== null) {
        // Combinar a data selecionada com a hora de início e término
        $inicioCompleto = $selectedDate . ' ' . $inicio;
        $fimCompleto = $selectedDate . ' ' . $fim;

        // Preparar e executar a consulta SQL para inserir o evento
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
} elseif ($action == 'fetch_events') {
    // Buscar Eventos
    $codgrupo = buscarGrupo($userId);

    if ($codgrupo !== null) {
        $query = $mysqli->prepare("SELECT titulo, cor, inicio, fim FROM eventos WHERE codgrupo = ?");
        $query->bind_param('s', $codgrupo);
        $query->execute();
        $result = $query->get_result();

        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'title' => $row['titulo'],
                'start' => $row['inicio'],
                'end' => $row['fim'],
                'color' => $row['cor']
            ];
        }

        echo json_encode($events);
        exit();
    }
}

echo json_encode(['error' => 'Requisição inválida']);
