<?php
$counterFile = 'counter.json';
$folder = 'files';

// Validasi parameter file
if (!isset($_GET['file']) || empty($_GET['file'])) {
    die("File tidak ditemukan.");
}

$filename = basename($_GET['file']);
$filepath = $folder . '/' . $filename;

if (!file_exists($filepath)) {
    die("File tidak ditemukan.");
}

// Baca counter
$data = json_decode(file_get_contents($counterFile), true);

// Tambah counter download untuk file ini
if (!isset($data['downloads'][$filename])) {
    $data['downloads'][$filename] = 0;
}
$data['downloads'][$filename] += 1;

// Simpan kembali
file_put_contents($counterFile, json_encode($data));

// Proses download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
?>