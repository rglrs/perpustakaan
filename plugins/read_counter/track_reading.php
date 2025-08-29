<?php
// nama file: track_reading.php
// lokasi: plugins/read_counter/

// Set header untuk memberitahu browser bahwa ini adalah response JSON
header('Content-Type: application/json');

// Inisialisasi SLiMS
define('INDEX_AUTH', 1);

// Cek jika file sysconfig ada, jika tidak, berikan error JSON
if (!file_exists('../../sysconfig.inc.php')) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'sysconfig.inc.php not found. Script may be in the wrong directory.']);
    exit;
}
require '../../sysconfig.inc.php';

// Pastikan hanya member yang login yang bisa mengakses
if (!isset($_SESSION['uid'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['status' => 'error', 'message' => 'You are not logged in!']);
    exit;
}

// Ambil data dari request POST
$biblio_id = isset($_POST['biblio_id']) ? (int)$_POST['biblio_id'] : 0;
$file_id = isset($_POST['file_id']) ? (int)$_POST['file_id'] : 0;
$duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0;
$last_page = isset($_POST['last_page']) ? (int)$_POST['last_page'] : 0;
$uid = $_SESSION['uid'];

if ($biblio_id && $file_id && $uid) {
    // Cek apakah data untuk biblio, file, dan user ini sudah ada
    $check_q = $dbs->query("SELECT * FROM read_counter WHERE biblio_id = {$biblio_id} AND file_id = {$file_id} AND uid = '{$uid}'");

    if ($check_q->num_rows > 0) {
        // Jika sudah ada, update durasi dan halaman terakhir
        $dbs->query("UPDATE read_counter SET duration = duration + {$duration}, last_page = {$last_page}, read_date = NOW(), read_count = read_count + 1 WHERE biblio_id = {$biblio_id} AND file_id = {$file_id} AND uid = '{$uid}'");
    } else {
        // Jika belum ada, buat record baru
        $dbs->query("INSERT INTO read_counter (biblio_id, file_id, uid, read_count, duration, last_page, read_date) VALUES ({$biblio_id}, {$file_id}, '{$uid}', 1, {$duration}, {$last_page}, NOW())");
    }

    echo json_encode(['status' => 'success', 'message' => 'Tracking data saved successfully!']);
} else {
    // Jika data tidak valid
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid data provided!', 'received' => $_POST]);
}
```

***

#### 2. Perbaiki Skrip di `viewer.php` (Sisi Client)

Sekarang, buka kembali file `js/pdfjs/web/viewer.php`. Ganti blok `<script>` yang sebelumnya kita tambahkan dengan versi yang lebih baik di bawah ini. Versi ini secara eksplisit memberitahu AJAX untuk mengharapkan JSON dan akan memberikan log yang lebih jelas jika terjadi error.

```html
<!-- Letakkan kode ini sebelum tag </body> di file js/pdfjs/web/viewer.php -->
<script>
    // Pastikan jQuery sudah dimuat
    if (window.jQuery) {
        // Inisialisasi variabel pelacakan
        let startTime = new Date();
        let currentPage = 1;
        let biblioId = <?php echo isset($_GET['biblio_id']) ? (int)$_GET['biblio_id'] : 0; ?>;
        let fileId = <?php echo isset($_GET['fid']) ? (int)$_GET['fid'] : 0; ?>;

        // Fungsi untuk mengirim data pelacakan ke server
        function sendTrackingData(page) {
            let endTime = new Date();
            let timeSpentInSeconds = Math.round((endTime - startTime) / 1000);

            // Hanya kirim jika durasi lebih dari 0 untuk efisiensi
            if (timeSpentInSeconds > 0) {
                $.ajax({
                    url: '<?php echo SWB_URL; ?>plugins/read_counter/track_reading.php',
                    type: 'POST',
                    dataType: 'json', // Eksplisit minta response JSON
                    data: {
                        biblio_id: biblioId,
                        file_id: fileId,
                        duration: timeSpentInSeconds,
                        last_page: page
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            console.log('Reading activity tracked:', response.message);
                        } else {
                            console.warn('Tracking warning:', response.message);
                        }
                        // Reset timer setelah berhasil mengirim
                        startTime = new Date();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Failed to send tracking data. Status:', textStatus, 'Error:', errorThrown);
                        // Coba parsing responseText jika ada, untuk debug
                        try {
                            let errorResponse = JSON.parse(jqXHR.responseText);
                            console.error('Server error message:', errorResponse.message);
                        } catch (e) {
                            console.error('Could not parse error response:', jqXHR.responseText);
                        }
                    }
                });
            }
        }

        // Lacak setiap kali halaman berubah
        PDFViewerApplication.eventBus.on('pagechanging', function (evt) {
            let newPageNumber = evt.pageNumber;
            // Kirim data halaman sebelumnya sebelum berganti
            sendTrackingData(currentPage);
            // Update halaman saat ini
            currentPage = newPageNumber;
        });

        // Kirim data terakhir saat pengguna akan menutup tab/browser
        window.addEventListener('beforeunload', function (e) {
            // Parameter `currentPage` sudah berisi halaman terakhir yang dilihat
            sendTrackingData(currentPage);
        });
        
        // Kirim data secara periodik (setiap 1 menit)
        setInterval(function() {
            sendTrackingData(currentPage);
        }, 60000);
    }
</script>
