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
if (!$perfilRes) { die("Perfil no encontrado."); }

$perfil         = $perfilRes[0];
$coins          = (int)$perfil["coins"];
$username       = $perfil["username"];
$nombreCompleto = $perfil["nombre_completo"];
$fotoPerfil     = $perfil["foto_perfil"] ?? null;

// --------------------------------------------------
// CAMBIAR FOTO DE PERFIL
// --------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "cambiar_foto") {

    if (!isset($_FILES["nueva_foto"]) || $_FILES["nueva_foto"]["error"] !== UPLOAD_ERR_OK) {
        $error_foto = "Debes seleccionar una imagen válida.";
    } else {

        // Crear carpeta
        $carpeta = __DIR__ . "/../uploads/perfiles/";
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        // Nombre archivo seguro
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

// --------------------------------------------------
// TAREAS ACTIVAS (general + individual)
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

// --------------------------------------------------
// TAREAS YA ENVIADAS (para desactivar botón)
// --------------------------------------------------
$realizadas = $supabase->from(
    "tareas_realizadas",
    "GET",
    null,
    "usuario_id=eq.$usuario_id"
);

$realizadas_map = [];
if ($realizadas) {
    foreach ($realizadas as $r) {
        $realizadas_map[$r["tarea_id"]] = $r["estado"]; // revision, aprobada, rechazada
    }
}

// Solo mostramos máximo 3 tareas
$tareas_mostrar = array_slice($tareas_filtradas, 0, 3);

// --------------------------------------------------
// LÍMITE DE TAREAS POR DÍA
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
$limiteDiario      = 3;
$puedeHacerHoy     = max(0, $limiteDiario - $cantRealizadasHoy);

// --------------------------------------------------
// PREMIOS
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

<header class="topbar-child">
    <div class="topbar-left">
        <div class="avatar-wrapper" id="avatarBtn">
            <?php if($fotoPerfil): ?>
                <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto perfil">
            <?php endif; ?>
        </div>

        <div class="hola">
            HOLA <span>@<?= strtoupper(htmlspecialchars($username)) ?></span>
        </div>
    </div>

    <div class="hamburger">
        <span></span><span></span><span></span>
    </div>
</header>

<main class="child-page">

    <div class="coins-pill">
        Tenés <span id="coins-counter" data-value="<?= $coins ?>">
            <?= number_format($coins, 0, ",", ".") ?>
        </span> Ti-Coins
    </div>

    <!-- ============================
         TAREAS
    ============================ -->
    <section class="card-tareas">
        <div class="card-tareas-header">Tareas Disponibles</div>
        <div class="card-tareas-body">

            <?php if ($puedeHacerHoy <= 0): ?>
                <p class="sin-tareas">¡Llegaste al máximo de tareas por hoy! 🌟</p>

            <?php elseif (empty($tareas_mostrar)): ?>
                <p class="sin-tareas">Por ahora no tenés tareas asignadas 😴</p>

            <?php else: ?>

                <?php foreach ($tareas_mostrar as $i => $tarea): ?>

                    <?php
                        $estado = $realizadas_map[$tarea["id"]] ?? null;
                        $yaEnviada = ($estado === "revision");
                    ?>

                    <div class="tarea-item">
                        <div class="tarea-titulo"><?= htmlspecialchars($tarea["titulo"]) ?></div>
                        <div class="tarea-coins"><?= (int)$tarea["coins_valor"] ?> TI-Coins</div>

                        <a href="<?= (!$yaEnviada && $puedeHacerHoy > 0) ? "tarea_realizar.php?id={$tarea['id']}" : "#" ?>"
                           class="tarea-btn"
                           style="<?= ($yaEnviada || $puedeHacerHoy <= 0) ? 'opacity:.5;pointer-events:none;' : '' ?>">
                            <?= $yaEnviada ? "En revisión…" : "Realizar" ?>
                        </a>
                    </div>

                    <?php if ($i < count($tareas_mostrar) - 1): ?>
                        <div class="tarea-separator"></div>
                    <?php endif; ?>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </section>

    <!-- ============================
         PREMIOS
    ============================ -->
    <section class="card-premios">
        <div class="card-premios-header">Premios Disponibles</div>

        <div class="card-premios-body">

            <?php if (!$premios): ?>
                <p class="sin-tareas">Todavía no hay premios cargados.</p>

            <?php else: ?>

                <?php foreach ($premios as $idx => $premio): ?>

                    <?php
                        $costo = (int)$premio["costo_coins"];
                        $puedeCobrar = $coins >= $costo;
                    ?>

                    <div class="premio-item">
                        <div class="premio-nombre">
                            <?= htmlspecialchars($premio["nombre"]) ?>
                        </div>

                        <div class="premio-coins">
                            <?= number_format($costo, 0, ",", ".") ?> TI-Coins
                        </div>

                        <a href="<?= $puedeCobrar ? "premio_cobrar.php?id={$premio['id']}" : '#' ?>"
                           class="premio-btn <?= $puedeCobrar ? 'ok' : 'no' ?>"
                           style="<?= $puedeCobrar ? '' : 'pointer-events:none;opacity:.7;' ?>">
                            Cobrar
                        </a>
                    </div>

                    <?php if ($idx < count($premios) - 1): ?>
                        <div class="premio-separator"></div>
                    <?php endif; ?>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </section>

    <p class="mensaje-amor">
        No te olvides nunca que tus tíos te aman mucho 💚
    </p>

</main>

<!-- ============================
     MODAL FOTO PERFIL
============================ -->
<div class="modal-overlay" id="modalFoto">
    <div class="modal">
        <h3>Tu foto de perfil</h3>

        <?php if($fotoPerfil): ?>
            <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto perfil grande">
        <?php else: ?>
            <div style="width:120px;height:120px;border-radius:50%;background:#ff3b30;margin:0 auto 10px;"></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="accion" value="cambiar_foto">
            <input type="file" name="nueva_foto" accept="image/*" required>
            <button class="premio-btn ok" style="margin-top:10px;">Cambiar foto</button>
        </form>

        <button type="button" class="modal-close" id="cerrarModal">Cerrar</button>

        <?php if(isset($error_foto)): ?>
            <p style="margin-top:10px;color:#e53935;font-size:13px;"><?= $error_foto ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // Animación coins
    const el = document.getElementById("coins-counter");
    const finalValue = parseInt(el.getAttribute("data-value"));
    let current = 0;
    const increment = finalValue / 30;

    function animate() {
        current += increment;
        if (current >= finalValue) {
            el.textContent = finalValue.toLocaleString("es-AR");
        } else {
            el.textContent = Math.floor(current).toLocaleString("es-AR");
            requestAnimationFrame(animate);
        }
    }
    animate();

    // Modal foto
    const avatarBtn  = document.getElementById("avatarBtn");
    const modalFoto  = document.getElementById("modalFoto");
    const cerrarBtn  = document.getElementById("cerrarModal");

    avatarBtn.onclick = () => modalFoto.style.display = "flex";
    cerrarBtn.onclick = () => modalFoto.style.display = "none";
    modalFoto.onclick = (e) => { if (e.target === modalFoto) modalFoto.style.display = "none"; };
});
</script>

</body>
</html>
