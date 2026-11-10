<?php
require_once __DIR__ . '/../config/db.php';
$db = getDB();
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("
        UPDATE penyedia_makanan SET
            nama_penyedia=:n, id_kategori=:k, alamat=:a,
            latitude=:lat, longitude=:lng,
            jam_buka=:jb, jam_tutup=:jt, no_telepon=:tel, foto=:f
        WHERE id_penyedia=:id
    ");
    $stmt->execute([
        ':n'=>$_POST['nama_penyedia'], ':k'=>$_POST['id_kategori'],
        ':a'=>$_POST['alamat'], ':lat'=>$_POST['latitude'], ':lng'=>$_POST['longitude'],
        ':jb'=>$_POST['jam_buka'], ':jt'=>$_POST['jam_tutup'],
        ':tel'=>$_POST['no_telepon'], ':f'=>$_POST['foto'], ':id'=>$id,
    ]);
    header('Location: index.php'); exit;
}

$data = $db->prepare("SELECT * FROM penyedia_makanan WHERE id_penyedia=:id");
$data->execute([':id'=>$id]);
$p = $data->fetch();
$kats = $db->query("SELECT * FROM kategori_makanan")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Penyedia - FoodMap GIS</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #F4F6F0; padding: 20px; color: #333;}
        .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,.1); border-top: 5px solid #800020; }
        h2 { color: #800020; margin-bottom: 20px; }
        label { font-weight: 600; font-size: .9rem; color: #555; display: block; margin-top: 15px; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: inherit; }
        input:focus { outline: none; border-color: #8A9A5B; box-shadow: 0 0 5px rgba(138, 154, 91, 0.3); }
        button { width: 100%; padding: 12px; background: #8A9A5B; color: white; border: none; border-radius: 6px; margin-top: 20px; font-size: 1rem; font-weight: bold; cursor: pointer; }
        button:hover { background: #6e7c46; }
        a.btn-back { display: inline-block; margin-top: 15px; color: #800020; text-decoration: none; font-weight: bold;}
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Data Penyedia #<?= $id ?></h2>
    
    <form method="POST">
        <label>Nama Penyedia</label>
        <input name="nama_penyedia" value="<?= htmlspecialchars($p['nama_penyedia']) ?>" required>
        
        <label>Kategori</label>
        <select name="id_kategori">
            <?php foreach ($kats as $k): ?>
                <option value="<?= $k['id_kategori'] ?>" <?= $k['id_kategori']==$p['id_kategori']?'selected':'' ?>>
                    <?= $k['nama_kategori'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Alamat</label>
        <textarea name="alamat" rows="3"><?= htmlspecialchars($p['alamat']) ?></textarea>

        <div style="display:flex; gap:10px;">
            <div style="flex:1;">
                <label>Latitude</label>
                <input name="latitude" value="<?= $p['latitude'] ?>">
            </div>
            <div style="flex:1;">
                <label>Longitude</label>
                <input name="longitude" value="<?= $p['longitude'] ?>">
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <div style="flex:1;">
                <label>Jam Buka</label>
                <input type="time" name="jam_buka" value="<?= $p['jam_buka'] ?>">
            </div>
            <div style="flex:1;">
                <label>Jam Tutup</label>
                <input type="time" name="jam_tutup" value="<?= $p['jam_tutup'] ?>">
            </div>
        </div>

        <label>No. Telepon</label>
        <input name="no_telepon" value="<?= $p['no_telepon'] ?>">

        <label>Nama File Foto</label>
        <input name="foto" value="<?= $p['foto'] ?>">

        <button type="submit">💾 Update Data</button>
    </form>
    
    <a href="index.php" class="btn-back">← Kembali ke Tabel Admin</a>
</div>

</body>
</html>