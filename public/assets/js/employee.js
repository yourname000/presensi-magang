// ==============================
//  GLOBAL STATE
// ==============================
let isScanIn = false;

/* ==========================
    Clock and Date
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
    LOKASI PRESENSI
========================== */
function presensi_location(title) {
    alert("📍 Memeriksa lokasi...");

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                let lat = position.coords.latitude;
                let long = position.coords.longitude;

                // Set hidden input
                document.getElementById("inputLatitude").value = lat;
                document.getElementById("inputLongitude").value = long;

                alert("✅ Lokasi ditemukan. Silakan lanjutkan presensi.");

                // Tampilkan modal
                const modalElement = document.getElementById("presensiModal");
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: false
                });
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
    KONFIRMASI PRESENSI
========================== */
function handleKonfirmasi() {
    const lat = document.getElementById("inputLatitude").value;
    const lng = document.getElementById("inputLongitude").value;

    if (!lat || !lng) {
        alert("📍 Data lokasi belum tersedia. Klik tombol presensi ulang.");
        return;
    }

    const shiftSelect = document.getElementById("select_id_shift");
    if (shiftSelect && !shiftSelect.value) {
        alert("⚠️ Pilih shift terlebih dahulu.");
        return;
    }

    alert("⏳ Mengirim data presensi...");

    // Submit langsung ke form
    document.getElementById("form_presensi").submit();
}
