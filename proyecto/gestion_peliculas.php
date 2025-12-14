<?php
require_once "conexion.php";
require_once "models/pelicula.php";

function cogerPeliculasBD($filtros = []) {
    global $conexion;

    $conexion->set_charset("utf8mb4");

    $sql = "SELECT * FROM Peliculas WHERE 1=1";
    $params = [];
    $tipos = "";

    if (!empty($filtros['titulo'])) {
        $sql .= " AND Titulo LIKE ?";
        $params[] = "%".$filtros['titulo']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['genero'])) {
        $sql .= " AND Genero = ?";
        $params[] = $filtros['genero'];
        $tipos .= "s";
    }

    if (!empty($filtros['director'])) {
        $sql .= " AND Director LIKE ?";
        $params[] = "%".$filtros['director']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['anio'])) {
        $sql .= " AND `Año_estreno` = ?";
        $params[] = (int)$filtros['anio'];
        $tipos .= "i";
    }

    $stmt = $conexion->prepare($sql);
    if (!$stmt) die("Error en la preparación: " . $conexion->error);

    if ($params) $stmt->bind_param($tipos, ...$params);

    $stmt->execute();
    $resultado = $stmt->get_result();

    $peliculas = [];
    while ($fila = $resultado->fetch_assoc()) {

        $adaptacionId = $fila['Adaptacion_Id'] ?? null;
        switch (strtolower($fila['Tipo_adaptacion'])) {
            case 'serie':
                $peliculas[] = new Serie(
                    $fila['Titulo'],
                    $fila['Año_estreno'],
                    $fila['Director'],
                    $fila['Actores'],
                    $fila['Genero'],
                    $adaptacionId
                );
                break;
            case 'corto':
                $peliculas[] = new Corto(
                    $fila['Titulo'],
                    $fila['Año_estreno'],
                    $fila['Director'],
                    $fila['Actores'],
                    $fila['Genero'],
                    $fila['Adaptacion_Id']
                );
                break;
            default:
                $peliculas[] = new Pelicula(
                    $fila['Titulo'],
                    $fila['Año_estreno'],
                    $fila['Director'],
                    $fila['Actores'],
                    $fila['Genero']
                );
                break;
        }
    }

    $stmt->close();
    return $peliculas;
}

?>
