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
        $tipoAdaptacion = strtolower($fila['Tipo_adaptacion']);
        
        if ($tipoAdaptacion == 'serie') {
            $n_temporadas = $fila['Adaptacion_id'] ?? 1;
            $pelicula = new Serie(
                $fila['Titulo'],
                $fila['Año_estreno'],
                $fila['Director'],
                $fila['Actores'],
                $fila['Genero'],
                $n_temporadas
            );
            $pelicula->ID = $fila['ID'];
            $pelicula->n_temporadas = $n_temporadas;
            $peliculas[] = $pelicula;
        }
        elseif ($tipoAdaptacion == 'corto') {
            $duracion = $fila['Adaptacion_id'] ?? 15;
            $pelicula = new Corto(
                $fila['Titulo'],
                $fila['Año_estreno'],
                $fila['Director'],
                $fila['Actores'],
                $fila['Genero'],
                $duracion
            );
            $pelicula->ID = $fila['ID'];
            $pelicula->duracion = $duracion;
            $peliculas[] = $pelicula;
        }
        else {
            $pelicula = new Pelicula(
                $fila['Titulo'],
                $fila['Año_estreno'],
                $fila['Director'],
                $fila['Actores'],
                $fila['Genero']
            );
            $pelicula->ID = $fila['ID'];
            $peliculas[] = $pelicula;
        }
    }

    $stmt->close();
    return $peliculas;
}
?>