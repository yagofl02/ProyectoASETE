<?php
    session_start();
    require "internacionalizacion.php";
    require "conexion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    $usuario = $_SESSION["usuario"];

    // Obtiene las reservas activas
    $sql = "SELECT * FROM reservas WHERE usuario = ? AND fecha_devolucion IS NULL ORDER BY fecha_reserva DESC";
    $consulta = $conexion->prepare($sql);
    $consulta->bind_param("s", $usuario);
    $consulta->execute();
    $reservas_activas = $consulta->get_result();

    // Obtiene el historial de reservas
    $sql = "SELECT * FROM reservas WHERE usuario = ? AND fecha_devolucion IS NOT NULL ORDER BY fecha_devolucion DESC";
    $consulta = $conexion->prepare($sql);
    $consulta->bind_param("s", $usuario);
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
    
    <h1>Mis Reservas</h1>
    
    <a href="catalogo.php">Ver Películas</a>
    <a href="catalogo_libros.php">Ver Libros</a>
    
    <p>Usuario: <?php echo $_SESSION['usuario']; ?> | <a href="logout.php">Cerrar Sesión</a></p>

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

    <h2>Reservas Activas</h2>
    <table>
        <tr>
            <th>Título</th>
            <th>Tipo</th>
            <th>Fecha Reserva</th>
            <th>Acción</th>
        </tr>
        
        <?php if($reservas_activas->num_rows > 0): ?>
            <?php while($reserva = $reservas_activas->fetch_assoc()): ?>
                <tr>
                    <td><?= $reserva['titulo'] ?></td>
                    <td><?= ucfirst($reserva['tipo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) ?></td>
                    <td>
                        <form method="post" action="devolver.php" style="display: inline;">
                            <input type="hidden" name="id_reserva" value="<?= $reserva['id'] ?>">
                            <button type="submit" style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer;">
                                Devolver
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No tienes reservas activas</td>
            </tr>
        <?php endif; ?>
    </table>

    <h2 style="margin-top: 30px;">Historial de Reservas</h2>
    <table>
        <tr>
            <th>Título</th>
            <th>Tipo</th>
            <th>Fecha Reserva</th>
            <th>Fecha Devolución</th>
        </tr>
        
        <?php if($reservas_historico->num_rows > 0): ?>
            <?php while($reserva = $reservas_historico->fetch_assoc()): ?>
                <tr>
                    <td><?= $reserva['titulo'] ?></td>
                    <td><?= ucfirst($reserva['tipo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_reserva'])) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($reserva['fecha_devolucion'])) ?></td>
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