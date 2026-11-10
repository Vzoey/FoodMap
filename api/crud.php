<?php
// api/crud.php
require_once __DIR__ . '/../config/db.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

switch ($method) {

    // ------------------------- CREATE -------------------------
    case 'POST':
        $nama     = trim($_POST['nama_penyedia'] ?? '');
        $kategori = (int)($_POST['id_kategori'] ?? 0);
        $alamat   = trim($_POST['alamat'] ?? '');
        $lat      = (float)($_POST['latitude'] ?? 0);
        $lng      = (float)($_POST['longitude'] ?? 0);
        $jamBuka  = $_POST['jam_buka']  ?? null;
        $jamTutup = $_POST['jam_tutup'] ?? null;
        $telepon  = trim($_POST['no_telepon'] ?? '');
        $foto     = trim($_POST['foto'] ?? 'default.jpg');

        // Validasi koordinat (syarat GIS)
        if (!$nama || !$kategori || $lat == 0 || $lng == 0) {
            jsonResponse(['status'=>'error','message'=>'Data wajib tidak lengkap'], 400);
        }
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            jsonResponse(['status'=>'error','message'=>'Koordinat tidak valid'], 400);
        }

        $stmt = $db->prepare("
            INSERT INTO penyedia_makanan
              (nama_penyedia, id_kategori, alamat, latitude, longitude,
               jam_buka, jam_tutup, no_telepon, foto)
            VALUES (:nama, :kat, :alamat, :lat, :lng, :buka, :tutup, :telp, :foto)
        ");
        $stmt->execute([
            ':nama'=>$nama, ':kat'=>$kategori, ':alamat'=>$alamat,
            ':lat'=>$lat, ':lng'=>$lng, ':buka'=>$jamBuka, ':tutup'=>$jamTutup,
            ':telp'=>$telepon, ':foto'=>$foto
        ]);
        jsonResponse(['status'=>'success','message'=>'Data ditambahkan','id'=>$db->lastInsertId()]);
        break;

    // ------------------------- UPDATE -------------------------
    case 'PUT':
        if ($id <= 0) jsonResponse(['status'=>'error','message'=>'ID wajib'], 400);

        parse_str(file_get_contents('php://input'), $put);
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['nama_penyedia','id_kategori','alamat','latitude','longitude',
                    'jam_buka','jam_tutup','no_telepon','foto'];
        foreach ($allowed as $f) {
            if (isset($put[$f])) {
                $fields[] = "$f = :$f";
                $params[":$f"] = $put[$f];
            }
        }
        if (empty($fields)) jsonResponse(['status'=>'error','message'=>'Tidak ada data'], 400);

        $stmt = $db->prepare("UPDATE penyedia_makanan SET " . implode(',', $fields) . " WHERE id_penyedia = :id");
        $stmt->execute($params);
        jsonResponse(['status'=>'success','message'=>'Data diperbarui']);
        break;

    // ------------------------- DELETE -------------------------
    case 'DELETE':
        if ($id <= 0) jsonResponse(['status'=>'error','message'=>'ID wajib'], 400);
        $stmt = $db->prepare("DELETE FROM penyedia_makanan WHERE id_penyedia = :id");
        $stmt->execute([':id' => $id]);
        jsonResponse(['status'=>'success','message'=>'Data dihapus']);
        break;

    // ------------------------- READ (list) -------------------------
    case 'GET':
        $stmt = $db->query("
            SELECT p.*, k.nama_kategori
            FROM penyedia_makanan p
            JOIN kategori_makanan k ON p.id_kategori = k.id_kategori
            ORDER BY p.id_penyedia DESC
        ");
        jsonResponse(['status'=>'success','data'=>$stmt->fetchAll()]);
        break;

    default:
        jsonResponse(['status'=>'error','message'=>'Method tidak didukung'], 405);
}