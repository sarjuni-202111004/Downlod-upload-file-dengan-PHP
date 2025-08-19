<?php
session_start();

// Hentikan cache browser
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $validUser = '177422';
    $validPass = 'NEWPASSWORD!';

    if ($username === $validUser && $password === $validPass) {
        $_SESSION['login'] = true;
        $_SESSION['username'] = $username;
        header("Location: admin.php");
        exit;
    } else {
        $msg = "❌ Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:#f9f9f9;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }
        .login-box {
            background:#fff;
            padding:30px;
            border-radius:10px;
            box-shadow:0 0 15px rgba(0,0,0,0.1);
            width:350px;
        }
        h2 { text-align:center; margin-bottom:20px; }
        input {
            width:100%;
            padding:12px;
            margin-bottom:15px;
            border:1px solid #ccc;
            border-radius:5px;
            box-sizing: border-box;
        }
        .password-wrapper { position:relative; margin-bottom:15px; }
        .password-wrapper input { padding-right:30px; }
        .toggle-password {
            position:absolute;
            right:10px;
            top:50%;
            transform:translateY(-50%);
            font-size:14px;
            color:#555;
            cursor:pointer;
            user-select:none;
        }
        button {
            width:100%;
            padding:12px;
            border:none;
            border-radius:5px;
            background:#2ecc71;
            color:#fff;
            font-size:16px;
            cursor:pointer;
        }
        button:hover { background:#27ae60; }
        .msg {
            text-align:center;
            margin-bottom:15px;
            font-weight:bold;
            color:#c0392b;
        }
    </style>
</head>
<body>
<div class="login-box">
    <h2>Login Admin</h2>
    <?php if(!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>
    <form method="post" autocomplete="off">
        <input type="text" name="username" placeholder="Username" required autofocus autocomplete="off">
        <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="Password" required autocomplete="new-password">
            <span class="toggle-password" onclick="togglePassword()">👁️</span>
        </div>
        <button type="submit">Login</button>
    </form>
</div>

<script>
// Hilangkan isi input saat halaman load (misalnya setelah back)
window.onload = function() {
    document.querySelector("input[name='username']").value = "";
    document.querySelector("input[name='password']").value = "";
};

function togglePassword() {
    const pw = document.getElementById('password');
    const icon = document.querySelector('.toggle-password');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.textContent = '🙈'; // hide
    } else {
        pw.type = 'password';
        icon.textContent = '👁️'; // show
    }
}
</script>
</body>
</html>
