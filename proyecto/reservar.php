<?php
    session_start();
    require "conexion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $titulo = $_POST["titulo"] ?? "";
        $tipo = $_POST["tipo"] ?? "pelicula";
        $usuario = $_SESSION["usuario"];
        
        // Verifica si el producto ya esta reservado
        $sql = "SELECT * FROM reservas WHERE titulo = ? AND tipo = ? AND fecha_devolucion IS NULL";
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param("ss", $titulo, $tipo);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if($resultado->num_rows > 0) {
            $_SESSION["mensaje_error"] = "Este producto ya está reservado por otro usuario";
        } else {
            // Verifica si el usuario ya tiene esto reservado
            $sql = "SELECT * FROM reservas WHERE usuario = ? AND titulo = ? AND tipo = ? AND fecha_devolucion IS NULL";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("sss", $usuario, $titulo, $tipo);
            $consulta->execute();
            $resultado = $consulta->get_result();

            if($resultado->num_rows > 0) {
                $_SESSION["mensaje_error"] = "Ya tienes este producto reservado";
            } else {
                // Realizar la reserva
                $fecha = date("Y-m-d H:i:s");
                $sql = "INSERT INTO reservas (usuario, titulo, tipo, fecha_reserva) VALUES (?, ?, ?, ?)";
                $consulta = $conexion->prepare($sql);
                $consulta->bind_param("ssss", $usuario, $titulo, $tipo, $fecha);
                
                if($consulta->execute()) {
                    $_SESSION["mensaje_exito"] = "Reserva realizada correctamente";
                } else {
                    $_SESSION["mensaje_error"] = "Error al realizar la reserva";
                }
            }
        }
        $consulta->close();
    }

    // Redirige según el tipo de producto
    if($tipo == "libro") {
        header("Location: catalogo_libros.php");
    } else {
        header("Location: catalogo.php");
    }
    exit;
?>