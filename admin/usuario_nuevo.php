<?php
session_start();
require '../config/supabase.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = $_POST["nombre"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $emailFake = $username . "@ticoins.local";

    // Crear usuario en Auth
    $payload = json_encode([
        "email" => $emailFake,
        "password" => $password
    ]);

    $curl = curl_init(getenv("SUPABASE_URL") . "/auth/v1/signup");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        "apikey: " . getenv("SUPABASE_PUBLISHABLE_KEY"),
        "Authorization: Bearer " . getenv("SUPABASE_PUBLISHABLE_KEY"),
        "Content-Type: application/json"
    ]);

    $res = curl_exec($curl);
    $nuevo = json_decode($res, true);

    $id = $nuevo["user"]["id"];

    // Insertar perfil
    $supabase->from("profiles", "POST", [
        "id" => $id,
        "nombre_completo" => $nombre,
        "username" => $username,
        "rol" => "nino",
        "coins" => 0
    ]);

    header("Location: usuarios.php");
    exit;
}
?>
<?php include 'header.php'; ?>

<h2>Nuevo Usuario</h2>

<form method="POST">
    <input type="text" name="nombre" placeholder="Nombre completo" required><br>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Contraseña" required><br>
    <button type="submit">Crear usuario</button>
</form>
