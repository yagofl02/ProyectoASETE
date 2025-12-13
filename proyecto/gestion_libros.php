<?php
require_once "conexion.php";
require_once "models/libro.php";


function cogerLibrosBD($filtros = []) {
    global $conexion;

    $sql = "SELECT * FROM libros WHERE 1=1";
    $params = [];
    $tipos = "";

    if (!empty($filtros['titulo'])) {
        $sql .= " AND titulo LIKE ?";
        $params[] = "%".$filtros['titulo']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['genero'])) {
        $sql .= " AND genero = ?";
        $params[] = $filtros['genero'];
        $tipos .= "s";
    }

    if (!empty($filtros['autor'])) {
        $sql .= " AND autor LIKE ?";
        $params[] = "%".$filtros['autor']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['anio'])) {
        $sql .= " AND anio = ?";
        $params[] = (int)$filtros['anio'];
        $tipos .= "i";
    }

    $stmt = $conexion->prepare($sql);
    if (!$stmt) die("Error en la preparación: " . $conexion->error);

    if ($params) $stmt->bind_param($tipos, ...$params);

    $stmt->execute();
    $resultado = $stmt->get_result();

    $libros = [];
    while ($fila = $resultado->fetch_assoc()) {
        $libros[] = new Libro(
            $fila['id'],
            $fila['titulo'],
            $fila['autor'],
            $fila['anio'],
            $fila['genero'],
            $fila['editorial'] ?? '',
            $fila['paginas'] ?? 0
        );
    }

    $stmt->close();
    return $libros;
}
?>