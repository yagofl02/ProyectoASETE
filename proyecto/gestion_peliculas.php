<?php
require_once "conexion.php";
require_once "models/pelicula.php";

function cogerPeliculasBD($filtros = []) {
    global $conexion;

    $conexion->set_charset("utf8mb4");

    $sql = "SELECT * FROM Peliculas WHERE 1=1";
    $parametros = [];
    $tipos = "";

    if (!empty($filtros['titulo'])) {
        $sql .= " AND Titulo LIKE ?";
        $parametros[] = "%".$filtros['titulo']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['genero'])) {
        $sql .= " AND Genero = ?";
        $parametros[] = $filtros['genero'];
        $tipos .= "s";
    }

    if (!empty($filtros['director'])) {
        $sql .= " AND Director LIKE ?";
        $parametros[] = "%".$filtros['director']."%";
        $tipos .= "s";
    }

    if (!empty($filtros['anio'])) {
        $sql .= " AND `Año_estreno` = ?";
        $parametros[] = (int)$filtros['anio'];
        $tipos .= "i";
    }

    $consulta = $conexion->prepare($sql);
    

    if ($parametros){
        $consulta->bind_param($tipos, ...$parametros);
    }
    $consulta->execute();
    $resultado = $consulta->get_result();

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

    $consulta->close();
    return $peliculas;
}
?>