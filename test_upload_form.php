<?php
// Test interactivo de carga
session_start();

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/assets/uploads/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $file = $_FILES['test_file'];
    
    // Validaciones
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "Error de carga: " . $file['error'];
    } elseif (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/gif'])) {
        $error = "Tipo de archivo no permitido: " . $file['type'];
    } elseif ($file['size'] > 3 * 1024 * 1024) {
        $error = "Archivo muy grande: " . $file['size'] . " bytes";
    } else {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $target = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $result = "✓ Archivo subido exitosamente: " . $filename;
        } else {
            $error = "Fallo al mover el archivo a: " . $target;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test de Carga</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .success { color: green; padding: 10px; background: #e8f5e9; border: 1px solid green; }
        .error { color: red; padding: 10px; background: #ffebee; border: 1px solid red; }
        form { margin: 20px 0; }
        input[type="file"] { padding: 10px; }
        button { padding: 10px 20px; background: #2196F3; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test de Carga de Imágenes</h1>
    
    <?php if ($result): ?>
        <div class="success"><?= $result ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="test_file" accept="image/*" required>
        <button type="submit">Subir Imagen</button>
    </form>
    
    <h2>Información del Sistema</h2>
    <pre>
Document Root: <?= $_SERVER['DOCUMENT_ROOT'] ?>
Upload Dir: <?= $_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/assets/uploads/' ?>
Dir Existe: <?= (is_dir($_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/assets/uploads/') ? 'SÍ' : 'NO') ?>
Es Escribible: <?= (is_writable($_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/assets/uploads/') ? 'SÍ' : 'NO') ?>
    </pre>
</body>
</html>
