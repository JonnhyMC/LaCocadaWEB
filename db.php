<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '1104jonathan';   // la misma que pusiste en phpMyAdmin
$DB_NAME = 'lacocada';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    die('Error de conexión a la base de datos: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8mb4');
?>
