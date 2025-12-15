<?php
    session_start();
    require "internacionalizacion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    require "conexion.php";

    $genero = "";
    $año = "";
    $director = "";
    
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $genero = $_GET["genero"] ?? "";
        $año = $_GET["año"] ?? "";
        $director = $_GET["director"] ?? "";

        $_SESSION["genero"] = $genero;
        $_SESSION["año"] = $año;
        $_SESSION["director"] = $director;
    } else {
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

    $peliculas = cogerPeliculasBD($filtros);

    function estaReservada($idPelicula, $conexion) {
        $sql = "SELECT * FROM Reservas WHERE idPelicula = ? AND Fecha_devolucion IS NULL";
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param("i", $idPelicula);
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
        <h1><?= $traducciones["catalog"] ?></h1>
        <a href="filtro.php"><?= $traducciones["filter"] ?></a>
        <a href="catalogo_libros.php" style="color: purple;"><?= $traducciones["libros"] ?></a>
        <a href="reservas.php" style="color: orange;"><?= $traducciones["reservas"] ?></a>
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
                <th><?= $traducciones["tipo"] ?></th>
                <th><?= $traducciones["disponibilidad"] ?></th>
                <th><?= $traducciones["accion"] ?></th>
            </tr>

            <?php $contador_peliculas = 0; ?>
            <?php foreach ($peliculas as $pelicula): ?>
                <?php 
                $cumpleFiltro = ($pelicula->año == $año || $año == "") && 
                               ($pelicula->genero == $genero || $genero == "") && 
                               (stripos($pelicula->director, $director) !== false || $director == "");
                
                if($cumpleFiltro): 
                ?>
                    
                    <?php 
                    $idPelicula = $pelicula->ID ?? $pelicula->id ?? null;
                    
                    if ($idPelicula !== null) {
                        $reservada = estaReservada($idPelicula, $conexion);
                    } else {
                        $reservada = false;
                    }
                    ?>

                    <tr>
                        <td><?= htmlspecialchars($pelicula->titulo ?? '') ?></td>
                        <td><?= htmlspecialchars($pelicula->año ?? '') ?></td>
                        <td><?= htmlspecialchars($pelicula->director ?? '') ?></td>
                        <td><?= htmlspecialchars($pelicula->actores ?? '') ?></td>
                        <td><?= htmlspecialchars($pelicula->genero ?? '') ?></td>
                        <td>
                            <?php 
                            if($pelicula instanceof Serie) {
                                echo 'Serie: ' . htmlspecialchars($pelicula->n_temporadas ?? '1') . ' temp';
                            } elseif($pelicula instanceof Corto) {
                                echo 'Corto: ' . htmlspecialchars($pelicula->duracion ?? '15') . ' min';
                            } else {
                                echo 'Película';
                            }
                            ?>
                        </td>
                        <td>
                            <?php if($reservada): ?>
                                <span style="color: red; font-weight: bold;"><?= $traducciones["no disponible"] ?></span>
                            <?php else: ?>
                                <span style="color: green; font-weight: bold;"><?= $traducciones["disponible"] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$reservada && $idPelicula): ?>
                                <form method="post" action="reservar.php" style="display: inline;">
                                    <input type="hidden" name="id_producto" value="<?= $idPelicula ?>">
                                    <input type="hidden" name="tipo" value="pelicula">
                                    <button type="submit" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                        <?= $traducciones["reservar"] ?>
                                    </button>
                                </form>
                            <?php elseif($reservada): ?>
                                <span style="color: #999;"><?= $traducciones["reservado"] ?></span>
                            <?php else: ?>
                                <span style="color: #999;"><?= $traducciones["no disponible"] ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php $contador_peliculas++; ?>

                <?php endif; ?>

            <?php endforeach; ?>

            <?php if($contador_peliculas == 0): ?>
                <tr>
                    <td colspan="8">No se ha encontrado ninguna película que coincida con la selección</td>
                </tr>
            <?php endif; ?>

        </table>

    </body>
</html>