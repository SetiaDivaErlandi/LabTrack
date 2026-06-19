<?php
include 'config/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php">LABTRACK</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text text-white me-3 d-none d-sm-inline">
                Sesi Aktif: <strong class="text-warning"><?php echo $_SESSION['username']; ?></strong> 
                (<span class="badge bg-secondary"><?php echo ucfirst($_SESSION['role']); ?></span>)
            </span>
            <a href="logout.php" class="btn btn-sm btn-danger fw-semibold px-3">Keluar</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="p-4 rounded shadow-sm welcome-banner bg-light">
                <h2 class="fw-bold text-dark m-0">Selamat Datang di LabTrack Panel</h2>
                <p class="text-muted m-0 mt-1">Sistem Pemrosesan Data Terdistribusi Terintegrasi Laboratorium.</p>
            </div>
        </div>
    </div>

    <?php 
    if ($_SESSION['role'] == 'mahasiswa') {
        date_default_timezone_set('Asia/Jakarta');
        $id_user_login = $_SESSION['id_user'] ?? 0;

        $cek_terlambat_user = mysqli_query($conn, "SELECT tgl_kembali, jam_kembali, nama_alat 
                                                   FROM view_laporan_peminjaman 
                                                   WHERE id_user = '$id_user_login' AND status = 'dipinjam'");

        $sudah_lewat_tenggat = false;
        $list_alat_terlambat = [];

        if ($cek_terlambat_user && mysqli_num_rows($cek_terlambat_user) > 0) {
            $waktu_sekarang_ts = time();
            while ($item = mysqli_fetch_assoc($cek_terlambat_user)) {
                $jam_format = !empty($item['jam_kembali']) ? $item['jam_kembali'] : '00:00:00';
                $waktu_kembali_ts = strtotime($item['tgl_kembali'] . ' ' . $jam_format);
                
                if ($waktu_sekarang_ts > $waktu_kembali_ts) {
                    $sudah_lewat_tenggat = true;
                    $list_alat_terlambat[] = $item['nama_alat'];
                }
            }
        }

        if ($sudah_lewat_tenggat) :
        ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-danger shadow-sm border-2 rounded-3" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="me-3 fs-3">⚠️</div>
                        <div>
                            <h5 class="alert-heading fw-bold mb-1 text-danger">PERINGATAN: Batas Waktu Pengembalian Habis!</h5>
                            <p class="mb-1 text-dark small">
                                Kamu terdeteksi belum mengembalikan alat lab berikut: 
                                <strong><?php echo implode(', ', array_unique($list_alat_terlambat)); ?></strong>.
                            </p>
                            <hr class="my-2">
                            <p class="mb-0 text-muted extra-small" style="font-size: 0.8rem;">
                                *Harap segera kembalikan alat ke asisten laboratorium untuk menghindari sanksi pembekuan hak pinjam alat praktikum berikutnya.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        endif;
    } 
    ?>
    <?php if ($_SESSION['role'] == 'admin'): ?>
        <h5 class="fw-bold text-dark text-uppercase mb-3 tracking-wide">Akses Kontrol Admin</h5>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card h-100 shadow-sm card-menu">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-primary">Manajemen Inventaris</h5>
                            <p class="card-text text-muted small">Kelola data seluruh alat laboratorium, tambah alat baru, atau ubah jumlah stok fisik.</p>
                        </div>
                        <a href="kelola_alat.php" class="btn btn-outline-primary btn-sm w-100 mt-3 fw-semibold">Buka Kelola Alat</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card h-100 shadow-sm card-menu">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-success">Validasi Peminjaman</h5>
                            <p class="card-text text-muted small">Konfirmasi permintaan peminjaman mahasiswa lewat View & Stored Function database.</p>
                        </div>
                        <a href="kelola.php" class="btn btn-outline-success btn-sm w-100 mt-3 fw-semibold">Lihat Request</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card h-100 shadow-sm card-menu">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-warning text-dark">Manajemen User</h5>
                            <p class="card-text text-muted small">Pantau status akun terdistribusi, lihat list, serta hapus hak akses login mahasiswa.</p>
                        </div>
                        <a href="kelola_user.php" class="btn btn-outline-warning btn-sm text-dark w-100 mt-3 fw-semibold">Kelola Akun</a>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <h5 class="fw-bold text-dark text-uppercase mb-3 tracking-wide">Layanan Mahasiswa</h5>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm card-menu">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-info">Katalog Alat Ready</h5>
                            <p class="card-text text-muted small">Cari alat praktikum yang tersedia lengkap dengan sisa stok aman sebelum melakukan pengajuan.</p>
                        </div>
                        <a href="katalog.php" class="btn btn-info btn-sm text-white fw-semibold w-100 mt-3">Lihat Katalog</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm card-menu">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-primary">Formulir Peminjaman</h5>
                            <p class="card-text text-muted small">Buat pengajuan pinjam alat praktikum baru dengan mengisi jumlah barang serta target pengembalian.</p>
                        </div>
                        <a href="form_pinjam.php" class="btn btn-primary btn-sm fw-semibold w-100 mt-3">Isi Formulir</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm card-menu">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-secondary">Status & Riwayat</h5>
                            <p class="card-text text-muted small">Pantau status validasi pengajuan peminjaman Anda (Menunggu, Dipinjam, Selesai, atau Terlambat).</p>
                        </div>
                        <a href="riwayat.php" class="btn btn-secondary btn-sm fw-semibold w-100 mt-3">Cek Riwayat</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm card-menu">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-bold text-dark">Kelola Akun Saya</h5>
                            <p class="card-text text-muted small">Nonaktifkan atau aktifkan kembali akun Anda sendiri.</p>
                        </div>
                        <a href="user_toggle_status.php" class="btn btn-outline-dark btn-sm fw-semibold w-100 mt-3">Kelola Akun</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>