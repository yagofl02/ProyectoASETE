<?php
require_once "conexion.php";
require_once "models/libro.php";

function cogerLibrosBD($filtros = []) {
    global $conexion;

    $conexion->set_charset("utf8mb4");

    $sql = "SELECT L.*, A.Nombre as NombreAutor 
            FROM Libros L 
            INNER JOIN Autores A ON L.Autor_id = A.Id 
            WHERE 1=1";
    $params = [];
    $tipos = "";

    if (!empty($filtros['titulo'])) {
        $sql .= " AND L.Titulo LIKE ?";
        $params[] = "%".$filtros['titulo']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['genero'])) {
        $sql .= " AND L.Genero = ?";
        $params[] = $filtros['genero'];
        $tipos .= "s";
    }

    if (!empty($filtros['autor'])) {
        $sql .= " AND A.Nombre LIKE ?";
        $params[] = "%".$filtros['autor']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['anio'])) {
        $sql .= " AND L.Año = ?";
        $params[] = (int)$filtros['anio'];
        $tipos .= "i";
    }

    $sql .= " ORDER BY L.Titulo";

    $consulta = $conexion->prepare($sql);
    if (!$consulta) die("Error en la preparación: " . $conexion->error);

    if ($params) $consulta->bind_param($tipos, ...$params);

    $consulta->execute();
    $resultado = $consulta->get_result();

    $libros = [];
    while ($fila = $resultado->fetch_assoc()) {
        
        $anio = $fila['Año'] ?? $fila['Anio'] ?? 0;
        
        $libros[] = new Libro(
            $fila['ID'],
            $fila['Titulo'],
            $fila['NombreAutor'],
            $anio, 
            $fila['Genero'],
            $fila['Editorial'] ?? '',
            $fila['Paginas'] ?? 0
        );
    }

    $consulta->close();
    return $libros;
}
?>