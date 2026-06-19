DROP TABLE IF EXISTS `inventaris`;
CREATE TABLE `inventaris` (
  `id_alat` int NOT NULL AUTO_INCREMENT,
  `nama_alat` varchar(100) NOT NULL,
  `stok` int NOT NULL,
  `deskripsi` text,
  PRIMARY KEY (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `inventaris` VALUES("1","Mikroskop Binokuler","3","Mikroskop laboratorium biologi");
INSERT INTO `inventaris` VALUES("2","Solder Listrik 60W","13","Solder untuk praktikum elektro");
INSERT INTO `inventaris` VALUES("3","Arduino Uno R3","18","Microcontroller kit IoT");
INSERT INTO `inventaris` VALUES("4","termometer","16","alat pengecek suhu badan");
INSERT INTO `inventaris` VALUES("5","Mikroskop Binokuler","5","Mikroskop laboratorium biologi");
INSERT INTO `inventaris` VALUES("6","termometer","15","alat pengecek suhu badan");


DROP TABLE IF EXISTS `peminjaman`;
CREATE TABLE `peminjaman` (
  `id_pinjam` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `id_alat` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `jam_kembali` time DEFAULT NULL,
  `tgl_kembali` date NOT NULL,
  `status` text,
  PRIMARY KEY (`id_pinjam`),
  KEY `id_user` (`id_user`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `inventaris` (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `peminjaman` VALUES("15","4","4","5","2026-06-03","20:26:00","2026-06-02","kembali");
INSERT INTO `peminjaman` VALUES("16","5","3","8","2026-06-03","20:36:00","2026-06-04","kembali");
INSERT INTO `peminjaman` VALUES("17","5","1","2","2026-06-03","20:55:00","2026-06-04","menunggu");
INSERT INTO `peminjaman` VALUES("18","5","1","1","2026-06-03","20:56:00","2026-06-03","menunggu");


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','mahasiswa') NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `last_active` datetime DEFAULT NULL,
  `reactivate_requested` tinyint(1) NOT NULL DEFAULT '0',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` VALUES("1","adminlab","admin123","admin","aktif","2026-06-03 20:34:23","0","","");
INSERT INTO `users` VALUES("3","oci","123","mahasiswa","nonaktif","2026-06-03 20:00:15","0","","");
INSERT INTO `users` VALUES("4","velix","123456","mahasiswa","aktif","2026-06-03 20:24:47","0","","");
INSERT INTO `users` VALUES("5","liza","12345","mahasiswa","aktif","2026-06-03 21:25:09","0","","");


