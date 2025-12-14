<?php
session_start();
require "internacionalizacion.php";

// Redirigir si no hay usuario logueado
if(!isset($_SESSION['usuario'])){
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
require_once "conexion.php";
require_once "models/pelicula.php";

$mensaje = "";
$error = false;

// Recuperar datos del formulario
$titulo   = $_POST["titulo"] ?? "";
$año      = $_POST["año"] ?? "";
$director = $_POST["director"] ?? "";
$actores  = $_POST["actores"] ?? "";
$genero   = $_POST["genero"] ?? "";

// Validar campos obligatorios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($titulo && $año && $director && $genero) {

        // Preparar consulta segura
        $stmt = $conexion->prepare("INSERT INTO Peliculas (Titulo, Año_estreno, Director, Actores, Genero, Tipo_adaptacion, Adaptacion_id) VALUES (?, ?, ?, ?, ?, 'Pelicula', null)");
        $stmt->bind_param("sisss", $titulo, $año, $director, $actores, $genero);

        if ($stmt->execute()) {
            $mensaje = "Película añadida correctamente.";
            $error = false;
        } else {
            $mensaje = "Error al añadir la película: " . $stmt->error;
            $error = true;
        }

        $stmt->close();

    } else {
        $mensaje = "Todos los campos obligatorios deben estar completos.";
        $error = true;
    }
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Película</title>
    <link rel="stylesheet" href="style/agregar_pelicula.css">
    <link rel="stylesheet" href="style/idioma.css">
</head>
<body>

<?php include "caja-idiomas.html"; ?>

<h1><?= $traducciones["add_new_film"] ?></h1>
<p><?= $traducciones["user"] ?>: <?= $_SESSION['usuario'] ?> | <a href="catalogo.php"><?= $traducciones["back_to_catalog"] ?></a></p>

<?php if($mensaje): ?>
    <p class="mensaje <?= $error ? 'error' : '' ?>"><?= $mensaje ?></p>
<?php endif; ?>

<form method="POST">
    <label for="titulo"><?= $traducciones["title"] ?>:</label>
    <input type="text" name="titulo" id="titulo" required>

    <label for="año"><?= $traducciones["year"] ?>:</label>
    <input type="number" name="año" id="año" required>

    <label for="director"><?= $traducciones["director"] ?>:</label>
    <input type="text" name="director" id="director" required>

    <label for="actores"><?= $traducciones["actors"] ?>:</label>
    <input type="text" name="actores" id="actores">

    <label for="genero"><?= $traducciones["genre"] ?>:</label>
    <select name="genero" id="genero" required>
        <option value="">Seleccionar</option>
        <option>Drama</option>
        <option>Romance</option>
        <option>Biografía</option>
        <option>Ciencia ficción</option>
        <option>Fantasía</option>
        <option>Animación</option>
        <option>Thriller</option>
    </select>

    <button type="submit"><?= $traducciones["save_film"] ?></button>
</form>

</body>
</html>
