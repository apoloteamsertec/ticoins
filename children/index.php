<?php
session_start();
if (!isset($_SESSION["child_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/supabase.php";

$usuario_id = $_SESSION["child_id"];

/* ============================
   PERFIL DEL NIÑO
============================ */
$perfilRes = $supabase->from("profiles", "GET", null, "id=eq.$usuario_id");
if (!$perfilRes) die("Perfil no encontrado.");

$perfil          = $perfilRes[0];
$coins           = (int)$perfil["coins"];
$username        = $perfil["username"];
$nombreCompleto  = $perfil["nombre_completo"];
$fotoPerfil      = $perfil["foto_perfil"] ? "/" . ltrim($perfil["foto_perfil"], "/") : null;

/* ============================
   CAMBIAR FOTO DE PERFIL
============================ */
if ($_SERVER["REQUEST_METHOD"] === "POST" 
    && isset($_POST["accion"]) 
    && $_POST["accion"] === "cambiar_foto") {

    if (isset($_FILES["nueva_foto"]) && $_FILES["nueva_foto"]["error"] === UPLOAD_ERR_OK) {

        $carpeta = __DIR__ . "/uploads/perfiles/";
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nombre_archivo = "avatar_" . $usuario_id . "_" . time() . "_" .
            preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES["nueva_foto"]["name"]);

        $ruta_absoluta = $carpeta . $nombre_archivo;
        $ruta_publica  = "uploads/perfiles/" . $nombre_archivo;

        move_uploaded_file($_FILES["nueva_foto"]["tmp_name"], $ruta_absoluta);

        $supabase->from("profiles", "PATCH", [
            "foto_perfil" => $ruta_publica
        ], "id=eq.$usuario_id");

        $fotoPerfil = "/" . $ruta_publica;
    }
}

/* ============================
   TAREAS ACTIVAS
============================ */
$todasTareas = $supabase->from("tareas", "GET", null, "activa=eq.true");

/* ============================
   TAREAS REALIZADAS
============================ */
$realizadas = $supabase->from(
    "tareas_realizadas",
    "GET",
    null,
    "usuario_id=eq.$usuario_id"
);

$tareasEstado = [];
if ($realizadas) {
    foreach ($realizadas as $r) {
        $tareasEstado[$r["tarea_id"]] = $r["estado"]; // aprobada, revision, rechazada
    }
}

/* ============================
   FILTRAR TAREAS DISPONIBLES
============================ */
$tareas_filtradas = [];

foreach ($todasTareas as $t) {
    $id = $t["id"];

    // Si está aprobada → NO mostrar más
    if (isset($tareasEstado[$id]) && $tareasEstado[$id] === "aprobada") {
        continue;
    }

    // Si está en revisión → NO mostrar hasta que el admin resuelva
    if (isset($tareasEstado[$id]) && $tareasEstado[$id] === "revision") {
        continue;
    }

    // Si está rechazada → volver a mostrarla
    if ($t["tipo_asignacion"] === "general" ||
        ($t["tipo_asignacion"] === "individual" && $t["usuario_asignado"] === $usuario_id)) {
        $tareas_filtradas[] = $t;
    }
}

// máx 3 tareas por día
$tareas_mostrar = array_slice($tareas_filtradas, 0, 3);

/* ============================
   LÍMITE DIARIO
============================ */
$hoy = date("Y-m-d");
$hoyInicio = $hoy . "T00:00:00";
$hoyFin    = $hoy . "T23:59:59";

$realizadasHoy = $supabase->from(
    "tareas_realizadas",
    "GET",
    null,
    "usuario_id=eq.$usuario_id&fecha_envio=gte.$hoyInicio&fecha_envio=lte.$hoyFin"
);

$cantRealizadasHoy = $realizadasHoy ? count($realizadasHoy) : 0;
$limiteDiario = 3;
$puedeHacerHoy = max(0, $limiteDiario - $cantRealizadasHoy);

/* ============================
   PREMIOS ACTIVOS
============================ */
$premios = $supabase->from("premios", "GET", null, "activo=eq.true");

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Ti-Coins</title>
<link rel="stylesheet" href="css/children.css">
</head>

<body>

<header class="topbar-child">
    <div class="topbar-left">
        <div class="avatar-wrapper" id="avatarBtn">
            <?php if($fotoPerfil): ?>
                <img src="<?= htmlspecialchars($fotoPerfil) ?>">
            <?php endif; ?>
        </div>
        <div class="hola">HOLA <span>@<?= strtoupper($username) ?></span></div>
    </div>

    <div class="hamburger">
        <span></span><span></span><span></span>
    </div>
</header>

<main class="child-page">

    <div class="coins-pill">
        Tenés 
        <span id="coins-counter" data-value="<?= $coins ?>">
            <?= number_format($coins, 0, ",", ".") ?>
        </span> 
        Ti-Coins
    </div>

    <!-- =======================
         TAREAS
    ======================== -->
    <section class="card-tareas">
        <div class="card-tareas-header">Tareas Disponibles</div>
        <div class="card-tareas-body">

            <?php if($puedeHacerHoy <= 0): ?>
                <p class="sin-tareas">¡Llegaste al máximo de tareas por hoy! 🌟</p>

            <?php elseif(empty($tareas_mostrar)): ?>
                <p class="sin-tareas">No tenés tareas asignadas 😴</p>

            <?php else: ?>
                <?php foreach ($tareas_mostrar as $t): ?>
                    <div class="tarea-item">
                        <div class="tarea-titulo"><?= htmlspecialchars($t["titulo"]) ?></div>
                        <div class="tarea-coins"><?= (int)$t["coins_valor"] ?> TI-Coins</div>

                        <a class="tarea-btn"
                           href="<?= $puedeHacerHoy > 0 ? "tarea_realizar.php?id=".$t["id"] : "#" ?>"
                           <?= $puedeHacerHoy <= 0 ? 'style="opacity:.4;pointer-events:none;"' : '' ?>
                        >Realizar</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </section>

    <!-- =======================
         PREMIOS
    ======================== -->
    <section class="card-premios">
        <div class="card-premios-header">Premios Disponibles</div>
        <div class="card-premios-body">

            <?php foreach($premios as $p): 
                $costo = (int)$p["costo_coins"];
                $ok = $coins >= $costo;
            ?>
                <div class="premio-item">
                    <div class="premio-nombre"><?= htmlspecialchars($p["nombre"]) ?></div>
                    <div class="premio-coins"><?= number_format($costo, 0, ",", ".") ?> TI-Coins</div>

                    <a class="premio-btn <?= $ok ? 'ok' : 'no' ?>"
                       href="<?= $ok ? "premio_cobrar.php?id=".$p["id"] : "#" ?>"
                       <?= !$ok ? 'style="pointer-events:none;opacity:.7;"' : '' ?>
                    >Cobrar</a>
                </div>

                <div class="premio-separator"></div>
            <?php endforeach; ?>

        </div>
    </section>

    <p class="mensaje-amor">No te olvides nunca que tus tíos te aman mucho 💚</p>

</main>

<!-- =======================
     MODAL FOTO PERFIL
======================= -->
<div class="modal-overlay" id="modalFoto">
    <div class="modal">
        <h3>Tu foto de perfil</h3>

        <?php if($fotoPerfil): ?>
            <img src="<?= htmlspecialchars($fotoPerfil) ?>" class="modal-avatar">
        <?php else: ?>
            <div class="modal-avatar-placeholder"></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="cambiar_foto">
            <input type="file" name="nueva_foto" required>
            <button class="modal-save">Cambiar foto</button>
        </form>

        <button class="modal-close" id="cerrarModal">Cerrar</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("modalFoto");
    document.getElementById("avatarBtn").onclick = () => modal.style.display = "flex";
    document.getElementById("cerrarModal").onclick = () => modal.style.display = "none";
    modal.onclick = (e) => { if (e.target === modal) modal.style.display = "none"; };
});
</script>

</body>
</html>
