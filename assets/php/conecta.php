<?php

$hostname = 'localhost';
$username = 'root';
$password = '';
$basedados = 'squadforge';

$mysqli = mysqli_connect($hostname, $username, $password, $basedados);

if (!$mysqli) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
