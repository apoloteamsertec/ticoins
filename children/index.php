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
$perfil          = $perfilRes[0];
$coins           = (int)$perfil["coins"];
$username        = $perfil["username"];
$nombreCompleto  = $perfil["nombre_completo"];
$fotoPerfil      = !empty($perfil["foto_perfil"]) ? $perfil["foto_perfil"] : null;

// --------------------------------------------------
// CAMBIAR FOTO DE PERFIL (si viene POST de modal)
// --------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["accion"]) && $_POST["accion"] === "cambiar_foto") {

    if (!isset($_FILES["nueva_foto"]) || $_FILES["nueva_foto"]["error"] !== UPLOAD_ERR_OK) {
        $error_foto = "Debes seleccionar una imagen válida.";
    } else {

        // Crear carpeta si no existe
        $carpeta = __DIR__ . "/../uploads/perfiles/";
        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        // Nombre de archivo seguro
        $nombre_archivo = "avatar_" . $usuario_id . "_" . time() . "_" .
            preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES["nueva_foto"]["name"]);

        $ruta_absoluta = $carpeta . $nombre_archivo;
        $ruta_publica  = "/uploads/perfiles/" . $nombre_archivo;

        if (!move_uploaded_file($_FILES["nueva_foto"]["tmp_name"], $ruta_absoluta)) {
            $error_foto = "No se pudo guardar la foto en el servidor.";
        } else {
            // Guardar en Supabase
            $supabase->from("profiles", "PATCH", [
                "foto_perfil" => $ruta_publica
            ], "id=eq.$usuario_id");

            // Actualizar variable local
            $fotoPerfil = $ruta_publica;
        }
    }
}

// --------------------------------------------------
// TAREAS ACTIVAS PARA ESTE NIÑO
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

// Máximo 3 tareas visibles en la tarjeta
$tareas_mostrar = array_slice($tareas_filtradas, 0, 3);

// --------------------------------------------------
// LÍMITE DE 3 TAREAS REALIZADAS POR DÍA
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
// PREMIOS DISPONIBLES
// --------------------------------------------------
// Ajusta el filtro según tu tabla (ej: activo=eq.true si existe)
$premios = $supabase->from("premios", "GET", null, "");
// Se asume columna coins_costo en la tabla premios

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
/* Avatar circular arriba a la izquierda */
.avatar-wrapper{
    width:40px;
    height:40px;
    border-radius:50%;
    background:#ff3b30; /* rojo por defecto */
    overflow:hidden;
    margin-right:10px;
    flex-shrink:0;
    cursor:pointer;
    border:2px solid #fff;
}
.avatar-wrapper img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* Header con avatar y texto */
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
    z-index:999;
}
.topbar-left{
    display:flex;
    align-items:center;
}
.topbar-child .hola{
    font-size:14px;
    font-weight:500;
}
.topbar-child .hola span{
    font-weight:700;
}

/* Tarjeta de premios (azul) */
.card-premios{
    width:100%;
    background:#fff;
    border-radius:26px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    margin-top:24px;
}
.card-premios-header{
    background:#0070c9;
    padding:14px;
    text-align:center;
    color:white;
    font-size:18px;
    font-weight:600;
}
.card-premios-body{
    padding:12px 18px 18px;
    text-align:center;
}
.premio-item{
    padding:10px 4px;
}
.premio-nombre{
    font-size:15px;
    font-weight:600;
    margin-bottom:4px;
}
.premio-coins{
    color:#1c8b3a;
    font-weight:600;
    font-size:13px;
    margin-bottom:10px;
}
.premio-btn{
    display:inline-block;
    padding:7px 28px;
    border-radius:999px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    box-shadow:0 5px 12px rgba(0,0,0,0.20);
    transition:.2s;
    color:#fff;
}
.premio-btn.ok{
    background:#0070c9; /* Azul cuando alcanza */
}
.premio-btn.no{
    background:#ff9800; /* Naranja cuando NO alcanza */
}
.premio-separator{
    width:80%;
    margin:10px auto;
    height:1px;
    background:#e0e0e0;
}

/* Modal para foto de perfil */
.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.4);
    display:none;
    align-items:center;
    justify-content:center;
    z-index:9999;
}
.modal{
    background:#fff;
    border-radius:16px;
    padding:18px;
    max-width:320px;
    width:90%;
    text-align:center;
}
.modal img{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    margin-bottom:10px;
}
.modal h3{
    margin-bottom:10px;
}
.modal-close{
    margin-top:10px;
    background:#ccc;
    border:none;
    border-radius:8px;
    padding:8px 14px;
    cursor:pointer;
}
</style>
</head>

<body>

<!-- ==============================
     BARRA SUPERIOR DEL NIÑO
==================================-->
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


<!-- ==============================
     CONTENIDO CENTRAL
==================================-->
<main class="child-page">

    <!-- PILLS DE COINS -->
    <div class="coins-pill">
        Tenés <span id="coins-counter" data-value="<?= $coins ?>"><?= number_format($coins, 0, ",", ".") ?></span> Ti-Coins
    </div>


    <!-- TARJETA DE TAREAS DISPONIBLES -->
    <section class="card-tareas">
        <div class="card-tareas-header">
            Tareas Disponibles
        </div>

        <div class="card-tareas-body">

            <?php if ($puedeHacerHoy <= 0): ?>
                <p class="sin-tareas">
                    ¡Llegaste al máximo de tareas por hoy! 🌟
                </p>
            <?php elseif (empty($tareas_mostrar)): ?>
                <p class="sin-tareas">
                    Por ahora no tenés tareas asignadas 😴
                </p>
            <?php else: ?>

                <?php foreach ($tareas_mostrar as $i => $tarea): ?>
                <div class="tarea-item">

                    <div class="tarea-titulo">
                        <?= htmlspecialchars($tarea["titulo"]) ?>
                    </div>

                    <div class="tarea-coins">
                        <?= (int)$tarea["coins_valor"] ?> TI-Coins
                    </div>

                    <a href="<?= $puedeHacerHoy > 0 ? 'tarea_realizar.php?id=' . $tarea["id"] : '#' ?>"
                       class="tarea-btn"
                       <?php if($puedeHacerHoy <= 0): ?>style="opacity:.5;pointer-events:none;"<?php endif; ?>>
                        Realizar
                    </a>

                </div>

                <?php if($i < count($tareas_mostrar)-1): ?>
                    <div class="tarea-separator"></div>
                <?php endif; ?>

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
                        // Ajusta "coins_costo" al nombre correcto en tu tabla
                        $costo = (int)$premio["coins_costo"];
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
                           <?php if(!$puedeCobrar): ?>style="pointer-events:none;opacity:.8;"<?php endif; ?>>
                            Cobrar
                        </a>
                    </div>

                    <?php if($idx < count($premios)-1): ?>
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

<!-- MODAL FOTO PERFIL -->
<div class="modal-overlay" id="modalFoto">
    <div class="modal">
        <h3>Tu foto de perfil</h3>

        <?php if($fotoPerfil): ?>
            <img src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto perfil grande">
        <?php else: ?>
            <div style="width:120px;height:120px;border-radius:50%;background:#ff3b30;margin:0 auto 10px;"></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" style="margin-top:10px;">
            <input type="hidden" name="accion" value="cambiar_foto">
            <input type="file" name="nueva_foto" accept="image/*" required>
            <button type="submit" class="premio-btn ok" style="margin-top:8px;">Cambiar foto</button>
        </form>

        <button type="button" class="modal-close" id="cerrarModal">Cerrar</button>

        <?php if(isset($error_foto)): ?>
            <p style="margin-top:8px;color:#e53935;font-size:13px;"><?= $error_foto ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
// Animación del contador de coins
document.addEventListener("DOMContentLoaded", function () {
    const el = document.getElementById("coins-counter");
    const finalValue = parseInt(el.getAttribute("data-value"));
    let current = 0;

    const duration = 900;
    const steps = 30;
    const increment = finalValue / steps;

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

    // Modal foto de perfil
    const avatarBtn  = document.getElementById("avatarBtn");
    const modalFoto  = document.getElementById("modalFoto");
    const cerrarBtn  = document.getElementById("cerrarModal");

    avatarBtn.addEventListener("click", () => {
        modalFoto.style.display = "flex";
    });
    cerrarBtn.addEventListener("click", () => {
        modalFoto.style.display = "none";
    });
    modalFoto.addEventListener("click", (e) => {
        if (e.target === modalFoto) modalFoto.style.display = "none";
    });
});
</script>

</body>
</html>
