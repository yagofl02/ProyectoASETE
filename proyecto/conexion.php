<?php


$servidor="bbdd";
$usuario="root";
$contraseña="bbdd";
$nombre_bd="proyecto";

$conexion=new mysqli($servidor, $usuario, $contraseña, $nombre_bd);

if($conexion->connect_error){
    die("Error de conexion: " . $conexion->connect_error);
}

