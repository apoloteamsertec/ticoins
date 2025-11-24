<?php
session_start();
require_once "../config/supabase.php";

// ----------------------------------------
// VALIDAR SESIÓN
// ----------------------------------------
if (!isset($_SESSION["child_id"])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION["child_id"];

// ----------------------------------------
// VALIDAR ID DEL PREMIO
// ----------------------------------------
if (!isset($_GET["id"])) {
    die("Premio no especificado.");
}

$premio_id = $_GET["id"];

// ----------------------------------------
// OBTENER PREMIO
// ----------------------------------------
$premio = $supabase->from("premios", "GET", null, "id=eq.$premio_id");
if (!$premio) {
    die("Premio no encontrado.");
}
$premio = $premio[0];

$valor = (int)$premio["coins_valor"];

// ----------------------------------------
// OBTENER COINS DEL NIÑO
// ----------------------------------------
$perfil = $supabase->from("profiles", "GET", null, "id=eq.$usuario_id");
$coins_actuales = (int)$perfil[0]["coins"];

// ----------------------------------------
// VERIFICAR SI TIENE SUFICIENTES COINS
// ----------------------------------------
if ($coins_actuales < $valor) {
    die("No tenés suficientes Ti-Coins para este premio.");
}

// ----------------------------------------
// EVITAR CANJEAR EL MISMO PREMIO 2 VECES HOY
// ----------------------------------------
$hoy = date("Y-m-d");

$canje_existente = $supabase->from(
    "premios_canjeados",
    "GET",
    null,
    "usuario_id=eq.$usuario_id&premio_id=eq.$premio_id&fecha_canje=eq.$hoy"
);

if (!empty($canje_existente)) {
    die("Ya canjeaste este premio hoy.");
}

// ----------------------------------------
// REGISTRAR EN premios_canjeados
// ----------------------------------------
$insert = $supabase->from("premios_canjeados", "POST", [
    "usuario_id"   => $usuario_id,
    "premio_id"    => $premio_id,
    "fecha_canje"  => $hoy,
    "coins_gastados" => $valor
]);

// ----------------------------------------
// DESCONTAR COINS DEL PERFIL
// ----------------------------------------
$nuevos_coins = $coins_actuales - $valor;

$update = $supabase->from("profiles", "PATCH", [
    "coins" => $nuevos_coins
], "id=eq.$usuario_id");

// ----------------------------------------
// REDIRIGIR A PANTALLA DE CONFIRMACIÓN
// ----------------------------------------
header("Location: premio_cobrado.php?premio=" . urlencode($premio["nombre"]));
exit;

?>
