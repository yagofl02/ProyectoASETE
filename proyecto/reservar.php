<?php
    session_start();
    require "conexion.php";

    if(!isset($_SESSION["usuario"])){
        header("Location: login.php");
        exit;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $id_producto = $_POST["id_producto"] ?? "";
        $tipo = $_POST["tipo"] ?? "pelicula";
        $usuario = $_SESSION["usuario"];
        
        // Obtener id
        $sql_cliente = "SELECT cliente_id FROM Usuarios WHERE usuario = ?";
        $consulta = $conexion->prepare($sql_cliente);
        $consulta->bind_param("s", $usuario);
        $consulta->execute();
        $resultado_cliente = $consulta->get_result();
        
        if($resultado_cliente->num_rows == 0) {
            $_SESSION["mensaje_error"] = "Usuario no encontrado";
            if($tipo == "libro") {
                header("Location: catalogo_libros.php");
            } else {
                header("Location: catalogo.php");
            }
            exit;
        }
        
        $cliente = $resultado_cliente->fetch_assoc();
        $idCliente = $cliente['cliente_id'];
        
        // verifica su existencia
        if($tipo == "libro") {
           
            $sql_verificar = "SELECT ID FROM Libros WHERE ID = ?";
            $consulta = $conexion->prepare($sql_verificar);
            $consulta->bind_param("i", $id_producto);
            $consulta->execute();
            $resultado_verificar = $consulta->get_result();
            
            if($resultado_verificar->num_rows == 0) {
                $_SESSION["mensaje_error"] = "El libro no existe";
                header("Location: catalogo_libros.php");
                exit;
            }
            
            $idLibro = $id_producto;
            $idPelicula = NULL;
        } else {
            // lo mismo que el libro
            $sql_verificar = "SELECT ID FROM Peliculas WHERE ID = ?";
            $consulta = $conexion->prepare($sql_verificar);
            $consulta->bind_param("i", $id_producto);
            $consulta->execute();
            $resultado_verificar = $consulta->get_result();
            
            if($resultado_verificar->num_rows == 0) {
                $_SESSION["mensaje_error"] = "La película no existe";
                header("Location: catalogo.php");
                exit;
            }
            
            $idLibro = NULL;
            $idPelicula = $id_producto;
        }
        
        // Verifica que este sindevolver
        if($tipo == "libro") {
            $sql = "SELECT * FROM Reservas WHERE idLibro = ? AND Fecha_devolucion IS NULL";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("i", $idLibro);
        } else {
            $sql = "SELECT * FROM Reservas WHERE idPelicula = ? AND Fecha_devolucion IS NULL";
            $consulta = $conexion->prepare($sql);
            $consulta->bind_param("i", $idPelicula);
        }
        
        $consulta->execute();
        $resultado = $consulta->get_result();

        if($resultado->num_rows > 0) {
            $_SESSION["mensaje_error"] = "Este producto ya está reservado por otro usuario";
        } else {
            // verifica que no este reservado 2 veces por el mismo
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
                $_SESSION["mensaje_error"] = "Ya tienes este producto reservado";
            } else {
                
                $fecha = date("Y-m-d H:i:s");
                
                $sql = "INSERT INTO Reservas (idCliente, idLibro, idPelicula, Fecha_reserva) VALUES (?, ?, ?, ?)";
                $consulta = $conexion->prepare($sql);
                
                
                $consulta->bind_param("iiis", $idCliente, $idLibro, $idPelicula, $fecha);
                
                if($consulta->execute()) {
                    $_SESSION["mensaje_exito"] = "Reserva realizada correctamente";
                } else {
                    $_SESSION["mensaje_error"] = "Error al realizar la reserva: " . $consulta->error;
                }
            }
        }
        if($consulta) {
            $consulta->close();
        }
    }


    if($tipo == "libro") {
        header("Location: catalogo_libros.php");
    } else {
        header("Location: catalogo_peliculas.php");
    }
    exit;
?>