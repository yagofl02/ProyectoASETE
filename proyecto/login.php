<?php
        session_start();

        //Añado la dependenci al fichero donde gestiono la internacionalización
        //Hacerlo así es IMPORTANTE par que no estéis repitiendo código todo el rato (ayuda a modulizar el proyecto)
        require "internacionalizacion.php";

    //conectamos con la base de datos
    require "conexion.php";
        

        //Creamos una variable para almacenar el error si lo hubiera
        $error = "";

        //Recuperamos la información enviada por método POST
        $usuario = $_POST["usuario"] ?? "";
        $contrasena = $_POST["contrasena"] ?? "";

        if($_SERVER["REQUEST_METHOD"] == "POST") { 
            
            //ciframos la contraseña con sha256
            $hash = hash("sha256", $contrasena);
            
            //hacemos la consulta con la base de datos seleccionada
            $sql = "SELECT * FROM Usuarios WHERE usuario= ? AND password = ?";
            $consulta = $conexion->prepare($sql);

            //sustituimos los ? ? con los valores correspondientes
            $consulta->bind_param("ss", $usuario, $hash);

            //ejecutamos la consulta
            $consulta->execute();

            //guardamos el resultado de la consulta
            $resultado = $consulta->get_result();

            if ($resultado->num_rows === 1) {
                // Login correcto
                $_SESSION["usuario"] = $usuario;
                header("Location: catalogo.php");
                exit;
            } else {
                // Error de login
                $error = "Usuario o contraseña incorrectos";
            }

            //cerramos la consulta
            $consulta->close();
            
        }
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Proyecto ASETE 1</title>
        <link rel="stylesheet" href="style/login.css">
        <link rel="stylesheet" href="style/idioma.css">
    </head>
    <body>
        <?php include "caja-idiomas.html"; ?>
        <div class="caja-login">
            <h1><?=$traducciones["login"]?></h1>

            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post">
                <label for="usuario"><?=$traducciones["user"]?></label>
                <input type="text" name="usuario" id="usuario" required>

                <label for="clave"><?=$traducciones["pass"]?></label>
                <input type="password" name="contrasena" id="clave" required>

                <button type="submit"><?=$traducciones["enter"]?></button>
            </form>
        </div>
    </body>
</html>