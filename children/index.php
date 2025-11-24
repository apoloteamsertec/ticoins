<?php
session_start();
if (!isset($_SESSION["child_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/supabase.php";

$usuario_id = $_SESSION["child_id"];

// --------------------------------------------------
// PERFIL DEL NIÑO
// --------------------------------------------------
$perfilRes = $supabase->from("profiles", "GET", null, "id=eq.$usuario_id");
if (!$perfilRes) {
    die("Perfil no encontrado.");
}
$perfil         = $perfilRes[0];
$coins          = (int)$perfil["coins"];
$username       = $perfil["username"];
$nombreCompleto = $perfil["nombre_completo"];
$fotoPerfil     = !empty($perfil["foto_perfil"]) ? $perfil["foto_perfil"] : null;

// --------------------------------------------------
// CAMBIAR FOTO DE PERFIL
// --------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "cambiar_foto") {

    if (!isset($_FILES["nueva_foto"]) || $_FILES["nueva_foto"]["error"] !== UPLOAD_ERR_OK) {
        $error_foto = "Debes seleccionar una imagen válida.";
    } else {

        $carpeta = __DIR__ . "/uploads/perfiles/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $nombre_archivo = "avatar_" . $usuario_id . "_" . time() . ".jpg";
        $ruta_absoluta  = $carpeta . $nombre_archivo;
        $ruta_publica   = "uploads/perfiles/" . $nombre_archivo;

        if (!move_uploaded_file($_FILES["nueva_foto"]["tmp_name"], $ruta_absoluta)) {
            $error_foto = "No se pudo guardar la foto del perfil.";
        } else {
            $supabase->from("profiles", "PATCH", [
                "foto_perfil" => $ruta_publica
            ], "id=eq.$usuario_id");

            $fotoPerfil = $ruta_publica;
        }
    }
}

// --------------------------------------------------
// TAREAS ACTIVAS
// --------------------------------------------------
$todasTareas = $supabase->from("tareas", "GET", null, "activa=eq.true");
$tareas_filtradas = [];

if ($todasTareas) {
    foreach ($todasTareas as $t) {
        if ($t["tipo_asignacion"] === "general") {
            $tareas_filtradas[] = $t;
        } else if ($t["tipo_asignacion"] === "individual" && $t["usuario_asignado"] === $usuario_id) {
            $tareas_filtradas[] = $t;
        }
    }
}

$tareas_mostrar = array_slice($tareas_filtradas, 0, 3);

// --------------------------------------------------
// LÍMITE DE 3 TAREAS POR DÍA
// --------------------------------------------------
$hoyInicio = date("Y-m-d") . "T00:00:00";
$hoyFin    = date("Y-m-d") . "T23:59:59";

$realizadasHoy = $supabase->from(
    "tareas_realizadas",
    "GET",
    null,
    "usuario_id=eq.$usuario_id&fecha_envio=gte.$hoyInicio&fecha_envio=lte.$hoyFin"
);

$cantRealizadasHoy = $realizadasHoy ? count($realizadasHoy) : 0;
$puedeHacerHoy     = max(0, 3 - $cantRealizadasHoy);

// --------------------------------------------------
// PREMIOS DISPONIBLES
// --------------------------------------------------
$premios = $supabase->from("premios", "GET", null, "");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Ti-Coins</title>
<link rel="stylesheet" href="css/children.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>

<!-- ==============================
     BARRA SUPERIOR
==================================-->
<header class="topbar-child">

    <div class="topbar-left">
        <div class="avatar-wrapper" id="avatarBtn">
            <?php if($fotoPerfil): ?>
                <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Perfil">
            <?php endif; ?>
        </div>

        <div class="hola">
            HOLA <span>@<?= strtoupper(htmlspecialchars($username)) ?></span>
        </div>
    </div>

    <div class="hamburger"><span></span><span></span><span></span></div>
</header>



<!-- ==============================
     CONTENIDO CENTRAL
==================================-->
<main class="child-page">

    <!-- PILLS COINS -->
    <div class="coins-pill">
        Tenés <span><?= number_format($coins, 0, ",", ".") ?></span> Ti-Coins
    </div>


    <!-- ==========================
         TAREAS
    =========================== -->
    <section class="card-tareas">
        <div class="card-tareas-header">Tareas Disponibles</div>

        <div class="card-tareas-body">

            <?php if ($puedeHacerHoy <= 0): ?>

                <p class="sin-tareas">¡Llegaste al máximo de tareas por hoy! 🌟</p>

            <?php elseif (empty($tareas_mostrar)): ?>

                <p class="sin-tareas">Por ahora no tenés tareas asignadas 😴</p>

            <?php else: ?>

                <?php foreach ($tareas_mostrar as $tarea): ?>
                <div class="tarea-item">

                    <div class="tarea-titulo"><?= htmlspecialchars($tarea["titulo"]) ?></div>

                    <div class="tarea-coins"><?= $tarea["coins_valor"] ?> TI-Coins</div>

                    <a href="tarea_realizar.php?id=<?= $tarea["id"] ?>"
                       class="tarea-btn">
                       Realizar
                    </a>
                </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </section>



    <!-- ==========================
         PREMIOS
    =========================== -->
    <section class="card-premios">
        <div class="card-premios-header">Premios Disponibles</div>

        <div class="card-premios-body">

            <?php if (!$premios): ?>
                <p class="sin-tareas">Todavía no hay premios cargados.</p>
            <?php else: ?>

                <?php foreach ($premios as $p): ?>
                <?php 
                    $costo  = (int)$p["costo_coins"];
                    $puede  = $coins >= $costo;
                ?>

                <div class="premio-item">

                    <div class="premio-nombre"><?= htmlspecialchars($p["nombre"]) ?></div>

                    <div class="premio-coins"><?= number_format($costo,0,",",".") ?> TI-Coins</div>

                    <a class="premio-btn <?= $puede ? 'ok' : 'no' ?>"
                       href="<?= $puede ? 'premio_cobrar.php?id=' . $p["id"] : '#' ?>"
                       <?= $puede ? '' : 'style="pointer-events:none;"' ?>>
                        Cobrar
                    </a>
                </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </section>

    <p class="mensaje-amor">No te olvides nunca que tus tíos te aman mucho 💚</p>

</main>



<!-- ==============================
     MODAL FOTO DE PERFIL
==================================-->
<div class="modal-overlay" id="modalFoto">
    <div class="modal">

        <h3>Tu foto de perfil</h3>

        <?php if($fotoPerfil): ?>
            <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto perfil">
        <?php else: ?>
            <div style="width:120px;height:120px;border-radius:50%;background:#ff3b30;margin:0 auto;"></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="cambiar_foto">
            <input type="file" name="nueva_foto" required>
            <button class="premio-btn ok" type="submit">Cambiar foto</button>
        </form>

        <button class="modal-close" id="cerrarModal">Cerrar</button>

    </div>
</div>



<script>
document.addEventListener("DOMContentLoaded", () => {

    const avatar = document.getElementById("avatarBtn");
    const modal  = document.getElementById("modalFoto");
    const close  = document.getElementById("cerrarModal");

    avatar.onclick = () => modal.style.display = "flex";
    close.onclick  = () => modal.style.display = "none";

    modal.onclick = (e) => {
        if (e.target === modal) modal.style.display = "none";
    };

});
</script>

</body>
</html>
