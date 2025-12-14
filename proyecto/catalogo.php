<?php

    session_start();

    require "internacionalizacion.php";

    //Si no hay usuario logueado (el que sea, nos da igual con que sea uno), lo redirijo al login
    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    //Conectar a la base de datos para verificar disponibilidad
    require "conexion.php";

    //Inicializamos los filtros
    $genero = "";
    $año = "";
    $director = "";
    //Capturar filtros enviados por GET
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        //Es el operador de fusión de null en PHP (desde la versión 7).
        $genero = $_GET["genero"] ?? "";
        $año = $_GET["año"] ?? "";
        $director = $_GET["director"] ?? "";

        //Guardar en sesión para que persistan
        $_SESSION["genero"] = $genero;
        $_SESSION["año"] = $año;
        $_SESSION["director"] = $director;
    } else {
        //Si no hay GET, tomar los filtros almacenados en sesión
        $genero = $_SESSION["genero"] ?? "";
        $año = $_SESSION["año"] ?? "";
        $director = $_SESSION["director"] ?? "";
    }

    require_once "gestion_peliculas.php";

    $filtros = [
    'genero'   => $genero,
    'anio'     => $año,
    'director' => $director
];

    // Obtenemos las películas filtradas desde la BD
    $peliculas = cogerPeliculasBD($filtros);

    // Función para verificar si una película está reservada
    function estaReservada($titulo, $conexion) {
        $sql = "SELECT * FROM reservas WHERE titulo = ? AND tipo = 'pelicula' AND fecha_devolucion IS NULL";
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param("s", $titulo);
        $consulta->execute();
        $resultado = $consulta->get_result();
        return $resultado->num_rows > 0;
    }

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Proyecto ASETE 1</title>
        <link rel="stylesheet" href="style/catalogo.css">
        <link rel="stylesheet" href="style/idioma.css">
    </head>
    <body>

        <?php include "caja-idiomas.html"; ?>
        <h1><?= $traducciones["catalog"] ?> de Películas</h1>
        <a href="filtro.php"><?= $traducciones["filter"] ?></a>
        <a href="agregar_peliculas.php" style="color: green;"><?= $traducciones["new_film"] ?></a>
        <a href="catalogo_libros.php" style="color: purple;">Ver Libros</a>
        <a href="reservas.php" style="color: orange;">Mis Reservas</a>
        <p><?= $traducciones["user"] ?>: <?php echo $_SESSION['usuario']; ?> | <a href="logout.php"><?= $traducciones["logout"] ?></p>

        <?php if(isset($_SESSION["mensaje_exito"])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $_SESSION["mensaje_exito"]; unset($_SESSION["mensaje_exito"]); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION["mensaje_error"])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $_SESSION["mensaje_error"]; unset($_SESSION["mensaje_error"]); ?>
            </div>
        <?php endif; ?>

        <table>
            <tr>
                <th><?= $traducciones["title"] ?></th>
                <th><?= $traducciones["year"] ?></th>
                <th><?= $traducciones["director"] ?></th>
                <th><?= $traducciones["actors"] ?></th>
                <th><?= $traducciones["genre"] ?></th>
                <th><?= $traducciones["seassons"] ?></th>
                <th><?= $traducciones["duration"] ?></th>
                <th>Disponibilidad</th>
                <th>Acción</th>
            </tr>

            <?php foreach ($peliculas as $pelicula): ?>
                <?php if(
                         ($pelicula->año == $año || $año == "") && 
                         ($pelicula->genero == $genero || $genero == "") && 
                         (stripos($pelicula->director, $director) != false || $director == "")
                        ): ?>
                    
                    <?php $reservada = estaReservada($pelicula->titulo, $conexion); ?>

                    <tr>
                        
                        <td><?= $pelicula->titulo ?></td>
                        <td><?= $pelicula->año ?></td>
                        <td><?= $pelicula->director ?></td>
                        <td><?= $pelicula->actores ?></td>
                        <td><?= $pelicula->genero ?></td>
                        <td>
                            <?php if($pelicula instanceof Serie): ?>
                            <?= $pelicula->n_temporadas ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($pelicula instanceof Corto): ?>
                            <?= $pelicula->duracion ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($reservada): ?>
                                <span style="color: red; font-weight: bold;">No Disponible</span>
                            <?php else: ?>
                                <span style="color: green; font-weight: bold;">Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$reservada): ?>
                                <form method="post" action="reservar.php" style="display: inline;">
                                    <input type="hidden" name="titulo" value="<?= $pelicula->titulo ?>">
                                    <input type="hidden" name="tipo" value="pelicula">
                                    <button type="submit" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                        Reservar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color: #999;">Reservado</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php $contador_peliculas = ($contador_peliculas ?? 0) + 1; ?>

                <?php endif; ?>
                <?php //echo $pelicula->mostrarPelicula(); ?>
                <?php //echo $pelicula->toJSON(); ?>

            <?php endforeach; ?>

            <?php if(empty($contador_peliculas)): ?>

                <tr>
                    <td colspan="9">No se ha encontrado ninguna película que coincida con la selección</td>
                </tr>

            <?php endif; ?>

        </table>

    </body>
</html>