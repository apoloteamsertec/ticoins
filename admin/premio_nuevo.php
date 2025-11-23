<?php include "header.php"; require_once "../config/supabase.php"; ?>

<?php
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $costo = $_POST["costo"];

    $imagenURL = null;

    // Subir imagen si hay
    if (!empty($_FILES["imagen"]["name"])) {
        $archivoTmp = $_FILES["imagen"]["tmp_name"];
        $nombreArchivo = time() . "_" . $_FILES["imagen"]["name"];

        $ch = curl_init("{$supabase->url}/storage/v1/object/premios/$nombreArchivo");
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_INFILE, fopen($archivoTmp, 'r'));
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($archivoTmp));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$supabase->service_key}",
            "Authorization: Bearer {$supabase->service_key}",
            "Content-Type: application/octet-stream"
        ]);

        curl_exec($ch);

        // URL pública
        $imagenURL = "{$supabase->url}/storage/v1/object/public/premios/$nombreArchivo";
    }

    // Insertar premio
    $premio = $supabase->from("premios", "POST", [
        "nombre" => $nombre,
        "descripcion" => $descripcion,
        "costo_coins" => $costo,
        "imagen" => $imagenURL
    ]);

    $mensaje = "Premio creado correctamente.";
}
?>

<h2>Nuevo premio</h2>

<?php if($mensaje): ?><p><?= $mensaje ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="form-box">

    <label>Nombre del premio</label>
    <input type="text" name="nombre" required>

    <label>Descripción</label>
    <textarea name="descripcion"></textarea>

    <label>Coins necesarios</label>
    <input type="number" name="costo" required>

    <label>Imagen (opcional)</label>
    <input type="file" name="imagen">

    <button type="submit">Crear premio</button>
</form>

</div> <!-- cierre container -->
</body>
</html>