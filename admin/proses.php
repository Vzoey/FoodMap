<?php
require_once __DIR__ . '/../config/db.php';
$db = getDB();
$aksi = $_GET['aksi'] ?? $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {
    $stmt = $db->prepare("
        INSERT INTO penyedia_makanan
        (nama_penyedia, id_kategori, alamat, latitude, longitude, jam_buka, jam_tutup, no_telepon, foto)
        VALUES (:n,:k,:a,:lat,:lng,:jb,:jt,:tel,:f)
    ");
    $stmt->execute([
        ':n'=>$_POST['nama_penyedia'], ':k'=>$_POST['id_kategori'],
        ':a'=>$_POST['alamat'], ':lat'=>$_POST['latitude'], ':lng'=>$_POST['longitude'],
        ':jb'=>$_POST['jam_buka'], ':jt'=>$_POST['jam_tutup'],
        ':tel'=>$_POST['no_telepon'], ':f'=>$_POST['foto'] ?: 'default.jpg',
    ]);
    header('Location: index.php'); exit;
}

if ($aksi === 'hapus') {
    $stmt = $db->prepare("DELETE FROM penyedia_makanan WHERE id_penyedia = :id");
    $stmt->execute([':id' => (int)$_GET['id']]);
    header('Location: index.php'); exit;
}