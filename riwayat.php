<?php
include 'config/db.php';
session_start();

// Set timezone agar sinkron dengan input mahasiswa
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil riwayat peminjaman user ini
$query_riwayat = "SELECT p.id_pinjam, p.id_user, p.id_alat, p.jumlah, p.tgl_pinjam, p.jam_kembali, p.tgl_kembali, p.status, i.nama_alat FROM peminjaman p 
                  JOIN inventaris i ON p.id_alat = i.id_alat 
                  WHERE p.id_user = '$id_user' 
                  ORDER BY p.id_pinjam DESC";
$riwayat = mysqli_query($conn, $query_riwayat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman Saya - LabTrack</title>
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

    <?php 
    // Ambil ulang data khusus status dipinjam untuk mendeteksi keterlambatan di atas tabel
    $cek_terlambat_riwayat = mysqli_query($conn, "SELECT tgl_kembali, jam_kembali, nama_alat 
                                               FROM peminjaman 
                                               JOIN inventaris ON peminjaman.id_alat = inventaris.id_alat
                                               WHERE id_user = '$id_user' AND status = 'dipinjam'");

    $sudah_lewat_tenggat = false;
    $list_alat_terlambat = [];

    if ($cek_terlambat_riwayat && mysqli_num_rows($cek_terlambat_riwayat) > 0) {
        $waktu_sekarang_ts = time();
        while ($item = mysqli_fetch_assoc($cek_terlambat_riwayat)) {
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
    <div class="alert alert-danger shadow-sm border-2 rounded-3 mb-4" role="alert">
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
    <?php endif; ?>
    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
        <h4 class="fw-bold text-dark mb-2">Riwayat Pengajuan Peminjaman</h4>
        <p class="text-muted small mb-4">Pantau status persetujuan dari admin laboratorium secara berkala di bawah ini.</p>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-secondary small fw-bold text-uppercase">
                    <tr>
                        <th>No</th>
                        <th>Alat yang Diajukan</th>
                        <th>Jumlah</th>
                        <th>Tanggal Pinjam</th>
                        <th>Batas Pengembalian</th>
                        <th>Status Validasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($riwayat) > 0): ?>
                        <?php $no = 1; while($row = mysqli_fetch_assoc($riwayat)): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td class="text-start fw-bold text-secondary"><?php echo $row['nama_alat']; ?></td>
                            <td><?php echo $row['jumlah']; ?> Pcs</td>
                            <td><?php echo date('d-m-Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($row['tgl_kembali'])) . ' ' . date('H:i', strtotime($row['jam_kembali'] ?? '00:00')); ?></td>
                            <td>
                                <?php 
                                $waktu_sekarang = time();
                                $string_waktu_kembali = $row['tgl_kembali'] . ' ' . ($row['jam_kembali'] ?? '00:00:00');
                                $waktu_kembali = strtotime($string_waktu_kembali);

                                // Tentukan status yang akan ditampilkan
                                $status_tampil = $row['status'];
                                if ($row['status'] == 'dipinjam' && $waktu_sekarang > $waktu_kembali) {
                                    $status_tampil = 'terlambat_dihitung';
                                }

                                if ($status_tampil == 'terlambat_dihitung') {
                                    echo '<span class="badge bg-danger py-2 px-3 fw-semibold">Terlambat</span>';
                                } elseif($status_tampil == 'menunggu') {
                                    echo '<span class="badge bg-warning text-dark py-2 px-3 fw-semibold">Menunggu Admin</span>';
                                } elseif($status_tampil == 'dipinjam') {
                                    echo '<span class="badge bg-primary py-2 px-3 fw-semibold">Sedang Dipinjam</span>';
                                } elseif($status_tampil == 'kembali') {
                                    echo '<span class="badge bg-success py-2 px-3 fw-semibold">Sudah Kembali</span>';
                                } else {
                                    echo '<span class="badge bg-secondary py-2 px-3 fw-semibold">Status Tidak Dikenal</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Kamu belum pernah membuat pengajuan peminjaman alat apa pun.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>