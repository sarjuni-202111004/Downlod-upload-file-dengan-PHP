<?php
session_start();
$folder = "files";

// Hentikan cache browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

$counterFile = 'counter.json';
$maxFileSize = 10 * 1024 * 1024; // 10 MB
$allowedExtensions = ['doc', 'docx'];

// Baca data counter
if (file_exists($counterFile)) {
    $data = json_decode(file_get_contents($counterFile), true);
} else {
    $data = ["views" => 0, "downloads" => [], "descriptions" => []];
}

// --- Handle Upload ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = basename($_FILES['file']['name']);
    $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $target = $folder . '/' . $filename;
    $description = $_POST['description'] ?? "";

    if (!in_array($fileExt, $allowedExtensions)) {
        $_SESSION['flash_msg'] = "❌ Hanya file Word (.doc / .docx) yang diperbolehkan!";
        $_SESSION['flash_type'] = "error";
    } elseif ($_FILES['file']['size'] > $maxFileSize) {
        $_SESSION['flash_msg'] = "❌ File terlalu besar! Maksimal 10 MB.";
        $_SESSION['flash_type'] = "error";
    } else {
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
            // Simpan deskripsi
            $data['descriptions'][$filename] = $description;
            file_put_contents($counterFile, json_encode($data));

            $_SESSION['flash_msg'] = "✅ File berhasil diupload!";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_msg'] = "❌ Gagal upload file!";
            $_SESSION['flash_type'] = "error";
        }
    }
    header("Location: admin.php");
    exit;
}

// --- Handle Delete ---
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $filePath = $folder . "/" . $fileToDelete;

    if (file_exists($filePath)) {
        unlink($filePath);
        $_SESSION['flash_msg'] = "✅ File '$fileToDelete' berhasil dihapus!";
        $_SESSION['flash_type'] = "success";
    } else {
        $_SESSION['flash_msg'] = "❌ File tidak ditemukan!";
        $_SESSION['flash_type'] = "error";
    }
    header("Location: admin.php");
    exit;
}

// Ambil daftar file Word saja
$files = array_filter(array_diff(scandir($folder), ['.', '..']), function ($file) use ($allowedExtensions) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExtensions);
});

// Fungsi format size
function formatSize($bytes)
{
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:Arial,sans-serif; max-width:1000px; margin:40px auto; padding:0 20px; background:#f9f9f9; color:#333; }
        h1,h2 { text-align:center; margin-bottom:20px; }
        a { color:#007bff; text-decoration:none; }
        a:hover { text-decoration:underline; }
        .top-nav { display:flex; justify-content:space-between; margin-bottom:20px; }
        .msg { margin:15px 0; padding:15px; border-radius:5px; font-weight:bold; opacity:1; transition: opacity 0.5s ease; }
        .msg.success { background:#e6f7e6; border:1px solid #2ecc71; color:#2ecc71; }
        .msg.error { background:#fbeaea; border:1px solid #e74c3c; color:#c0392b; }
        form { background:#fff; padding:20px; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,0.05); margin-bottom:40px; }
        input[type="file"], input[type="text"] { width:100%; padding:10px; margin-bottom:15px; border:1px solid #ccc; border-radius:5px; }
        button { background:#2ecc71; color:#fff; border:none; padding:12px 20px; border-radius:5px; cursor:pointer; font-size:16px; }
        button:hover { background:#27ae60; }
        table { width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 0 10px rgba(0,0,0,0.05); }
        th, td { padding:12px 15px; text-align:left; border-bottom:1px solid #eee; }
        th { background:#f4f4f4; }
        tr:hover { background:#f1f9ff; }
        .delete { color:#e74c3c; font-weight:bold; }
        @media(max-width:768px){ th, td{ font-size:14px; padding:8px; } button{ width:100%; } }
    </style>
</head>
<body>

<div class="top-nav">
    <a href="index.php">⬅ Kembali ke Halaman Download</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<h1>⚙️ Admin Panel</h1>

<?php if (isset($_SESSION['flash_msg'])): ?>
    <div id="flashMessage" class="msg <?= $_SESSION['flash_type'] ?>">
        <?= $_SESSION['flash_msg'] ?>
    </div>
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<h2>📤 Upload File Baru (Max 10 MB)</h2>
<form method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <input type="text" name="description" placeholder="Keterangan file">
    <button type="submit">Upload</button>
</form>

<h2>📂 Daftar File</h2>
<table>
    <tr>
        <th>Nama File</th>
        <th>Keterangan</th>
        <th>Ukuran</th>
        <th>Jumlah Download</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($files as $file):
        $path = $folder . '/' . $file;
    ?>
    <tr>
        <td><?= htmlspecialchars($file) ?></td>
        <td><?= $data['descriptions'][$file] ?? '-' ?></td>
        <td><?= formatSize(filesize($path)) ?></td>
        <td><?= $data['downloads'][$file] ?? 0 ?> kali</td>
        <td>
            <a href="download.php?file=<?= urlencode($file) ?>">📥 Download</a> |
            <a class="delete" href="admin.php?delete=<?= urlencode($file) ?>" onclick="return confirm('Yakin hapus file ini?')">🗑 Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const flash = document.getElementById("flashMessage");
    if(flash){
        setTimeout(()=>{
            flash.style.opacity = "0";
            setTimeout(()=> flash.remove(), 500); // Hapus setelah fade out
        }, 4000); // 4 detik
    }
});
</script>

</body>
</html>
