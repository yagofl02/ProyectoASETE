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
    $autor = "";
    //Capturar filtros enviados por GET
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        //Es el operador de fusión de null en PHP (desde la versión 7).
        $genero = $_GET["genero"] ?? "";
        $año = $_GET["año"] ?? "";
        $autor = $_GET["autor"] ?? "";

        //Guardar en sesión para que persistan
        $_SESSION["genero_libro"] = $genero;
        $_SESSION["año_libro"] = $año;
        $_SESSION["autor"] = $autor;
    } else {
        //Si no hay GET, tomar los filtros almacenados en sesión
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

 // Se obtiene de la base de datos
 $libros = cogerLibrosBD($filtros);

 // Función para verificar si un libro está reservado
 function estaReservadoLibro($titulo, $conexion) {
    $sql = "SELECT * FROM reservas WHERE titulo = ? AND tipo = 'libro' AND fecha_devolucion IS NULL";
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
        <h1>Catálogo de Libros</h1>
        <a href="catalogo.php">Ver Películas</a>
        <a href="mis_reservas.php" style="color: orange;">Mis Reservas</a>
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
                <th>Título</th>
                <th>Autor</th>
                <th>Año</th>
                <th>Género</th>
                <th>Editorial</th>
                <th>Páginas</th>
                <th>Disponibilidad</th>
                <th>Acción</th>
            </tr>

            <?php foreach ($libros as $libro): ?>
                <?php if(
                         ($libro->anio == $año || $año == "") && 
                         ($libro->genero == $genero || $genero == "") && 
                         (stripos($libro->autor, $autor) != false || $autor == "")
                        ): ?>
                    
                    <?php $reservado = estaReservadoLibro($libro->titulo, $conexion); ?>

                    <tr>
                        
                        <td><?= $libro->titulo ?></td>
                        <td><?= $libro->autor ?></td>
                        <td><?= $libro->anio ?></td>
                        <td><?= $libro->genero ?></td>
                        <td><?= $libro->editorial ?></td>
                        <td><?= $libro->paginas ?></td>
                        <td>
                            <?php if($reservado): ?>
                                <span style="color: red; font-weight: bold;">No Disponible</span>
                            <?php else: ?>
                                <span style="color: green; font-weight: bold;">Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$reservado): ?>
                                <form method="post" action="reservar.php" style="display: inline;">
                                    <input type="hidden" name="titulo" value="<?= $libro->titulo ?>">
                                    <input type="hidden" name="tipo" value="libro">
                                    <button type="submit" style="background: #007bff; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                        Reservar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span style="color: #999;">Reservado</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php $contador_libros = ($contador_libros ?? 0) + 1; ?>

                <?php endif; ?>

            <?php endforeach; ?>

            <?php if(empty($contador_libros)): ?>

                <tr>
                    <td colspan="8">No se ha encontrado ningún libro que coincida con la selección</td>
                </tr>

            <?php endif; ?>

        </table>

    </body>
</html>