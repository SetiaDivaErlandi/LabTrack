<?php
include 'config/db.php';
session_start();

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_pinjam = mysqli_real_escape_string($conn, $_GET['id']);
    $action = $_GET['action'];

    if ($action == 'setujui') {
        mysqli_begin_transaction($conn);

        try {
            $check_lock = mysqli_query($conn, "SELECT status FROM peminjaman WHERE id_pinjam = '$id_pinjam' FOR UPDATE");
            $status_skrg = mysqli_fetch_assoc($check_lock);

            if ($status_skrg['status'] === 'menunggu') {
                $query = "UPDATE peminjaman SET status = 'dipinjam' WHERE id_pinjam = '$id_pinjam'";
                mysqli_query($conn, $query);
                
                mysqli_commit($conn);
                $success = "✓ Pengajuan berhasil disetujui! Stok alat otomatis berkurang melalui TRIGGER database secara aman (Anti-Deadlock).";
            } else {
                throw new Exception("Transaksi ini sudah diproses atau divalidasi sebelumnya.");
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Terjadi kesalahan sistem (Concurrency Blocked): " . $e->getMessage();
        }

    } elseif ($action == 'kembali') {
        mysqli_begin_transaction($conn);

        try {
            $check_lock = mysqli_query($conn, "SELECT status FROM peminjaman WHERE id_pinjam = '$id_pinjam' FOR UPDATE");
            $status_skrg = mysqli_fetch_assoc($check_lock);

            if ($status_skrg['status'] === 'dipinjam') {
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
                <thead>
                    <tr>
                        <th>MAHASISWA</th>
                        <th>ALAT PRAKTIKUM</th>
                        <th>JUMLAH</th>
                        <th>TGL PINJAM</th>     
                        <th>JAM PINJAM</th>   
                        <th>TGL KEMBALI</th>  
                        <th>JAM KEMBALI</th>  
                        <th>TOTAL PINJAM</th>
                        <th>STATUS</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($requests) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($requests)): 
                            $tgl_pinjam = date('d-m-Y', strtotime($row['tgl_pinjam']));
                            $jam_pinjam = date('H:i', strtotime($row['tgl_pinjam']));
                            
                            $tgl_kembali = date('d-m-Y', strtotime($row['tgl_kembali']));
                            $jam_kembali = !empty($row['jam_kembali']) ? date('H:i', strtotime($row['jam_kembali'])) : '00:00';
                        ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo $row['nama_mahasiswa']; ?></td>
                            <td class="text-start"><?php echo $row['nama_alat']; ?></td>
                            <td><?php echo $row['jumlah']; ?> Pcs</td>
                            
                            <td><?php echo $tgl_pinjam; ?></td>
                            <td><?php echo $jam_pinjam; ?></td>
                            
                            <td><?php echo $tgl_kembali; ?></td>
                            <td><?php echo $jam_kembali; ?></td>
                            
                            <td><span class="badge bg-dark"><?php echo $row['total_aktif']; ?> Item Aktif</span></td>
                            
                            <td>
                                <?php 
                                $waktu_sekarang = time();
                                $string_waktu_kembali = $row['tgl_kembali'] . ' ' . (!empty($row['jam_kembali']) ? $row['jam_kembali'] : '00:00:00');
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
                                <?php if($status_tampil == 'menunggu'): ?>
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
                            <td colspan="10" class="text-center py-4 text-muted">Belum ada riwayat transaksi pengajuan.</td>
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