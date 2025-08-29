<?php
// nama file: 1_CreateReadCounterTable.php
// lokasi: plugins/read_counter/migration/

// Pastikan hanya dijalankan dari dalam SLiMS
defined('INDEX_AUTH') OR die('Direct access not allowed!');

// Akses variabel database global SLiMS
global $dbs;

// Hapus tabel lama jika ada, untuk memastikan instalasi bersih
$dbs->query('DROP TABLE IF EXISTS `read_counter`');

// Buat tabel baru dengan semua kolom yang dibutuhkan
$sql = 'CREATE TABLE `read_counter` (
    `biblio_id` INT(11) UNSIGNED NOT NULL,
    `file_id` INT(11) UNSIGNED NOT NULL,
    `uid` VARCHAR(20) NOT NULL,
    `read_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
    `duration` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT \'Durasi membaca dalam detik\',
    `last_page` INT(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT \'Halaman terakhir dibaca\',
    `read_date` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`biblio_id`, `file_id`, `uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;';

$dbs->query($sql);
