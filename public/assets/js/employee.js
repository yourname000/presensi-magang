let isScanIn = false;

/* ==========================
    FUNGSI UPDATE JAM & TANGGAL
   ========================== */
function updateClock() {
    const now = new Date();
    let h = String(now.getHours()).padStart(2, "0");
    let m = String(now.getMinutes()).padStart(2, "0");
    let s = String(now.getSeconds()).padStart(2, "0");

    const timeEl = document.getElementById("time");
    if (timeEl) timeEl.textContent = `${h}:${m}:${s}`;

    const days = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
    const months = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    const dayName = days[now.getDay()];
    const day = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();

    const dateEl = document.getElementById("date");
    if (dateEl) dateEl.textContent = `${dayName}, ${day} ${month} ${year}`;
}
setInterval(updateClock, 1000);
updateClock();

/* ==========================
    HELPER: HAVERSINE (meter)
   ========================== */
function haversineDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // meters
    const toRad = deg => deg * Math.PI / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

/* ==========================
    FUNGSI CEK & AMBIL LOKASI + CEK RADIUS
   ========================== */
function presensi_location(status) {
    alert("📍 Memeriksa lokasi Anda...");

    // ambil data office dari DOM (Blade menaruhnya)
    const elLat = document.getElementById("officeLat");
    const elLng = document.getElementById("officeLng");
    const elRadius = document.getElementById("officeRadius");

    // validasi objek office ada dan datanya valid
    if (!elLat || !elLng || !elRadius) {
        alert("⚠️ Data lokasi kantor tidak tersedia. Hubungi admin.");
        return;
    }

    const officeLatVal = elLat.dataset.value;
    const officeLngVal = elLng.dataset.value;
    const officeRadiusVal = elRadius.dataset.value;

    if (!officeLatVal || !officeLngVal || !officeRadiusVal) {
        alert("⚠️ Koordinat kantor belum diset. Hubungi admin.");
        return;
    }

    const officeLat = parseFloat(officeLatVal);
    const officeLng = parseFloat(officeLngVal);
    const officeRadius = parseFloat(officeRadiusVal); // meter

    if (!navigator.geolocation) {
        alert("Browser tidak mendukung geolocation.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // hitung jarak
            const distance = haversineDistance(officeLat, officeLng, lat, lng);

            // jika di luar radius -> langsung beri pesan, jangan buka modal
            if (isNaN(distance) || distance > officeRadius) {
                alert(`❌ Anda berada di Luar area kantor!\n\nJarak Anda: ${isNaN(distance) ? '—' : distance.toFixed(2)} meter\nRadius diizinkan: ${officeRadius} meter`);
                return;
            }

            // di dalam radius -> simpan koordinat ke input sesuai status
            if (status === "Masuk") {
                const inLat = document.getElementById("inputLatitude");
                const inLng = document.getElementById("inputLongitude");
                if (inLat) inLat.value = lat;
                if (inLng) inLng.value = lng;
            } else if (status === "Pulang") {
                const outLat = document.getElementById("inputLatitudeOut");
                const outLng = document.getElementById("inputLongitudeOut");
                if (outLat) outLat.value = lat;
                if (outLng) outLng.value = lng;
            }

            const statusInput = document.getElementById("inputStatus");
            if (statusInput) statusInput.value = status;

            // tampilkan modal verifikasi dan alert internal
            const lokasiAlert = document.getElementById("lokasiAlert");
            if (lokasiAlert) lokasiAlert.style.display = "block";

            alert("✅ Lokasi ditemukan dan berada dalam radius kantor. Silakan lanjutkan presensi.");

            const modalElement = document.getElementById("presensiModal");
            const modal = new bootstrap.Modal(modalElement, { backdrop: 'static' });
            modal.show();

            // hapus double-backdrop jika ada (jQuery)
            if (window.jQuery) {
                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
            }
        },
        function(error) {
            let message = "";
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    message = "Akses lokasi ditolak. Aktifkan izin lokasi di browser.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = "Informasi lokasi tidak tersedia.";
                    break;
                case error.TIMEOUT:
                    message = "Pengambilan lokasi terlalu lama.";
                    break;
                default:
                    message = "Terjadi kesalahan saat mengambil lokasi.";
            }
            alert("⚠️ " + message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

/* ==========================
    KONFIRMASI DAN KIRIM FORM
   ========================== */
function handleKonfirmasi() {
    const status = document.getElementById("inputStatus") ? document.getElementById("inputStatus").value : '';

    // Ambil koordinat sesuai status
    let lat = '';
    let lng = '';
    if (status === "Masuk") {
        const inLat = document.getElementById("inputLatitude");
        const inLng = document.getElementById("inputLongitude");
        lat = inLat ? inLat.value : '';
        lng = inLng ? inLng.value : '';
    } else if (status === "Pulang") {
        const outLat = document.getElementById("inputLatitudeOut");
        const outLng = document.getElementById("inputLongitudeOut");
        lat = outLat ? outLat.value : '';
        lng = outLng ? outLng.value : '';
    }

    // Validasi
    if (!lat || !lng) {
        alert("📍 Data lokasi belum tersedia. Klik tombol presensi ulang.");
        return;
    }

    if (!status) {
        alert("⚠️ Status presensi tidak boleh kosong. Klik tombol presensi ulang.");
        return;
    }

    const shiftSelect = document.getElementById("select_id_shift");
    if (shiftSelect && !shiftSelect.value) {
        alert("⚠️ Pilih shift terlebih dahulu sebelum absen.");
        return;
    }

    if (confirm(`Yakin ingin mengirim data presensi ${status}?`)) {
        document.getElementById("form_presensi").submit();
    }
}

/* ==========================
    FUNGSI TAMPILKAN MODAL PROFIL
   ========================== */
function set_modal_profile() {
    if (window.jQuery) {
        $(".modal-backdrop").remove();
        $("body").removeClass("modal-open");
    }
    const modal = new bootstrap.Modal(document.getElementById('kt_modal_profile'));
    modal.show();
}
