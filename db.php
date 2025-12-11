<?php

$mysqli = new mysqli(
    getenv("MYSQLHOST"),
    getenv("MYSQLUSER"),
    getenv("MYSQLPASSWORD"),
    getenv("MYSQLDATABASE"),
    intval(getenv("MYSQLPORT"))
);

if ($mysqli->connect_errno) {
    die("Error al conectar con MySQL: " . $mysqli->connect_error);
}

?>
