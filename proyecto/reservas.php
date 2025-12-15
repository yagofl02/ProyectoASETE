<?php
    session_start();
    require "internacionalizacion.php";
    require "conexion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    $usuario = $_SESSION["usuario"];
    
   
    $sql_cliente = "SELECT id FROM Usuarios WHERE usuario = ?";
    $consulta = $conexion->prepare($sql_cliente);
    $consulta->bind_param("s", $usuario);
    $consulta->execute();
    $resultado_cliente = $consulta->get_result();
    $cliente = $resultado_cliente->fetch_assoc();
    $idCliente = $cliente['id'];

    // reservas activas
    $sql = "SELECT R.*, 
                   L.Titulo as TituloLibro, 
                   P.Titulo as TituloPelicula,
                   CASE 
                       WHEN R.idLibro IS NOT NULL THEN 'libro'
                       ELSE 'pelicula'
                   END as tipo
            FROM Reservas R
            LEFT JOIN Libros L ON R.idLibro = L.ID
            LEFT JOIN Peliculas P ON R.idPelicula = P.ID
            WHERE R.idCliente = ? AND R.Fecha_devolucion IS NULL 
            ORDER BY R.Fecha_reserva DESC";
    $consulta = $conexion->prepare($sql);
    $consulta->bind_param("i", $idCliente);
    $consulta->execute();
    $reservas_activas = $consulta->get_result();

    // Historial
    $sql = "SELECT R.*, 
                   L.Titulo as TituloLibro, 
                   P.Titulo as TituloPelicula,
                   CASE 
                       WHEN R.idLibro IS NOT NULL THEN 'libro'
                       ELSE 'pelicula'
                   END as tipo
            FROM Reservas R
            LEFT JOIN Libros L ON R.idLibro = L.ID
            LEFT JOIN Peliculas P ON R.idPelicula = P.ID
            WHERE R.idCliente = ? AND R.Fecha_devolucion IS NOT NULL 
            ORDER BY R.Fecha_devolucion DESC";
    $consulta = $conexion->prepare($sql);
    $consulta->bind_param("i", $idCliente);
    $consulta->execute();
    $reservas_historico = $consulta->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Reservas - Proyecto ASETE</title>
    <link rel="stylesheet" href="style/catalogo.css">
    <link rel="stylesheet" href="style/idioma.css">
</head>
<body>
    <?php include "caja-idiomas.html"; ?>
    
    <h1><?= $traducciones["reservas"] ?></h1>
    
    <a href="catalogo_peliculas.php"><?= $traducciones["peliculas"] ?></a>
    <a href="catalogo_libros.php"><?= $traducciones["libros"] ?></a>
    
    <p><?= $traducciones["user"] ?>: <?php echo $_SESSION['usuario']; ?> | <a href="logout.php"><?= $traducciones["logout"] ?></a></p>

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

    <h2><?= $traducciones["activas"] ?></h2>
    <table>
        <tr>
            <th><?= $traducciones["title"] ?></th>
            <th><?= $traducciones["tipo"] ?></th>
            <th><?= $traducciones["fechar"] ?></th>
            <th><?= $traducciones["accion"] ?></th>
        </tr>
        
        <?php if($reservas_activas->num_rows > 0): ?>
            <?php while($reserva = $reservas_activas->fetch_assoc()): ?>
                <?php 
                    $titulo = ($reserva['tipo'] == 'libro') ? $reserva['TituloLibro'] : $reserva['TituloPelicula'];
                    $id_reserva = $reserva['idCliente'] . '-' . ($reserva['idLibro'] ?? $reserva['idPelicula']) . '-' . $reserva['tipo'];
                ?>
                <tr>
                    <td><?= $titulo ?></td>
                    <td><?= ucfirst($reserva['tipo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($reserva['Fecha_reserva'])) ?></td>
                    <td>
                        <form method="post" action="devolver.php" style="display: inline;">
                            <input type="hidden" name="idCliente" value="<?= $reserva['idCliente'] ?>">
                            <input type="hidden" name="idLibro" value="<?= $reserva['idLibro'] ?>">
                            <input type="hidden" name="idPelicula" value="<?= $reserva['idPelicula'] ?>">
                            <input type="hidden" name="tipo" value="<?= $reserva['tipo'] ?>">
                            <button type="submit" style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                Devolver
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4"><?= $traducciones["reservasa"] ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <h2 style="margin-top: 30px;"><?= $traducciones["reservasi"] ?></h2>
    <table>
        <tr>
            <th><?= $traducciones["title"] ?></th>
            <th><?= $traducciones["tipo"] ?></th>
            <th><?= $traducciones["fechar"] ?></th>
            <th><?= $traducciones["accion"] ?></th>
        </tr>
        
        <?php if($reservas_historico->num_rows > 0): ?>
            <?php while($reserva = $reservas_historico->fetch_assoc()): ?>
                <?php 
                    $titulo = ($reserva['tipo'] == 'libro') ? $reserva['TituloLibro'] : $reserva['TituloPelicula'];
                ?>
                <tr>
                    <td><?= $titulo ?></td>
                    <td><?= ucfirst($reserva['tipo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($reserva['Fecha_reserva'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($reserva['Fecha_devolucion'])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No tienes historial de reservas</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>