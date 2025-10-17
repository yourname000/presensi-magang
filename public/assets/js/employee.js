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
    FUNGSI CEK & AMBIL LOKASI
========================== */
// ✅ Tambahkan parameter `status` agar bisa tahu presensi Masuk / Pulang
function presensi_location(status) {
    alert("📍 Memeriksa lokasi Anda...");

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                let lat = position.coords.latitude;
                let long = position.coords.longitude;

                // Masukkan nilai koordinat ke form
                document.getElementById("inputLatitude").value = lat;
                document.getElementById("inputLongitude").value = long;

                // ✅ Tambahkan logika untuk isi status otomatis
                const statusInput = document.getElementById("inputStatus");
                if (statusInput) {
                    statusInput.value = status; // "Masuk" atau "Pulang"
                }

                alert("✅ Lokasi ditemukan. Silakan lanjutkan presensi.");

                // Tampilkan modal form presensi
                const modalElement = document.getElementById("presensiModal");
                const modal = new bootstrap.Modal(modalElement, { backdrop: false });
                modal.show();

                $(".modal-backdrop").remove();
                $("body").removeClass("modal-open");
            },
            function (error) {
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
    } else {
        alert("Browser tidak mendukung geolocation.");
    }
}

/* ==========================
    KONFIRMASI DAN KIRIM FORM
========================== */
function handleKonfirmasi() {
    const lat = document.getElementById("inputLatitude").value;
    const lng = document.getElementById("inputLongitude").value;
    const status = document.getElementById("inputStatus").value;

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
