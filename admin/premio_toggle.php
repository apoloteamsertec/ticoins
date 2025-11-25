

<?php require "../auth/check_admin.php"; require_once "../config/supabase.php";

$id = $_GET["id"];

$premio = $supabase->from("premios", "GET", null, "id=eq.$id")[0];
$newState = !$premio["activo"];

$supabase->from("premios", "PATCH", [
    "activo" => $newState
], "id=eq.$id");

header("Location: premios.php");
exit;
