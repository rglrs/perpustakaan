<?php
/**
 * @package         read_counter
 * @author          Drajat Hasan (Modified by Gemini)
 * @license         GPLv3
 * @description     Menghitung setiap kali file digital dibaca oleh anggota dan merekam durasinya.
 * @version         2.1.0
 * @last-update     2024-05-21
 */

// Pastikan hanya dijalankan dari dalam SLiMS
defined('INDEX_AUTH') OR die('Direct access not allowed!');

/**
 * Mendaftarkan menu laporan ke SLiMS.
 *
 * @return array
 */
function getReportInfo()
{
    // Menggunakan sintaks array lama untuk kompatibilitas maksimum
    return array(
        'id' => 'read_counter',
        'title' => 'Read Counter Report',
        'file' => 'report.php'
    );
}

/**
 * Fungsi hook ini sengaja dikosongkan karena logika pencatatan
 * sudah dipindahkan ke 'track_reading.php' yang dipanggil via AJAX.
 */
function read_counter_plugin($data)
{
    // Tidak melakukan apa-apa di sini
    return;
}

// Daftarkan hook ke SLiMS
register_plugin('after_read_digital_file', 'read_counter_plugin');

