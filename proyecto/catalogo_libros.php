<?php
    session_start();
    require "internacionalizacion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    require "conexion.php";

    //variables para los filtros
    $genero = "";
    $año = "";
    $autor = "";
    
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $genero = $_GET["genero"] ?? "";
        $año = $_GET["año"] ?? "";
        $autor = $_GET["autor"] ?? "";

        $_SESSION["genero_libro"] = $genero;
        $_SESSION["año_libro"] = $año;
        $_SESSION["autor"] = $autor;
    } else {
        $genero = $_SESSION["genero_libro"] ?? "";
        $año = $_SESSION["año_libro"] ?? "";
        $autor = $_SESSION["autor"] ?? "";
    }

    require_once "gestion_libros.php";

    $filtros = [
        'genero'   => $genero,
        'anio'     => $año,
        'autor'    => $autor
    ];

    $libros = cogerLibrosBD($filtros);

    function estaReservadoLibro($idLibro, $conexion) {
        $sql = "SELECT * FROM Reservas WHERE idLibro = ? AND Fecha_devolucion IS NULL";
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param("i", $idLibro);
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
        <h1><?= $traducciones["libros"] ?></h1>
        <a href="catalogo_peliculas.php"><?= $traducciones["peliculas"] ?></a>
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
                <th><?= $traducciones["author"] ?></th>
                <th><?= $traducciones["year"] ?></th>
                <th><?= $traducciones["genre"] ?></th>
                <th><?= $traducciones["editorial"] ?></th>
                <th><?= $traducciones["paginas"] ?></th>
                <th><?= $traducciones["disponibilidad"] ?></th>
                <th><?= $traducciones["accion"] ?></th>
            </tr>

            <?php $contador_libros = 0; ?>
            <?php foreach ($libros as $libro): ?>
                <?php 
                $libroAño = $libro->anio ?? $libro->año ?? '';
                $libroGenero = $libro->genero ?? '';
                $libroAutor = $libro->autor ?? '';
                
                $cumpleFiltro = ($libroAño == $año || $año == "") && 
                               ($libroGenero == $genero || $genero == "") && 
                               (stripos($libroAutor, $autor) !== false || $autor == "");
                
                if($cumpleFiltro): 
                ?>
                    
                    <?php 
                    $idLibro = $libro->ID ?? $libro->id ?? null;
                    $reservado = false;
                    
                    if ($idLibro !== null) {
                        $reservado = estaReservadoLibro($idLibro, $conexion);
                    }
                    ?>

                    <tr>
                        <td><?= htmlspecialchars($libro->titulo ?? '') ?></td>
                        <td><?= htmlspecialchars($libroAutor) ?></td>
                        <td><?= htmlspecialchars($libroAño) ?></td>
                        <td><?= htmlspecialchars($libroGenero) ?></td>
                        <td><?= htmlspecialchars($libro->editorial ?? '') ?></td>
                        <td><?= htmlspecialchars($libro->paginas ?? '') ?></td>
                        <td>
                            <?php if($reservado): ?>
                                <span style="color: red; font-weight: bold;"><?= $traducciones["no disponible"] ?></span>
                            <?php else: ?>
                                <span style="color: green; font-weight: bold;"><?= $traducciones["disponible"] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$reservado && $idLibro): ?>
                                <form method="post" action="reservar.php" style="display: inline;">
                                    <input type="hidden" name="id_producto" value="<?= $idLibro ?>">
                                    <input type="hidden" name="tipo" value="libro">
                                    <button type="submit" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                        <?= $traducciones["reservar"] ?>
                                    </button>
                                </form>
                            <?php elseif($reservado): ?>
                                <span style="color: #999;"><?= $traducciones["reservado"] ?></span>
                            <?php else: ?>
                                <span style="color: #999;"><?= $traducciones["no disponible"] ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php $contador_libros++; ?>

                <?php endif; ?>

            <?php endforeach; ?>

            <?php if($contador_libros == 0): ?>
                <tr>
                    <td colspan="8">No se ha encontrado ningún libro que coincida con la selección</td>
                </tr>
            <?php endif; ?>

        </table>

    </body>
</html>