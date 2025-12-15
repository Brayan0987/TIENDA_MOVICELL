<?php
require_once __DIR__ . '/../../Core/conexion.php';

/**
 * getOptions seguro: solo permite tablas/campos del whitelist para evitar inyección.
 * Retorna string con <option value="...">...</option>
 */
function getOptions(string $tabla) : string {
    $map = [
        'producto' => ['id'=>'id_producto','campo'=>'nombre','tabla'=>'producto'],
        'marcas' => ['id'=>'id_marcas','campo'=>'marca','tabla'=>'marcas'],
        'color' => ['id'=>'id_color','campo'=>'color','tabla'=>'color'],
        'ram' => ['id'=>'id_ram','campo'=>'ram','tabla'=>'ram'],
        'almacenamiento' => ['id'=>'id_almacenamiento','campo'=>'almacenamiento','tabla'=>'almacenamiento'],
        'precio' => ['id'=>'id_precio','campo'=>'precio','tabla'=>'precio'],
    ];

    if (!isset($map[$tabla])) {
        return '';
    }

    $cfg = $map[$tabla];
    $con = conectar();
    $options = '';
    if ($con) {
        $query = "SELECT `{$cfg['id']}`, `{$cfg['campo']}` FROM `{$cfg['tabla']}`";
        $result = mysqli_query($con, $query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $options .= "<option value='" . htmlspecialchars($row[$cfg['id']], ENT_QUOTES) . "'>" . htmlspecialchars($row[$cfg['campo']], ENT_QUOTES) . "</option>";
            }
            mysqli_free_result($result);
        }
    }
    return $options;
}