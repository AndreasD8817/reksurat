
USE reksurat;
CREATE TABLE `disposisi_dewan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `surat_masuk_id` int NOT NULL,
  `nama_pegawai` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `catatan_disposisi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `tanggal_disposisi` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `file_lampiran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `surat_masuk_id` (`surat_masuk_id`),
  CONSTRAINT `disposisi_dewan_ibfk_1` FOREIGN KEY (`surat_masuk_id`) REFERENCES `surat_masuk_dewan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
