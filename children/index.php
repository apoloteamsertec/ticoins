<?php
session_start();
if (!isset($_SESSION["child_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/supabase.php";

$usuario_id = $_SESSION["child_id"];

// ========================
// PERFIL DEL NIÑO
// ========================
$perfilRes = $supabase->from("profiles", "GET", null, "id=eq.$usuario_id");
if (!$perfilRes) { die("Perfil no encontrado."); }

$perfil         = $perfilRes[0];
$coins          = (int)$perfil["coins"];
$username       = $perfil["username"];
$nombreCompleto = $perfil["nombre_completo"];
$fotoPerfil     = !empty($perfil["foto_perfil"]) ? $perfil["foto_perfil"] : null;

// ========================
// CAMBIO DE FOTO DE PERFIL
// ========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "cambiar_foto") {

    if (!isset($_FILES["nueva_foto"]) || $_FILES["nueva_foto"]["error"] !== UPLOAD_ERR_OK) {
        $error_foto = "Debes seleccionar una imagen válida.";
    } else {
        $carpeta = __DIR__ . "/../uploads/perfiles/";
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nombre_archivo = "avatar_" . $usuario_id . "_" . time() . "_" .
            preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES["nueva_foto"]["name"]);

        $ruta_absoluta = $carpeta . $nombre_archivo;
        $ruta_publica  = "/uploads/perfiles/" . $nombre_archivo;

        if (move_uploaded_file($_FILES["nueva_foto"]["tmp_name"], $ruta_absoluta)) {
            $supabase->from("profiles", "PATCH", [
                "foto_perfil" => $ruta_publica
            ], "id=eq.$usuario_id");

            $fotoPerfil = $ruta_publica;
        } else {
            $error_foto = "No se pudo guardar la foto.";
        }
    }
}

// ========================
// TAREAS ACTIVAS
// ========================
$todasTareas = $supabase->from("tareas", "GET", null, "activa=eq.true");
$tareas_filtradas = [];

if ($todasTareas) {
    foreach ($todasTareas as $t) {
        if ($t["tipo_asignacion"] === "general") $tareas_filtradas[] = $t;
        else if ($t["tipo_asignacion"] === "individual" && $t["usuario_asignado"] === $usuario_id)
            $tareas_filtradas[] = $t;
    }
}

$tareas_mostrar = array_slice($tareas_filtradas, 0, 3);

// ========================
// LÍMITE DIARIO 3 TAREAS
// ========================
$hoyInicio = date("Y-m-d") . "T00:00:00";
$hoyFin    = date("Y-m-d") . "T23:59:59";

$realizadasHoy = $supabase->from(
    "tareas_realizadas",
    "GET",
    null,
    "usuario_id=eq.$usuario_id&fecha_envio=gte.$hoyInicio&fecha_envio=lte.$hoyFin"
);

$puedeHacerHoy = 3 - (count($realizadasHoy) ?? 0);

// ========================
// PREMIOS
// ========================
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

<style>
body {
    margin: 0;
    background: #F1F3F6;
    font-family: Inter, sans-serif;
}

/* ---- TOPBAR ---- */
.topbar-child{
    width:100%;
    background:#3EB04B;
    padding:10px 14px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:0 3px 8px rgba(0,0,0,0.25);
    position:sticky;
    top:0;
    z-index:100;
}

.topbar-left { display:flex; align-items:center; }

.avatar-wrapper{
    width:40px;height:40px;border-radius:50%;
    background:#ff3b30;overflow:hidden;
    margin-right:10px;cursor:pointer;
    border:2px solid white;
}
.avatar-wrapper img{ width:100%;height:100%;object-fit:cover; }

/* ---- CONTENIDO ---- */
.child-page{
    max-width:420px;
    margin:0 auto;
    padding:18px;
}

/* Coins pill */
.coins-pill{
    width:100%;background:#3EB04B;color:white;
    padding:14px;border-radius:24px;text-align:center;
    font-size:18px;font-weight:600;
}

/* ---- CARD TAREAS ---- */
.card-tareas, .card-premios{
    width:100%;background:white;
    margin-top:22px;border-radius:26px;
    box-shadow:0 8px 22px rgba(0,0,0,.15);
    overflow:hidden;
}
.card-tareas-header{
    background:#3EB04B;color:white;text-align:center;
    padding:14px;font-size:18px;font-weight:600;
}
.card-tareas-body{ padding:14px;text-align:center; }
.tarea-item{ padding:10px 4px; }
.tarea-titulo{ font-size:15px;font-weight:600; }
.tarea-coins{ font-size:13px;color:#1c8b3a;margin-bottom:10px; }
.tarea-btn{
    display:inline-block;background:#3EB04B;
    color:white;padding:8px 24px;border-radius:999px;
    font-weight:600;text-decoration:none;
}

/* ---- PREMIOS ---- */
.card-premios-header{
    background:#0070c9;color:white;
    padding:14px;text-align:center;
    font-size:18px;font-weight:600;
}
.premio-btn.ok{ background:#0070c9;color:white; }
.premio-btn.no{ background:#ff9800;color:white; pointer-events:none;opacity:.7; }

/* ---- MODAL ---- */
.modal-overlay{
    position:fixed;inset:0;background:rgba(0,0,0,.4);
    display:none;align-items:center;justify-content:center;
    z-index:9999;
}
.modal{
    background:white;border-radius:16px;padding:18px;
    width:90%;max-width:320px;text-align:center;
}
.modal img{
    width:120px;height:120px;border-radius:50%;
    object-fit:cover;margin-bottom:10px;
}
.modal-close{
    background:#ccc;border:none;padding:10px;border-radius:8px;
    margin-top:10px;cursor:pointer;
}
</style>
</head>

<body>

<!-- ======================
     TOP BAR
======================= -->
<header class="topbar-child">
    <div class="topbar-left">
        <div class="avatar-wrapper" id="avatarBtn">
            <?php if($fotoPerfil): ?>
                <img src="<?= $fotoPerfil ?>">
            <?php endif; ?>
        </div>
        <div class="hola">HOLA <span>@<?= strtoupper($username) ?></span></div>
    </div>
    <div class="hamburger"><span></span><span></span><span></span></div>
</header>


<!-- ======================
     CONTENEDOR CENTRAL
======================= -->
<main class="child-page">

    <div class="coins-pill">
        Tenés <span><?= number_format($coins,0,",",".") ?></span> Ti-Coins
    </div>

    <!-- ===== TAREAS ===== -->
    <section class="card-tareas">
        <div class="card-tareas-header">Tareas Disponibles</div>

        <div class="card-tareas-body">
        <?php if($puedeHacerHoy <= 0): ?>
            <p>¡Llegaste al máximo de tareas por hoy! 🌟</p>
        <?php elseif(empty($tareas_mostrar)): ?>
            <p>No tenés tareas asignadas 😴</p>
        <?php else: ?>
            <?php foreach($tareas_mostrar as $t): ?>
                <div class="tarea-item">
                    <div class="tarea-titulo"><?= $t["titulo"] ?></div>
                    <div class="tarea-coins"><?= $t["coins_valor"] ?> TI-Coins</div>
                    <a class="tarea-btn"
                       href="tarea_realizar.php?id=<?= $t["id"] ?>">Realizar</a>
                </div>
               
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </section>

    <!-- TARJETA DE PREMIOS DISPONIBLES -->
<section class="card-premios">
    <div class="card-premios-header">
        Premios Disponibles
    </div>

    <div class="card-premios-body">

        <?php if (!$premios || count($premios) === 0): ?>
            <p class="sin-tareas">Todavía no hay premios cargados.</p>

        <?php else: ?>

            <?php foreach($premios as $idx => $premio): ?>
                <?php
                    $costo = (int)$premio["costo_coins"];
                    $puedeCobrar = $coins >= $costo;
                ?>

                <div class="premio-item">

                    <div class="premio-nombre">
                        <?= htmlspecialchars($premio["nombre"] ?? $premio["titulo"] ?? "Premio") ?>
                    </div>

                    <div class="premio-coins">
                        <?= number_format($costo, 0, ",", ".") ?> TI-Coins
                    </div>

                    <a href="<?= $puedeCobrar ? 'premio_cobrar.php?id=' . $premio["id"] : '#' ?>"
                       class="premio-btn <?= $puedeCobrar ? 'ok' : 'no' ?>"
                       <?= !$puedeCobrar ? 'style="pointer-events:none;opacity:.8;"' : '' ?>>
                       Cobrar
                    </a>

                </div>

                <?php if($idx < count($premios)-1): ?>
                    <div class="premio-separator"></div>
                <?php endif; ?>

            <?php endforeach; ?>

        <?php endif; ?>

    </div> <!-- cierre card-premios-body -->
</section>


    <p style="text-align:center;margin-top:20px;">
        No te olvides nunca que tus tíos te aman mucho 💚
    </p>
</main>


<!-- ======================
     MODAL FOTO PERFIL
======================= -->
<div class="modal-overlay" id="modalFoto">
    <div class="modal">
        <h3>Tu foto de perfil</h3>

        <?php if($fotoPerfil): ?>
            <img src="<?= $fotoPerfil ?>">
        <?php else: ?>
            <div style="width:120px;height:120px;border-radius:50%;background:#ff3b30;margin:auto;"></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="cambiar_foto">
            <input type="file" name="nueva_foto" accept="image/*" required>
            <button type="submit" class="premio-btn ok" style="margin-top:10px;">Cambiar foto</button>
        </form>

        <button class="modal-close" id="cerrarModal">Cerrar</button>
    </div>
</div>


<script>
// --- Modal foto ---
const avatarBtn = document.getElementById("avatarBtn");
const modalFoto = document.getElementById("modalFoto");
const cerrarModal = document.getElementById("cerrarModal");

avatarBtn.onclick = () => modalFoto.style.display = "flex";
cerrarModal.onclick = () => modalFoto.style.display = "none";

modalFoto.onclick = e => { if(e.target === modalFoto) modalFoto.style.display = "none"; }
</script>

</body>
</html>
