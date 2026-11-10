<?php require_once __DIR__ . '/../config/db.php'; $db = getDB(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — FoodMap GIS</title>
    <style>
        :root {
            --burgundy: #800020;
            --burgundy-dark: #5c0017;
            --matcha: #8A9A5B;
            --matcha-dark: #6e7c46;
            --bg: #F4F6F0;
            --white: #FDFBF7;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Roboto, sans-serif; background: var(--bg); padding: 20px; color: #333;}
        
        .header-admin { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 20px; border-bottom: 3px solid var(--burgundy); padding-bottom: 10px;
        }
        h1 { color: var(--burgundy); font-size: 1.5rem; }
        a.btn-kembali { color: var(--matcha-dark); font-weight: bold; text-decoration: none; }
        a.btn-kembali:hover { text-decoration: underline; }

        /* Tabel Responsif */
        .table-responsive { overflow-x: auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.05); }
        table { width: 100%; min-width: 800px; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; font-size: .9rem; }
        th { background: var(--burgundy); color: white; }
        tr:hover { background: #f9f9f9; }

        .btn { padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; font-size: .85rem; font-weight: 600; text-decoration: none; display: inline-block;}
        .btn-add { background: var(--matcha); margin-bottom: 15px; }
        .btn-edit { background: #1971c2; }
        .btn-del { background: var(--burgundy); }
        .btn:hover { opacity: .85; }

        /* Form Grid */
        form { 
            background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; 
            display: none; grid-template-columns: 1fr 1fr; gap: 15px; 
            border-top: 4px solid var(--matcha);
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
        form label { font-size: .85rem; font-weight: 600; display: block; margin-bottom: 5px; color: var(--burgundy);}
        form input, form select, form textarea { padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 100%; font-family: inherit;}
        form input:focus { outline: none; border-color: var(--matcha); }
        .full { grid-column: 1 / -1; }

        /* Form HP */
        @media (max-width: 600px) {
            form { grid-template-columns: 1fr; }
            .header-admin { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>
<body>

<div class="header-admin">
    <h1>⚙️ Admin — FoodMap GIS</h1>
    <a href="../index.php" class="btn-kembali">← Kembali ke Peta</a>
</div>

<button class="btn btn-add" onclick="document.getElementById('formTambah').style.display='grid'">
    + Tambah Penyedia
</button>

<form id="formTambah" method="POST" action="proses.php">
    <input type="hidden" name="aksi" value="tambah">

    <div><label>Nama Penyedia</label><input name="nama_penyedia" required placeholder="Contoh: Warung Nasi Bu Imas"></div>
    <div><label>Kategori</label>
        <select name="id_kategori" required>
            <?php
            $kats = $db->query("SELECT * FROM kategori_makanan")->fetchAll();
            foreach ($kats as $k) echo "<option value='{$k['id_kategori']}'>{$k['nama_kategori']}</option>";
            ?>
        </select>
    </div>
    <div><label>Latitude</label><input name="latitude" type="number" step="any" required placeholder="-6.9175"></div>
    <div><label>Longitude</label><input name="longitude" type="number" step="any" required placeholder="107.6191"></div>
    <div class="full"><label>Alamat Lengkap</label><textarea name="alamat" rows="2" required></textarea></div>
    <div><label>Jam Buka</label><input name="jam_buka" type="time" required></div>
    <div><label>Jam Tutup</label><input name="jam_tutup" type="time" required></div>
    <div><label>No. Telepon</label><input name="no_telepon"></div>
    <div><label>Nama File Foto</label><input name="foto" placeholder="foto.jpg"></div>
    
    <div class="full">
        <button class="btn btn-add" type="submit" style="width:100%; padding:12px; font-size:1rem;">💾 Simpan Data</button>
    </div>
</form>

<?php
$rows = $db->query("
    SELECT p.*, k.nama_kategori
    FROM penyedia_makanan p
    JOIN kategori_makanan k ON p.id_kategori = k.id_kategori
    ORDER BY p.id_penyedia DESC
")->fetchAll();
?>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Nama</th><th>Kategori</th><th>Alamat</th>
                <th>Lat, Lng</th><th>Jam</th><th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= $r['id_penyedia'] ?></td>
                <td><b><?= htmlspecialchars($r['nama_penyedia']) ?></b></td>
                <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
                <td><?= htmlspecialchars($r['alamat']) ?></td>
                <td><?= $r['latitude'] ?>, <br><?= $r['longitude'] ?></td>
                <td><?= $r['jam_buka'] ?> - <?= $r['jam_tutup'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $r['id_penyedia'] ?>" class="btn btn-edit">Edit</a>
                    <a href="proses.php?aksi=hapus&id=<?= $r['id_penyedia'] ?>"
                       class="btn btn-del"
                       onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>