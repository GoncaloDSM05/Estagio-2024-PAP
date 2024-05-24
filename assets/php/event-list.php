<?php
session_start();
include_once "conecta.php";

$sql = $db->query("SELECT idevento, titulo, cor, inicio, fim, codgrupo FROM eventos");
$events = $sql->fetchAll();

$evs = [];
foreach ($events as $event) {
    extract($event);

    $evs[] = [
        'idevento' => $id,
        'titulo' => $title,
        'cor' => $color,
        'inicio' => $start,
        'fim' => $end,
        'codgrupo' => $codgrupo,
    ];
}

echo json_encode($evs);
