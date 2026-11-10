// =====================================================================
// app.js — Logika Peta Leaflet + OpenStreetMap untuk FoodMap GIS
// =====================================================================

let map;                 // objek Leaflet Map
let markerLayer;         // LayerGroup untuk semua marker penyedia
let userMarker = null;   // marker posisi user
let userPos = null;      // {lat, lng} lokasi user
let routingControl = null; // kontrol rute dari Leaflet Routing Machine
let allData = [];        // cache data hasil fetch

// =====================================================================
// 1. INISIALISASI PETA (dipanggil saat DOM ready)
// =====================================================================
function initMap() {
    // Buat peta Leaflet, center Bandung
    map = L.map('map', {
        zoomControl: true,
        attributionControl: true,
    }).setView([-6.9175, 107.6191], 13);

    // ============ BASEMAP: OpenStreetMap standard tiles ============
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution:
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Layer group untuk semua marker penyedia (mudah dibersihkan)
    markerLayer = L.layerGroup().addTo(map);

    // Deteksi lokasi user
    deteksiLokasiUser();

    // Muat data awal
    muatData();

    // ============ EVENT HANDLER FILTER ============
    document.getElementById('btnCari').addEventListener('click', muatData);
    document.getElementById('btnReset').addEventListener('click', () => {
        document.getElementById('inputKeyword').value = '';
        document.getElementById('selectKategori').value = '';
        document.getElementById('inputHargaMin').value = '';
        document.getElementById('inputHargaMax').value = '';
        muatData();
    });
    document.getElementById('btnCloseDetail').addEventListener('click', () => {
        document.getElementById('detailPanel').classList.add('hidden');
    });
}

// =====================================================================
// 2. DETEKSI LOKASI USER (Geolocation API browser — sama seperti versi Google Maps)
// =====================================================================
function deteksiLokasiUser() {
    const infoEl = document.getElementById('infoUser');

    if (!navigator.geolocation) {
        infoEl.textContent = '⚠️ Browser tidak mendukung geolokasi.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            userPos = {
                lat: pos.coords.latitude,
                lng: pos.coords.longitude,
            };

            // ============ MARKER BIRU untuk user ============
            userMarker = L.marker([userPos.lat, userPos.lng], {
                icon: L.divIcon({
                    className: 'user-marker',
                    html: '<div class="user-dot"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                }),
                zIndexOffset: 1000,
            })
            .addTo(map)
            .bindPopup('<b>📍 Lokasi Anda</b><br>' +
                       userPos.lat.toFixed(5) + ', ' + userPos.lng.toFixed(5));

            infoEl.innerHTML = `📍 Lokasi Anda: 
                <b>${userPos.lat.toFixed(5)}, ${userPos.lng.toFixed(5)}</b>`;
            map.setView([userPos.lat, userPos.lng], 15);
        },
        (err) => {
            infoEl.textContent = '⚠️ Gagal mendeteksi lokasi: ' + err.message;
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// =====================================================================
// 3. MUAT DATA DARI BACKEND (fetch API + filter — SAMA PERSIS)
// =====================================================================
async function muatData() {
    const q        = document.getElementById('inputKeyword').value.trim();
    const kategori = document.getElementById('selectKategori').value;
    const hmin     = document.getElementById('inputHargaMin').value;
    const hmax     = document.getElementById('inputHargaMax').value;

    const params = new URLSearchParams();
    if (q)        params.append('q', q);
    if (kategori) params.append('kategori', kategori);
    if (hmin)     params.append('harga_min', hmin);
    if (hmax)     params.append('harga_max', hmax);

    try {
        const res  = await fetch('api/get_penyedia.php?' + params.toString());
        const json = await res.json();
        if (json.status !== 'success') throw new Error(json.message || 'Gagal memuat');

        allData = json.data;
        renderMarkers(allData);
        renderList(allData);
    } catch (err) {
        console.error(err);
        alert('Gagal memuat data: ' + err.message);
    }
}

// =====================================================================
// 4. RENDER MARKER DI PETA (Leaflet API)
// =====================================================================
function renderMarkers(data) {
    // Bersihkan marker lama
    markerLayer.clearLayers();

    if (data.length === 0) return;

    const latLngs = [];

    data.forEach(item => {
        const lat = parseFloat(item.latitude);
        const lng = parseFloat(item.longitude);
        latLngs.push([lat, lng]);

        // Marker default Leaflet (icon biru)
        const marker = L.marker([lat, lng], {
            title: item.nama_penyedia,
            riseOnHover: true,
        });

        // Bind popup ringkas (klik → detail lengkap di panel kanan)
        marker.bindPopup(`
            <b>${item.nama_penyedia}</b><br>
            <small>${item.nama_kategori}</small><br>
            <a href="#" onclick="tampilkanDetail(${item.id_penyedia}); return false;">
                Lihat detail →
            </a>
        `);

        // Klik marker = tampilkan detail lengkap
        marker.on('click', () => tampilkanDetail(item.id_penyedia));

        markerLayer.addLayer(marker);
    });

    // Auto-zoom ke semua marker
    if (latLngs.length === 1) {
        map.setView(latLngs[0], 16);
    } else {
        map.fitBounds(L.latLngBounds(latLngs), { padding: [30, 30] });
    }
}

// =====================================================================
// 5. RENDER DAFTAR HASIL DI PANEL KIRI (SAMA PERSIS)
// =====================================================================
function renderList(data) {
    const listEl = document.getElementById('hasilList');

    if (data.length === 0) {
        listEl.innerHTML = '<p style="text-align:center;color:#888;padding:20px;">Tidak ada hasil.</p>';
        return;
    }

    listEl.innerHTML = data.map(item => `
        <div class="card-hasil" onclick="tampilkanDetail(${item.id_penyedia})">
            <span class="badge">${item.nama_kategori}</span>
            <h4>${item.nama_penyedia}</h4>
            <div class="meta">📍 ${item.alamat || '-'}</div>
            <div class="meta">🕒 ${item.jam_buka?.slice(0,5)} - ${item.jam_tutup?.slice(0,5)}</div>
            <div class="harga">Mulai Rp ${formatRupiah(item.harga_min)}</div>
        </div>
    `).join('');
}

// =====================================================================
// 6. TAMPILKAN DETAIL PENYEDIA
// =====================================================================
async function tampilkanDetail(id) {
    try {
        const res  = await fetch('api/get_detail.php?id=' + id);
        const json = await res.json();
        if (json.status !== 'success') throw new Error('Detail tidak ditemukan');

        const p = json.penyedia;
        const menu = json.menu;

        // Pindahkan peta ke lokasi penyedia
        const lat = parseFloat(p.latitude);
        const lng = parseFloat(p.longitude);
        map.flyTo([lat, lng], 16, { duration: 1 });

        // Bangun konten detail
        const menuHtml = menu.map(m => `
            <div class="menu-item">
                <span>${m.nama_menu}</span>
                <span class="harga">Rp ${formatRupiah(m.harga)}</span>
            </div>
        `).join('');

        // ============ URL RUTE: pakai Google Maps URL (gratis, tanpa API) ============
        // Alternatif: buka rute di Leaflet Routing Machine (lihat tombol "Rute di Peta")
        const ruteUrlGMaps =
            `https://www.google.com/maps/dir/?api=1` +
            `&origin=${userPos ? userPos.lat + ',' + userPos.lng : ''}` +
            `&destination=${lat},${lng}` +
            `&travelmode=driving`;

        document.getElementById('detailContent').innerHTML = `
            <h2>${p.nama_penyedia}</h2>
            <span class="kategori-badge">${p.nama_kategori}</span>
            <p>📍 <b>Alamat:</b> ${p.alamat}</p>
            <p>🕒 <b>Jam Buka:</b> ${p.jam_buka?.slice(0,5)} - ${p.jam_tutup?.slice(0,5)}</p>
            <p>📞 <b>Telepon:</b> ${p.no_telepon || '-'}</p>
            <p>🧭 <b>Koordinat:</b> ${p.latitude}, ${p.longitude}</p>

            <div class="menu-list">
                <h3 style="font-size:.95rem;margin-bottom:8px;">🍽️ Menu</h3>
                ${menuHtml}
            </div>

            <!-- Tombol 1: Rute di peta Leaflet (OSRM) -->
            <button class="btn-rute" onclick="hitungRute(${lat}, ${lng})">
                🗺️ Tampilkan Rute di Peta
            </button>

            <!-- Tombol 2: Buka Google Maps (fallback) -->
            <a href="${ruteUrlGMaps}" target="_blank" style="text-decoration:none;">
                <button class="btn-rute btn-rute-alt">🚗 Buka di Google Maps</button>
            </a>
        `;
        document.getElementById('detailPanel').classList.remove('hidden');
    } catch (err) {
        alert('Gagal memuat detail: ' + err.message);
    }
}

// =====================================================================
// 7. HITUNG RUTE DI PETA via OSRM (Leaflet Routing Machine)
//    OSRM = Open Source Routing Machine — gratis, tanpa API key
// =====================================================================
function hitungRute(destLat, destLng) {
    if (!userPos) {
        alert('Aktifkan izin lokasi terlebih dahulu untuk menghitung rute dari posisi Anda.');
        return;
    }

    // Hapus kontrol rute sebelumnya
    if (routingControl) {
        map.removeControl(routingControl);
    }

    // Buat kontrol rute baru
    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(userPos.lat, userPos.lng), // titik awal
            L.latLng(destLat, destLng),         // tujuan
        ],
        router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1', // server OSRM publik
        }),
        lineOptions: {
            styles: [{ color: '#1971c2', weight: 5, opacity: 0.8 }],
        },
        show: false,                // sembunyikan panel instruksi turn-by-turn
        addWaypoints: false,
        routeWhileDragging: false,
        fitSelectedRoutes: true,
        createMarker: function() { return null; }, // jangan buat marker baru
    }).addTo(map);

    // Event saat rute berhasil dihitung → tampilkan jarak & waktu
    routingControl.on('routesfound', function(e) {
        const route = e.routes[0];
        const jarakKm = (route.summary.totalDistance / 1000).toFixed(2);
        const waktuMenit = Math.ceil(route.summary.totalTime / 60);

        // Sisipkan info ke panel detail
        const info = document.createElement('div');
        info.className = 'rute-info';
        info.innerHTML = `
            <b>✅ Rute ditemukan</b><br>
            📏 Jarak: <b>${jarakKm} km</b><br>
            ⏱️ Estimasi: <b>${waktuMenit} menit</b>
        `;
        // Hapus info lama kalau ada
        const old = document.querySelector('.rute-info');
        if (old) old.remove();
        document.getElementById('detailContent').appendChild(info);
    });

    routingControl.on('routingerror', function(e) {
        alert('Gagal menghitung rute. Pastikan koneksi internet aktif.');
        console.error(e);
    });
}

// =====================================================================
// 8. UTILITY
// =====================================================================
function formatRupiah(num) {
    if (num == null) return '-';
    return parseInt(num).toLocaleString('id-ID');
}

// =====================================================================
// 9. JALANKAN SETELAH DOM SIAP
// =====================================================================
document.addEventListener('DOMContentLoaded', initMap);