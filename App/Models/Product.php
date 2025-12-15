<?php
namespace App\Models;
use App\Core\Db;
use mysqli;

final class Product {
  private mysqli $db;
  public function __construct(){ $this->db = Db::conn(); }

  public function suggest(string $q, int $limit=8): array {
    $tokens = array_values(array_filter(preg_split('/\s+/u', trim($q)), fn($t)=>mb_strlen($t)>=2));
    if (!$tokens) return [];
    $w = implode(' AND ', array_fill(0, count($tokens), 'nombre LIKE ?'));
    $types = str_repeat('s', count($tokens)+1);
    $params = array_map(fn($t)=>'%'.$t.'%', $tokens);
    $pref = $tokens.'%'; $params[] = $pref;
    $sql = "SELECT id,nombre FROM productos WHERE $w
            ORDER BY CASE WHEN nombre LIKE ? THEN 0 ELSE 1 END, CHAR_LENGTH(nombre) ASC
            LIMIT ?";
    $types .= 'i'; $params[] = $limit;

    $stmt = $this->db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $out=[]; while($r=$res->fetch_assoc()) $out[]=['id'=>(int)$r['id'],'nombre'=>$r['nombre']];
    $stmt->close();
    return $out;
  }

  public function search(array $q): array {
    $where=[]; $types=''; $params=[];
    if (!empty($q['q']) && mb_strlen($q['q'])>=2) {
      $w = '%'.$q['q'].'%'; $where[]="(p.nombre LIKE ? OR p.descripcion LIKE ?)"; $types.='ss'; $params[]=$w; $params[]=$w;
    }
    if (!empty($q['marca'])) {
      $in=implode(',', array_fill(0,count($q['marca']),'?')); $where[]="p.id_marcas IN ($in)";
      foreach($q['marca'] as $m){ $types.='i'; $params[]=(int)$m; }
    }
    if (!empty($q['storage'])) {
      $in=implode(',', array_fill(0,count($q['storage']),'?')); $where[]="p.almacenamiento_gb IN ($in)";
      foreach($q['storage'] as $s){ $types.='i'; $params[]=(int)$s; }
    }
    if (!empty($q['color'])) {
      $in=implode(',', array_fill(0,count($q['color']),'?')); $where[]="p.color IN ($in)";
      foreach($q['color'] as $c){ $types.='s'; $params[]=$c; }
    }
    if (isset($q['min_price'])){ $where[]="COALESCE(p.sale_price,p.precio) >= ?"; $types.='d'; $params[]=(float)$q['min_price']; }
    if (isset($q['max_price'])){ $where[]="COALESCE(p.sale_price,p.precio) <= ?"; $types.='d'; $params[]=(float)$q['max_price']; }
    $whereSql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

    $order='p.id DESC';
    if (($q['sort']??'')==='price_asc')  $order='COALESCE(p.sale_price,p.precio) ASC';
    if (($q['sort']??'')==='price_desc') $order='COALESCE(p.sale_price,p.precio) DESC';
    if (($q['sort']??'')==='newest')     $order='p.created_at DESC';
    $orderTypes=''; $orderParams=[];
    if (($q['sort']??'')==='relevance' && !empty($q['q']) && mb_strlen($q['q'])>=2){
      $order="CASE WHEN p.nombre LIKE ? THEN 0 WHEN p.descripcion LIKE ? THEN 1 ELSE 2 END, p.created_at DESC";
      $orderTypes='ss'; $orderParams=['%'.$q['q'].'%','%'.$q['q'].'%'];
    }

    // COUNT
    $stmt=$this->db->prepare("SELECT COUNT(*) n FROM productos p $whereSql");
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute(); $total=(int)$stmt->get_result()->fetch_assoc()['n']; $stmt->close();

    // RANGO
    $stmt=$this->db->prepare("SELECT MIN(COALESCE(p.sale_price,p.precio)) minp, MAX(COALESCE(p.sale_price,p.precio)) maxp FROM productos p $whereSql");
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute(); $range=$stmt->get_result()->fetch_assoc(); $stmt->close();

    // Paginación
    $page=max(1,(int)($q['page']??1)); $per=min(48,max(6,(int)($q['per_page']??12))); $off=($page-1)*$per;

    // PRODUCTOS
    $sql="SELECT p.id,p.nombre,p.descripcion,p.precio,p.sale_price,p.created_at,
                 p.color,p.almacenamiento_gb,p.ram_gb,p.sku,p.imagen_url,m.marca
          FROM productos p LEFT JOIN marcas m ON m.id_marcas=p.id_marcas
          $whereSql ORDER BY $order LIMIT ? OFFSET ?";
    $prodTypes=$types.$orderTypes.'ii'; $prodParams=array_merge($params,$orderParams,[$per,$off]);
    $stmt=$this->db->prepare($sql); if ($prodTypes) $stmt->bind_param($prodTypes, ...$prodParams);
    $stmt->execute(); $res=$stmt->get_result();
    $products=[];
    while($r=$res->fetch_assoc()){
      $price = $r['sale_price'] ?? $r['precio'];
      $products[]=[
        'id'=>(int)$r['id'],'nombre'=>$r['nombre'],'marca'=>$r['marca'],'color'=>$r['color'],
        'almacenamiento_gb'=>$r['almacenamiento_gb']!==null?(int)$r['almacenamiento_gb']:null,
        'ram_gb'=>$r['ram_gb']!==null?(int)$r['ram_gb']:null,
        'precio'=>(float)$r['precio'],'sale_price'=>$r['sale_price']!==null?(float)$r['sale_price']:null,
        'price_effective'=>(float)$price,'image_url'=>$r['imagen_url'],
        'is_new'=>($r['created_at']!==null && strtotime($r['created_at'])>=strtotime('-30 days')),
        'is_on_sale'=>($r['sale_price']!==null)
      ];
    }
    $stmt->close();

    // FACETAS
    $fac_marca=[]; $mSql="SELECT m.id_marcas id,m.marca nombre,COUNT(*) n
                          FROM productos p LEFT JOIN marcas m ON m.id_marcas=p.id_marcas
                          $whereSql GROUP BY m.id_marcas,m.marca ORDER BY n DESC";
    $stmt=$this->db->prepare($mSql); if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute(); $r=$stmt->get_result();
    while($row=$r->fetch_assoc()){ if($row['id']) $fac_marca[]=['id'=>(int)$row['id'],'nombre'=>$row['nombre'],'count'=>(int)$row['n']]; }
    $stmt->close();

    $fac_storage=$this->facet($whereSql,$types,$params,"SELECT p.almacenamiento_gb,COUNT(*) FROM productos p");
    $fac_color  =$this->facet($whereSql,$types,$params,"SELECT p.color,COUNT(*) FROM productos p");

    return [
      'total'=>$total,
      'price_min'=>isset($range['minp'])?(float)$range['minp']:0,
      'price_max'=>isset($range['maxp'])?(float)$range['maxp']:0,
      'products'=>$products,
      'facets'=>['marcas'=>$fac_marca,'almacenamiento_gb'=>$fac_storage,'colores'=>$fac_color],
    ];
  }

  private function facet(string $whereSql, string $types, array $params, string $sql): array {
    $stmt=$this->db->prepare($sql.' '.$whereSql.' GROUP BY 1 ORDER BY COUNT(*) DESC');
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute(); $r=$stmt->get_result(); $out=[];
    while($row=$r->fetch_row()){ $out[]=['value'=>$row,'count'=>(int)$row[9]]; }
    $stmt->close(); return $out;
  }
}
