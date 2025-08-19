<?php
session_start();

// Nama file counter
$counterFile = 'counter.json';

// Kalau belum ada file counter, buat file kosong
if (!file_exists($counterFile)) {
    file_put_contents($counterFile, json_encode(["views" => 0, "downloads" => [], "descriptions" => []]));
}

// Baca data counter
$data = json_decode(file_get_contents($counterFile), true);

// Tambah jumlah view hanya sekali per sesi
if (!isset($_SESSION['counted'])) {
    $data['views'] = isset($data['views']) ? $data['views'] + 1 : 1;
    file_put_contents($counterFile, json_encode($data));
    $_SESSION['counted'] = true;
}

// Folder file
$folder = "files";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// Ambil daftar file dari folder "files"
$files = array_diff(scandir($folder), ['.', '..']);

// Fungsi format ukuran file
function formatSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar File Download</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: auto; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        a { text-decoration: none; color: blue; }
        footer { margin-top: 40px; padding: 15px; text-align: center; font-size: 14px; color: #555; border-top: 1px solid #ccc; }
        footer a { color: green; font-weight: bold; }
    </style>
</head>
<body>

<h1>📂 Download PKTG</h1>
<p>👁 Dilihat: <b><?= $data['views'] ?></b> kali</p>

<p><a href="login.php">🔑 Login Admin</a></p>

<!-- Tabel File -->
<table>
    <tr>
        <th>Nama File</th>
        <th>Ukuran</th>
        <th>Keterangan</th>
        <th>Jumlah Download</th>
        <th>Terakhir Diubah</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($files as $file): 
        $path = $folder . '/' . $file;
        ?>
    <tr>
        <td><?= htmlspecialchars($file) ?></td>
        <td><?= formatSize(filesize($path)) ?></td>
        <td><?= isset($data['descriptions'][$file]) ? htmlspecialchars($data['descriptions'][$file]) : '-' ?></td>
        <td><?= isset($data['downloads'][$file]) ? $data['downloads'][$file] : 0 ?> kali</td>
        <td><?= date("d-m-Y H:i", filemtime($path)) ?></td>
        <td><a href="download.php?file=<?= urlencode($file) ?>">📥 Download</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Footer -->
<footer>
    💡 Ada saran atau masukan? Hubungi
    <a href="https://wa.me/6281311734533" target="_blank">WhatsApp</a>.
</footer>

</body>
</html>
