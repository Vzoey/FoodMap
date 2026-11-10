<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <!-- Meta viewport ini WAJIB untuk responsif di HP -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>FoodMap GIS — Matcha & Burgundy Theme</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="app-header">
    <h1>🍜 FoodMap GIS</h1>
    <span class="subtitle">Sistem Informasi Geografis Pencarian Penyedia Makanan</span>
</header>

<main class="app-main">
    <aside class="panel-cari">
        <div class="filter-box">
            <input type="text" id="inputKeyword" placeholder="Cari nama / alamat...">
            <select id="selectKategori">
                <option value="">Semua Kategori</option>
                <option>Restoran</option>
                <option>Kafe</option>
                <option>Warung Makan</option>
                <option>Fast Food</option>
                <option>Kuliner Khas</option>
            </select>
            <div class="harga-row">
                <input type="number" id="inputHargaMin" placeholder="Harga min">
                <span>—</span>
                <input type="number" id="inputHargaMax" placeholder="Harga max">
            </div>
            <div style="display:flex; gap:8px;">
                <button id="btnCari" style="flex:2;">🔍 Cari</button>
                <button id="btnReset" class="btn-secondary" style="flex:1;">Reset</button>
            </div>
        </div>

        <div id="infoUser" class="info-user">📍 Mendeteksi lokasi Anda...</div>

        <div id="hasilList" class="hasil-list">
            <!-- hasil pencarian muncul di sini -->
        </div>
    </aside>

    <section class="panel-peta">
        <div id="map"></div>
        
        <div id="detailPanel" class="detail-panel hidden">
            <button id="btnCloseDetail" class="btn-close">✕</button>
            <div id="detailContent"></div>
        </div>
    </section>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>