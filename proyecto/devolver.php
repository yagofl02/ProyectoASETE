<?php
    session_start();
    require "conexion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $idCliente = $_POST["idCliente"] ?? "";
        $idLibro = $_POST["idLibro"] ?? NULL;
        $idPelicula = $_POST["idPelicula"] ?? NULL;
        $tipo = $_POST["tipo"] ?? "";
        $usuario = $_SESSION["usuario"];

        // verifica si coincide
        $sql_check = "SELECT id FROM Usuarios WHERE usuario = ?";
        $consulta = $conexion->prepare($sql_check);
        $consulta->bind_param("s", $usuario);
        $consulta->execute();
        $resultado_check = $consulta->get_result();
        $user_data = $resultado_check->fetch_assoc();
        
        if($user_data['id'] != $idCliente) {
            $_SESSION["mensaje_error"] = "No tienes permiso para devolver esta reserva";
            header("Location: reservas.php");
            exit;
        }

        // Vverifica que exista y este activa
        if($tipo == "libro") {
            $sql = "SELECT * FROM Reservas WHERE idCliente = ? AND idLibro = ? AND Fecha_devolucion IS NULL";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("ii", $idCliente, $idLibro);
        } else {
            $sql = "SELECT * FROM Reservas WHERE idCliente = ? AND idPelicula = ? AND Fecha_devolucion IS NULL";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("ii", $idCliente, $idPelicula);
        }
        
        $consulta->execute();
        $resultado = $consulta->get_result();

        if($resultado->num_rows > 0) {
            //Fecha de devolucion
            $fecha = date("Y-m-d H:i:s");
            
            if($tipo == "libro") {
                $sql = "UPDATE Reservas SET Fecha_devolucion = ? WHERE idCliente = ? AND idLibro = ? AND Fecha_devolucion IS NULL";
                $consulta = $conexion->prepare($sql);
                $consulta->bind_param("sii", $fecha, $idCliente, $idLibro);
            } else {
                $sql = "UPDATE Reservas SET Fecha_devolucion = ? WHERE idCliente = ? AND idPelicula = ? AND Fecha_devolucion IS NULL";
                $consulta = $conexion->prepare($sql);
                $consulta->bind_param("sii", $fecha, $idCliente, $idPelicula);
            }
            
            if($consulta->execute()) {
                $_SESSION["mensaje_exito"] = "Devolución realizada correctamente";
            } else {
                $_SESSION["mensaje_error"] = "Error al realizar la devolución: " . $consulta->error;
            }
        } else {
            $_SESSION["mensaje_error"] = "Reserva no encontrada o ya devuelta";
        }
        $consulta->close();
    }

    header("Location: reservas.php");
    exit;
?>