<?php
session_start();

define('ADMIN_PASS', 'Password.');
define('EMERGENCY_PASS', 'Emergency123!');

if (isset($_POST['password'])) {
    $pass = trim($_POST['password']);
    echo "<!-- Debug: Password entered = " . htmlspecialchars($pass) . " -->";
    
    if ($pass === ADMIN_PASS || $pass === EMERGENCY_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
        header("Location: main.php");
        exit;
    } else {
        $error = "Wrong password! Try Emergency123!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: main.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>BUB Hub</title>
  <style>
    body { margin:0; height:100vh; background: linear-gradient(#1e3a8a, #3b82f6); font-family: system-ui; color:white; overflow:hidden; }
    .lock { position:fixed; inset:0; background:rgba(0,0,0,0.97); display:flex; align-items:center; justify-content:center; z-index:10000; }
    .box { background:#1f2528; border:10px solid #4ade80; padding:50px 40px; border-radius:16px; text-align:center; max-width:420px; width:90%; box-shadow:0 0 60px #4ade80; }
    input { width:100%; padding:18px; font-size:22px; background:#111; color:white; border:6px solid #4ade80; border-radius:8px; margin:15px 0; }
    button { width:100%; padding:16px; font-size:18px; background:#4ade80; color:black; border:none; border-radius:8px; cursor:pointer; font-weight:bold; }
    .error { color:#ff6666; margin-top:10px; font-weight:bold; }
  </style>
</head>
<body>

<?php if (!isset($_SESSION['logged_in'])): ?>
  <div class="lock">
    <div class="box">
      <h2>🔒 BUB HUB</h2>
      <p>Enter your password to continue</p>
      <form method="post">
        <input type="password" name="password" placeholder="Password" required autofocus>
        <button type="submit">UNLOCK HUB</button>
      </form>
      <?php if (isset($error)): ?>
        <p class="error">❌ <?= $error ?></p>
      <?php endif; ?>
      <p style="margin-top:20px; font-size:14px; color:#aaa;">
        Try: <strong>Emergency123!</strong>
      </p>
    </div>
  </div>
<?php endif; ?>

<header style="background:#4a9c4a; padding:20px; text-align:center; font-size:28px; cursor:pointer;" onclick="adminClick()">
  BUB Hub
</header>

<?php if (isset($_SESSION['logged_in'])): ?>
  <main style="padding:40px; text-align:center;">
    <h2>🎮 Your Local Games</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:20px; max-width:1200px; margin:40px auto;">
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/kdata.html','_blank')">Kdata Game Hub</div>
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/thegub.html','_blank')">thegub</div>
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/thehub.html','_blank')">thehub</div>
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/myai.html','_blank')">MyAI - Echo</div>
    </div>
  </main>
<?php endif; ?>

<script>
let clicks = 0;
function adminClick() {
  clicks++;
  if (clicks >= 5) {
    window.location.href = "admin.php";
  }
}
</script>
</body>
</html>
