// Variable global untuk menyimpan status presensi saat modal dibuka
let isScanIn = false;

/* ==========================
    Clock and Date
========================== */
function updateClock() {
    const now = new Date();
    let h = String(now.getHours()).padStart(2, '0');
    let m = String(now.getMinutes()).padStart(2, '0');
    let s = String(now.getSeconds()).padStart(2, '0');
    const timeEl = document.getElementById('time');
    if (timeEl) timeEl.textContent = `${h}:${m}:${s}`;

    const days = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
    const months = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    let dayName = days[now.getDay()];
    let day = now.getDate();
    let month = months[now.getMonth()];
    let year = now.getFullYear();
    const dateEl = document.getElementById('date');
    if (dateEl) dateEl.textContent = `${dayName}, ${day} ${month} ${year}`;
}
setInterval(updateClock, 1000);
updateClock();

/* ==========================
    Bootstrap Alert Helper
========================== */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show mt-2`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alert);

    setTimeout(() => {
        const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
        bsAlert.close();
    }, 4000);
}

/* ==========================
    Lokasi Presensi
========================== */
function presensi_location(title) {
    showAlert("info", `<i class="fa fa-map-marker-alt text-danger"></i> Memeriksa lokasi...`);

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function (position) {
            let lat = position.coords.latitude;
            let long = position.coords.longitude;
            document.getElementById('inputLatitude').value = lat;
            document.getElementById('inputLongitude').value = long;

            const csrf_token = $('input[name="_token"]').val();

            $.ajax({
                url: "/absen-location",
                type: "POST",
                data: { _token: csrf_token, latitude: lat, longitude: long },
                success: function (data) {
                    if (data.status === true) {
                        const presensiModal = new bootstrap.Modal(document.getElementById('presensiModal'));
                        presensiModal.show();
                        showAlert("success", "Lokasi valid, silakan lanjutkan absensi.");
                    } else {
                        document.getElementById('inputLatitude').value = '';
                        document.getElementById('inputLongitude').value = '';
                        showAlert("danger", data.message || "Lokasi tidak sesuai area presensi!");
                    }
                },
                error: function () {
                    showAlert("danger", "Gagal mengirim data lokasi ke server.");
                }
            });
        }, function (error) {
            let message = '';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Akses lokasi ditolak. Aktifkan izin lokasi di browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    message = 'Pengambilan lokasi terlalu lama.';
                    break;
                default:
                    message = 'Terjadi kesalahan saat mengambil lokasi.';
            }
            showAlert("danger", message);
        }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
    } else {
        showAlert("danger", "Browser tidak mendukung geolocation.");
    }
}

/* ==========================
    Konfirmasi dan AJAX Submit
========================== */
async function handleKonfirmasi() {
    const hiddenSelfie = document.getElementById('uploadSelfie');
    const selfie = hiddenSelfie.dataset.image;
    const lat = document.getElementById('inputLatitude').value;
    const lng = document.getElementById('inputLongitude').value;

    if (!selfie) return showAlert("warning", "Ambil selfie terlebih dahulu.");
    if (!lat || !lng) return showAlert("danger", "Data lokasi tidak ditemukan.");

    let shift = null, shiftText = null;
    if (!isScanIn) {
        const shiftSelect = document.getElementById('select_id_shift');
        shift = shiftSelect.value;
        shiftText = shiftSelect.options[shiftSelect.selectedIndex]?.text || '-';
        if (!shift) return showAlert("warning", "Pilih shift terlebih dahulu.");
    } else {
        shift = document.querySelector('input[name="id_shift"]').value;
        shiftText = `ID: ${shift} (Pulang)`;
    }

    showAlert("info", "Mengirim data absensi...");

    const csrf_token = $('input[name="_token"]').val();
    const formData = {
        _token: csrf_token,
        id_shift: shift,
        selfie: selfie,
        latitude: lat,
        longitude: lng
    };

    $.ajax({
        url: $('#form_presensi').attr('action'),
        type: 'POST',
        data: formData,
        success: function (res) {
            if (res.status) {
                showAlert("success", "Presensi berhasil disimpan!");
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert("danger", res.message || "Presensi gagal.");
            }
        },
        error: function () {
            showAlert("danger", "Gagal mengirim data ke server.");
        }
    });
}
