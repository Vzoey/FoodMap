<?php
// api/get_penyedia.php
require_once __DIR__ . '/../config/db.php';

$db        = getDB();
$keyword   = $_GET['q']           ?? '';
$kategori  = $_GET['kategori']    ?? '';
$harga_min = $_GET['harga_min']   ?? '';
$harga_max = $_GET['harga_max']   ?? '';

$sql = "
    SELECT
        p.id_penyedia,
        p.nama_penyedia,
        k.nama_kategori,
        p.alamat,
        p.latitude,
        p.longitude,
        p.jam_buka,
        p.jam_tutup,
        p.no_telepon,
        p.foto,
        MIN(m.harga) AS harga_min,
        MAX(m.harga) AS harga_max
    FROM penyedia_makanan p
    JOIN kategori_makanan k ON p.id_kategori = k.id_kategori
    LEFT JOIN menu_makanan m ON p.id_penyedia = m.id_penyedia
    WHERE 1=1
";

$params = [];

if ($keyword !== '') {
    $sql .= " AND (p.nama_penyedia LIKE :kw OR p.alamat LIKE :kw) ";
    $params[':kw'] = "%$keyword%";
}
if ($kategori !== '') {
    $sql .= " AND k.nama_kategori = :kat ";
    $params[':kat'] = $kategori;
}
if ($harga_min !== '' && $harga_max !== '') {
    // Filter by range harga menu: penyedia yang PUNYA menu di range tsb
    $sql .= " AND p.id_penyedia IN (
                SELECT id_penyedia FROM menu_makanan
                WHERE harga BETWEEN :hmin AND :hmax
              ) ";
    $params[':hmin'] = (float)$harga_min;
    $params[':hmax'] = (float)$harga_max;
}

$sql .= " GROUP BY p.id_penyedia ORDER BY p.nama_penyedia ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

jsonResponse([
    'status' => 'success',
    'count'  => count($rows),
    'data'   => $rows
]);