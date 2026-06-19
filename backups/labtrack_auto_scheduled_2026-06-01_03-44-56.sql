DROP TABLE IF EXISTS `inventaris`;
CREATE TABLE `inventaris` (
  `id_alat` int NOT NULL AUTO_INCREMENT,
  `nama_alat` varchar(100) NOT NULL,
  `stok` int NOT NULL,
  `deskripsi` text,
  PRIMARY KEY (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `inventaris` VALUES("1","Mikroskop Binokuler","10","Mikroskop laboratorium biologi");
INSERT INTO `inventaris` VALUES("2","Solder Listrik 60W","15","Solder untuk praktikum elektro");
INSERT INTO `inventaris` VALUES("3","Arduino Uno R3","20","Microcontroller kit IoT");
INSERT INTO `inventaris` VALUES("4","termometer","20","alat pengecek suhu badan");
INSERT INTO `inventaris` VALUES("5","Mikroskop Binokuler","10","Mikroskop laboratorium biologi");
INSERT INTO `inventaris` VALUES("6","termometer","20","alat pengecek suhu badan");


DROP TABLE IF EXISTS `peminjaman`;
CREATE TABLE `peminjaman` (
  `id_pinjam` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `id_alat` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `tgl_pinjam` date NOT NULL,
  `tgl_kembali` date NOT NULL,
  `status` enum('menunggu','dipinjam','kembali','terlambat') DEFAULT 'menunggu',
  PRIMARY KEY (`id_pinjam`),
  KEY `id_user` (`id_user`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `inventaris` (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `peminjaman` VALUES("1","3","1","2","2026-05-31","2026-05-31","kembali");
INSERT INTO `peminjaman` VALUES("2","3","2","2","2026-05-31","2026-05-31","kembali");
INSERT INTO `peminjaman` VALUES("3","3","3","4","2026-05-31","2026-05-31","kembali");
INSERT INTO `peminjaman` VALUES("4","3","3","3","2026-05-31","2026-05-31","kembali");
INSERT INTO `peminjaman` VALUES("5","4","3","1","2026-05-31","2026-06-27","kembali");


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','mahasiswa') NOT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` VALUES("1","adminlab","admin123","admin");
INSERT INTO `users` VALUES("3","oci","123","mahasiswa");
INSERT INTO `users` VALUES("4","velix","123456","mahasiswa");


