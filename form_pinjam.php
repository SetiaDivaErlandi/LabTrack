<?php
session_start();

// Mengatur zona waktu ke Waktu Indonesia Barat (WIB) agar jam sesuai dengan laptop
date_default_timezone_set('Asia/Jakarta'); 

include 'config/db.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

if (isset($_POST['ajukan_peminjaman'])) {
    $id_alat      = mysqli_real_escape_string($conn, $_POST['id_alat']);
    $jumlah       = mysqli_real_escape_string($conn, $_POST['jumlah']);
    $tgl_pinjam   = date('Y-m-d');
    $jam_pinjam   = date('H:i:s'); // Jam akan otomatis mengambil waktu WIB saat ini
    $tgl_kembali  = mysqli_real_escape_string($conn, $_POST['tgl_kembali']);
    $jam_kembali  = mysqli_real_escape_string($conn, $_POST['jam_kembali']);
    $datetime_pinjam   = strtotime($tgl_pinjam . ' ' . $jam_pinjam);
    $datetime_kembali  = strtotime($tgl_kembali . ' ' . $jam_kembali);
    
    if ($datetime_kembali < $datetime_pinjam) {
        echo "<script>alert('TANGGAL & JAM TIDAK VALID!\\n\\nAnda meminjam pada: " . date('d/m/Y H:i', $datetime_pinjam) . "\\n\\nTanggal pengembalian harus sama atau SETELAH waktu peminjaman.\\n\\nSilakan isi ulang data dengan benar.');</script>";
    } else {
        mysqli_begin_transaction($conn);

        try {
            $cek_stok = mysqli_query($conn, "SELECT stok FROM inventaris WHERE id_alat = '$id_alat' FOR UPDATE");
            if (!$cek_stok) {
                throw new Exception("Error cek stok: " . mysqli_error($conn));
            }
            
            $data_stok = mysqli_fetch_assoc($cek_stok);

            if ($jumlah < 1 || !is_numeric($jumlah)) {
                throw new Exception("Jumlah peminjaman harus minimal 1 unit!");
            }

            if ($data_stok['stok'] < $jumlah) {
                echo "<script>alert('Stok alat tidak mencukupi! Tersedia: " . $data_stok['stok'] . " pcs, Diminta: " . $jumlah . " pcs');</script>";
            } else {
                // Query INSERT dengan menyertakan kolom jam_pinjam dan variabel $jam_pinjam
                $query = "INSERT INTO peminjaman (id_user, id_alat, jumlah, tgl_pinjam, jam_pinjam, jam_kembali, tgl_kembali, status) 
                          VALUES ('$id_user', '$id_alat', '$jumlah', '$tgl_pinjam', '$jam_pinjam', '$jam_kembali', '$tgl_kembali', 'menunggu')";
                
                if (mysqli_query($conn, $query)) {
                    mysqli_commit($conn);
                    echo "<script>alert('Peminjaman berhasil diajukan! Menunggu persetujuan admin.'); window.location='riwayat.php';</script>";
                } else {
                    throw new Exception("Gagal query INSERT: " . mysqli_error($conn));
                }
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            echo "<script>alert('Terjadi kesalahan: " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Peminjaman - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php">LABTRACK</a>
        <a href="dashboard.php" class="btn btn-sm btn-outline-light">Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container my-5">
    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
        <h4 class="fw-bold text-dark mb-2">Formulir Peminjaman</h4>
        <p class="text-muted small mb-4">Silakan masukkan jenis alat lab dan tanggal pengembalian dengan benar.</p>

        <form action="" method="POST">
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Pilih Alat Praktikum</label>
                <select name="id_alat" id="id_alat" class="form-select form-select-lg" required>
                    <option value="">-- Pilih Alat --</option>
                    <?php
                    $ambil_alat = mysqli_query($conn, "SELECT * FROM inventaris WHERE stok > 0");
                    if (!$ambil_alat) {
                        echo "<option value=''>Error memuat data: " . mysqli_error($conn) . "</option>";
                    } elseif (mysqli_num_rows($ambil_alat) === 0) {
                        echo "<option value=''>Tidak ada alat tersedia</option>";
                    } else {
                        while($alat = mysqli_fetch_assoc($ambil_alat)) {
                            echo "<option value='".$alat['id_alat']."' data-stok='".$alat['stok']."'>".$alat['nama_alat']." (Stok: ".$alat['stok'].")</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Jumlah yang Dipinjam</label>
                <input type="number" name="jumlah" id="jumlah" class="form-control form-control-lg" placeholder="Contoh: 2" min="1" required>
                <small class="text-muted d-block mt-2" id="maxStok">Pilih alat terlebih dahulu</small>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Tanggal Pengembalian</label>
                <input type="date" name="tgl_kembali" id="tgl_kembali" class="form-control form-control-lg" required>
                <small class="text-muted d-block mt-2">Tanggal pengembalian minimal harus hari ini atau hari berikutnya</small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Jam Batas Pengembalian</label>
                <input type="time" name="jam_kembali" id="jam_kembali" class="form-control form-control-lg" required>
                <small class="text-muted d-block mt-2">Jika pengembalian hari ini, jam harus sama atau lebih besar dari jam sekarang</small>
            </div>

            <button type="submit" name="ajukan_peminjaman" class="btn btn-primary btn-lg w-100 fw-bold">
                Kirim Formulir Pengajuan
            </button>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const selectAlat = document.getElementById('id_alat');
    const inputJumlah = document.getElementById('jumlah');
    const textMaxStok = document.getElementById('maxStok');

    selectAlat.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stok = selectedOption.getAttribute('data-stok');

        if (stok) {
            inputJumlah.setAttribute('max', stok);
            inputJumlah.value = ''; 
            textMaxStok.textContent = 'Maksimal dapat dipinjam: ' + stok + ' pcs';
        } else {
            inputJumlah.removeAttribute('max');
            inputJumlah.value = '';
            textMaxStok.textContent = 'Pilih alat terlebih dahulu';
        }
    });

    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tgl_kembali').setAttribute('min', today);

    const now = new Date();
    const currentTime = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    document.getElementById('jam_kembali').value = currentTime;
    
    document.querySelector('form').addEventListener('submit', function(e) {
        const idAlat = document.getElementById('id_alat').value;
        const jumlah = parseInt(inputJumlah.value);
        const tglKembali = document.getElementById('tgl_kembali').value;
        const jamKembali = document.getElementById('jam_kembali').value;
        const selectedOption = selectAlat.options[selectAlat.selectedIndex];
        const maxStok = parseInt(selectedOption.getAttribute('data-stok'));

        if (!idAlat) {
            e.preventDefault();
            alert('Silakan pilih alat terlebih dahulu!');
            return false;
        }

        if (!jumlah || jumlah < 1) {
            e.preventDefault();
            alert('Silakan isi jumlah peminjaman (minimal 1)!');
            return false;
        }

        if (jumlah > maxStok) {
            e.preventDefault();
            alert('Jumlah peminjaman tidak boleh lebih dari stok tersedia! Maksimal: ' + maxStok + ' pcs');
            return false;
        }

        if (!tglKembali || !jamKembali) {
            e.preventDefault();
            alert('Silakan isi tanggal dan jam pengembalian!');
            return false;
        }

        if (tglKembali < today) {
            e.preventDefault();
            alert('Tanggal pengembalian tidak boleh di hari sebelumnya!');
            return false;
        }

        if (tglKembali === today && jamKembali < currentTime) {
            e.preventDefault();
            alert('Jika pengembalian hari ini, jam harus sama atau LEBIH BESAR dari jam sekarang (' + currentTime + ')');
            return false;
        }

        return true;
    });
</script>
</body>
</html>