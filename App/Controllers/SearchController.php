<?php
namespace App\Controllers;

use App\Models\Product;

final class SearchController
{
    public function suggest(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $q = trim($_GET['q'] ?? '');

        $product = new Product();
        $result  = $product->suggest($q);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $q         = trim($_GET['q'] ?? '');
        $marca     = $_GET['marca']     ?? [];
        $storage   = $_GET['storage']   ?? [];
        $color     = $_GET['color']     ?? [];
        $minPrice  = $_GET['min_price'] ?? null;
        $maxPrice  = $_GET['max_price'] ?? null;
        $sort      = $_GET['sort']      ?? 'relevance';
        $page      = (int)($_GET['page']      ?? 1);
        $perPage   = (int)($_GET['per_page']  ?? 12);

        // Forzar arrays cuando viene un solo valor
        if (!is_array($marca)   && $marca   !== null) $marca   = [$marca];
        if (!is_array($storage) && $storage !== null) $storage = [$storage];
        if (!is_array($color)   && $color   !== null) $color   = [$color];

        $params = [
            'q'         => $q,
            'marca'     => $marca,
            'storage'   => $storage,
            'color'     => $color,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort'      => $sort,
            'page'      => $page,
            'per_page'  => $perPage,
        ];

        $product = new Product();
        $result  = $product->search($params);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }
}
