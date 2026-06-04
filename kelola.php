<?php
include 'config/db.php';
session_start();

// Set timezone agar fungsi time() dan strtotime() akurat dengan waktu lokal (WIB)
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman: Hanya Admin yang boleh masuk
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// ============================================================================================
// IMPLEMENTASI MATERI CONCURRENCY CONTROL & PENCEGAHAN DEADLOCK (MODUL 4)
// Menggunakan Metode Pessimistic Locking (FOR UPDATE) & Database Transaction
// ============================================================================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];

    if ($action == 'setujui') {
        // 1. Memulai Transaksi Database (Menerapkan prinsip Atomicity & Isolation)
        mysqli_begin_transaction($conn);

        try {
            // 2. KUNCI BARIS DATA (Row-Level Lock) dengan FOR UPDATE untuk mencegah Deadlock / Race Condition
            $check_lock = mysqli_query($conn, "SELECT status FROM peminjaman WHERE id_pinjam = '$id_pinjam' FOR UPDATE");
            $status_skrg = mysqli_fetch_assoc($check_lock);

            // 3. Validasi kondisi data sebelum melakukan manipulasi
            if ($status_skrg['status'] === 'menunggu') {
                // Hanya update status - TRIGGER database akan otomatis kurangi stok secara aman
                $query = "UPDATE peminjaman SET status = 'dipinjam' WHERE id_pinjam = '$id_pinjam'";
                mysqli_query($conn, $query);
                
                // Jika sukses, kunci dilepaskan dan data disimpan permanen
                mysqli_commit($conn);
                $success = "✓ Pengajuan berhasil disetujui! Stok alat otomatis berkurang melalui TRIGGER database secara aman (Anti-Deadlock).";
            } else {
                throw new Exception("Transaksi ini sudah diproses atau divalidasi sebelumnya.");
            }
        } catch (Exception $e) {
            // Jika terjadi kegagalan, batalkan semua perubahan agar data tetap konsisten
            mysqli_rollback($conn);
            $error = "Terjadi kesalahan sistem (Concurrency Blocked): " . $e->getMessage();
        }

    } elseif ($action == 'kembali') {
        // Memulai Transaksi untuk proses pengembalian barang
        mysqli_begin_transaction($conn);

        try {
            // Mengunci data baris peminjaman sebelum diubah statusnya menjadi 'kembali'
            $check_lock = mysqli_query($conn, "SELECT status FROM peminjaman WHERE id_pinjam = '$id_pinjam' FOR UPDATE");
            $status_skrg = mysqli_fetch_assoc($check_lock);

            // Admin bisa memproses pengembalian baik status aslinya 'dipinjam' atau secara fisik sudah lewat tenggat
            if ($status_skrg['status'] === 'dipinjam') {
                // Hanya update status - TRIGGER database akan otomatis tambah stok kembali
                $query = "UPDATE peminjaman SET status = 'kembali' WHERE id_pinjam = '$id_pinjam'";
                mysqli_query($conn, $query);
                
                mysqli_commit($conn);
                $success = "✓ Alat berhasil dikembalikan! Stok alat otomatis bertambah melalui TRIGGER database.";
            } else {
                throw new Exception("Alat ini sudah berstatus kembali.");
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}

// ============================================================================================
// IMPLEMENTASI MATERI: MENGGUNAKAN VIEW & STORED FUNCTION DATABASE
// ============================================================================================
$query_request = "SELECT *, hitung_total_pinjam(id_user) as total_aktif FROM view_laporan_peminjaman ORDER BY id_pinjam DESC";
$requests = mysqli_query($conn, $query_request);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Peminjaman - LabTrack</title>
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
        <h4 class="fw-bold text-success mb-2">Validasi & Request Peminjaman</h4>
        <p class="text-muted small mb-4">Memantau data terintegrasi menggunakan Database View dan Stored Function.</p>

        <?php if(isset($success)) echo "<div class='alert alert-success py-2 small'>$success</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert alert-danger py-2 small'>$error</div>"; ?>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead class="table-dark small text-uppercase">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Alat Praktikum</th>
                        <th>Jumlah</th>
                        <th>Batas Kembali</th>
                        <th>Total Pinjam Aktif (Function)</th>
                        <th>Status Saat Ini</th>
                        <th>Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($requests) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($requests)): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo $row['nama_mahasiswa']; ?></td>
                            <td class="text-start"><?php echo $row['nama_alat']; ?></td>
                            <td><?php echo $row['jumlah']; ?> Pcs</td>
                            
                            <td>
                                <?php 
                                    $jam_data = !empty($row['jam_kembali']) ? $row['jam_kembali'] : '00:00:00';
                                    echo date('d-m-Y', strtotime($row['tgl_kembali'])) . ' ' . date('H:i', strtotime($jam_data)); 
                                ?>
                            </td>
                            
                            <td><span class="badge bg-dark"><?php echo $row['total_aktif']; ?> Item Aktif</span></td>
                            
                            <td>
                                <?php 
                                // Logika Deteksi Terlambat Real-time
                                $waktu_sekarang = time();
                                $jam_format = !empty($row['jam_kembali']) ? $row['jam_kembali'] : '00:00:00';
                                $string_waktu_kembali = $row['tgl_kembali'] . ' ' . $jam_format;
                                $waktu_kembali = strtotime($string_waktu_kembali);
                                
                                $status_tampil = $row['status'];
                                if ($row['status'] == 'dipinjam' && $waktu_sekarang > $waktu_kembali) {
                                    $status_tampil = 'terlambat_dihitung';
                                }

                                if ($status_tampil == 'terlambat_dihitung') {
                                    echo '<span class="badge bg-danger py-2 px-3">Terlambat</span>';
                                } elseif($status_tampil == 'menunggu') {
                                    echo '<span class="badge bg-warning text-dark py-2 px-3">Menunggu</span>';
                                } elseif($status_tampil == 'dipinjam') {
                                    echo '<span class="badge bg-primary py-2 px-3">Dipinjam</span>';
                                } elseif($status_tampil == 'kembali') {
                                    echo '<span class="badge bg-success py-2 px-3">Kembali</span>';
                                } else {
                                    echo '<span class="badge bg-secondary py-2 px-3">Status Tidak Dikenal</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php 
                                // PERBAIKAN LOGIKA: Sinkronisasi status_tampil agar tombol Selesai tetap aktif saat Terlambat
                                if($status_tampil == 'menunggu'): 
                                ?>
                                    <a href="kelola.php?action=setujui&id=<?php echo $row['id_pinjam']; ?>" class="btn btn-sm btn-success fw-bold px-2" onclick="return confirm('Setujui peminjaman ini?')">Setujui</a>
                                <?php elseif($status_tampil == 'dipinjam' || $status_tampil == 'terlambat_dihitung'): ?>
                                    <a href="kelola.php?action=kembali&id=<?php echo $row['id_pinjam']; ?>" class="btn btn-sm btn-info text-white fw-bold px-2" onclick="return confirm('Selesaikan peminjaman?')">Selesai</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Selesai</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat transaksi pengajuan.</td>
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