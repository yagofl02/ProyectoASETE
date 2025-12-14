<?php
    session_start();
    require "conexion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_reserva = $_POST["id_reserva"] ?? "";
        $usuario = $_SESSION["usuario"];

        // Verifica que la reserva sea del usuario
        $sql = "SELECT * FROM reservas WHERE id = ? AND usuario = ? AND fecha_devolucion IS NULL";
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param("is", $id_reserva, $usuario);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if($resultado->num_rows > 0) {
            // Actualizar la fecha de devolución
            $fecha = date("Y-m-d H:i:s");
            $sql = "UPDATE reservas SET fecha_devolucion = ? WHERE id = ?";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("si", $fecha, $id_reserva);
            
            if($consulta->execute()) {
                $_SESSION["mensaje_exito"] = "Devolución realizada correctamente";
            } else {
                $_SESSION["mensaje_error"] = "Error al realizar la devolución";
            }
        } else {
            $_SESSION["mensaje_error"] = "Reserva no encontrada o ya devuelta";
        }
        $consulta->close();
    }

    header("Location: reservas.php");
    exit;
?>