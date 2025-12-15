<?php
// Archivo de prueba para verificar carga de imágenes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "=== TEST DE CARGA DE IMÁGENES ===\n\n";

// 1. Verificar DOCUMENT_ROOT
echo "1. DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

// 2. Verificar ruta de uploads
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/TIENDA_MOVICELL/Public/assets/uploads/';
echo "2. Ruta de uploads: " . $uploadDir . "\n";
echo "   Existe: " . (is_dir($uploadDir) ? "SÍ" : "NO") . "\n";
echo "   Escribible: " . (is_writable($uploadDir) ? "SÍ" : "NO") . "\n";

// 3. Verificar permisos de directorios padres
$parentDir = dirname($uploadDir);
echo "3. Directorio padre: " . $parentDir . "\n";
echo "   Existe: " . (is_dir($parentDir) ? "SÍ" : "NO") . "\n";
echo "   Escribible: " . (is_writable($parentDir) ? "SÍ" : "NO") . "\n";

// 4. Listar archivos si existen
if (is_dir($uploadDir)) {
    echo "4. Archivos en la carpeta:\n";
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "   - " . $file . "\n";
        }
    }
}

// 5. Información de $_FILES
echo "5. \$_FILES:\n";
echo "   isset(\$_FILES['imagen']): " . (isset($_FILES['imagen']) ? "SÍ" : "NO") . "\n";
if (isset($_FILES['imagen'])) {
    echo "   error: " . $_FILES['imagen']['error'] . "\n";
    echo "   name: " . $_FILES['imagen']['name'] . "\n";
    echo "   size: " . $_FILES['imagen']['size'] . "\n";
    echo "   tmp_name: " . $_FILES['imagen']['tmp_name'] . "\n";
}

// 6. Configuración de PHP
echo "6. Configuración PHP:\n";
echo "   upload_tmp_dir: " . ini_get('upload_tmp_dir') . "\n";
echo "   upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   post_max_size: " . ini_get('post_max_size') . "\n";
echo "   file_uploads: " . ini_get('file_uploads') . "\n";

?>
