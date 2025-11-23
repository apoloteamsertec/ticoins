<?php include "header.php"; require_once "../config/supabase.php"; ?>

<?php
$id = $_GET["id"];
$premio = $supabase->from("premios", "GET", null, "id=eq.$id")[0];

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $update = [
        "nombre" => $_POST["nombre"],
        "descripcion" => $_POST["descripcion"],
        "costo_coins" => $_POST["costo"]
    ];

    // Si subió nueva imagen
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

        $update["imagen"] = "{$supabase->url}/storage/v1/object/public/premios/$nombreArchivo";
    }

    $supabase->from("premios", "PATCH", $update, "id=eq.$id");

    $mensaje = "Premio actualizado.";
    $premio = $supabase->from("premios", "GET", null, "id=eq.$id")[0];
}
?>

<h2>Editar premio</h2>

<?php if($mensaje): ?><p><?= $mensaje ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="form-box">

    <label>Nombre</label>
    <input type="text" name="nombre" value="<?= $premio['nombre'] ?>" required>

    <label>Descripción</label>
    <textarea name="descripcion"><?= $premio['descripcion'] ?></textarea>

    <label>Coins necesarios</label>
    <input type="number" name="costo" value="<?= $premio['costo_coins'] ?>" required>

    <label>Imagen (subir para reemplazar)</label>
    <input type="file" name="imagen">

    <button type="submit">Guardar cambios</button>
</form>

 </div><!-- .container -->
</div><!-- .page -->
</body>
</html>