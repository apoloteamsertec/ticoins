<?php
session_start();
require_once "../config/supabase.php";

// --------------------------------------------------
//  VALIDACIÓN BÁSICA
// --------------------------------------------------
if (!isset($_GET["id"])) {
    die("Tarea no especificada.");
}

$tarea_id = $_GET["id"];

// 🔹 Datos del usuario (TEMPORAL, reemplazar por login real)
$usuario_id = $_SESSION["child_id"];

// --------------------------------------------------
//  Obtener info de la tarea
// --------------------------------------------------
$tarea = $supabase->from("tareas", "GET", null, "id=eq.$tarea_id");
if (!$tarea) {
    die("Tarea no encontrada.");
}
$tarea = $tarea[0];

// --------------------------------------------------
//  SI ENVIÓ LA FOTO
// --------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Validar archivo
    if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        $error = "Debes subir una foto para continuar.";
    } else {

        // --------------------------------------------------
        // Crear carpeta si no existe
        // --------------------------------------------------
        $carpeta = __DIR__ . "/../uploads/evidencias/";

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        // --------------------------------------------------
        // Generar nombre SEGURO del archivo
        // --------------------------------------------------
        $nombre_archivo = "evidencia_" . time() . "_" .
            preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES["foto"]["name"]);

        // Ruta ABSOLUTA para guardar físicamente
        $ruta_absoluta = $carpeta . $nombre_archivo;

        // Ruta pública que guardaremos en Supabase
        $ruta_publica = "/uploads/evidencias/" . $nombre_archivo;

        // --------------------------------------------------
        // Mover archivo
        // --------------------------------------------------
        if (!move_uploaded_file($_FILES["foto"]["tmp_name"], $ruta_absoluta)) {
            die("Error al guardar la imagen en el servidor.");
        }

        // --------------------------------------------------
        // GUARDAR REGISTRO EN SUPABASE
        // --------------------------------------------------
        $supabase->from("tareas_realizadas", "POST", [
            "usuario_id"      => $usuario_id,
            "tarea_id"        => $tarea_id,
            "estado"          => "revision",
            "fecha_envio"     => date("c"),
            "foto_evidencia"  => $ruta_publica,
        ]);

        header("Location: tarea_enviada.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Enviar tarea</title>
<link rel="stylesheet" href="css/children.css">
</head>

<body>

<header class="topbar-child">
    <div class="hola">
        REALIZAR TAREA
    </div>
    <div class="hamburger">
        <span></span><span></span><span></span>
    </div>
</header>

<main class="child-page">

    <h2 style="text-align:center;"><?= htmlspecialchars($tarea["titulo"]) ?></h2>

    <?php if (!empty($tarea["descripcion"])): ?>
        <p style="text-align:center;color:#555;margin-bottom:18px;">
            <?= htmlspecialchars($tarea["descripcion"]) ?>
        </p>
    <?php endif; ?>

    <?php if(isset($error)): ?>
        <p style="background:#ffdddd;border-left:4px solid red;padding:10px;border-radius:8px;">
            <?= $error ?>
        </p>
    <?php endif; ?>

    <!-- FORMULARIO -->
    <form method="POST" enctype="multipart/form-data" class="form-box" style="margin-top:20px;padding:20px;">

        <label>Subí una foto como evidencia:</label>
        <input type="file" name="foto" accept="image/*" required>

        <button type="submit" style="width:100%;margin-top:12px;">
            Enviar evidencia
        </button>
    </form>

</main>

</body>
</html>
