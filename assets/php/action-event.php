<?php
session_start();
include_once 'conecta.php';

$dados = filter_input_array(INPUT_POST);

if (!empty($dados['action']) && $dados['action'] === 'delete') {
    $sql = $db->prepare("DELETE FROM eventos WHERE idevento = :id");
    $sql->bindValue(":id", $dados['id']);
    if (!$sql->execute()) {
        $_SESSION['msg'] = "<p class='alert-danger'>Erro! Evento não existe ou já foi deletado.</p>";
        header('Location: ../../dashboard.php');
        exit;
    }
    $_SESSION['msg'] = "<p class='alert-success'>Sucesso! Evento excluído.</p>";
    header('Location: ../../dashboard.php');
    exit;
}

unset($dados['action']);

if ($dados['start'] >= $dados['end']) {
    $_SESSION['msg'] = "<p class='alert-danger'>Erro! Preencha as datas corretamente e tente novamente.</p>";
    header('Location: ../../dashboard.php');
    exit;
}

$required = ['title', 'color', 'start'];
$translate = [
    'titulo' => 'Nome do Evento',
    'cor' => 'Cor',
    'inicio' => 'Início do Evento',
];

foreach ($dados as $key => $value) {
    if (in_array($key, $required) && empty($value)) {
        $_SESSION['msg'] = "<p class='alert-danger'>Erro! O campo " . $translate[$key] . " é obrigatório.</p>";
        header('Location: ../../dashboard.php');
        exit;
    }

    $set[] = $key . ' = :' . $key;
}
$set = implode(', ', $set);

if (!$dados['id']) {
    $sql = $db->prepare("INSERT INTO events SET $set");
    foreach ($dados as $key => $value) {
        $sql->bindValue(":" . $key, $value);
    }
    $sql->execute();
    $_SESSION['msg'] = "<p class='alert-success'>Sucesso! Evento salvo.</p>";
    header('Location: ../../dashboard.php');
    exit;
} else {
    $sql = $db->prepare("UPDATE events SET $set WHERE id = :id");
    foreach ($dados as $key => $value) {
        $sql->bindValue(":" . $key, $value);
    }
    $sql->execute();
    $_SESSION['msg'] = "<p class='alert-success'>Sucesso! Evento alterado.</p>";
    header('Location: ../../dashboard.php');
    exit;
}
