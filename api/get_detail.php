<?php
// api/get_detail.php
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) jsonResponse(['status' => 'error', 'message' => 'ID tidak valid'], 400);

$db = getDB();

$stmt = $db->prepare("
    SELECT p.*, k.nama_kategori
    FROM penyedia_makanan p
    JOIN kategori_makanan k ON p.id_kategori = k.id_kategori
    WHERE p.id_penyedia = :id
");
$stmt->execute([':id' => $id]);
$penyedia = $stmt->fetch();

if (!$penyedia) jsonResponse(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);

$stmt2 = $db->prepare("
    SELECT id_menu, nama_menu, harga
    FROM menu_makanan
    WHERE id_penyedia = :id
    ORDER BY harga ASC
");
$stmt2->execute([':id' => $id]);
$menu = $stmt2->fetchAll();

jsonResponse([
    'status'   => 'success',
    'penyedia' => $penyedia,
    'menu'     => $menu
]);