<?php
session_start();

$ADMIN_PASS = "NewAdmin123!";

$usersFile = 'users.json';
$guestFile = 'guest.json';

// Auto-create files if they don't exist
if (!file_exists($usersFile)) {
    file_put_contents($usersFile, '[]');
}
if (!file_exists($guestFile)) {
    file_put_contents($guestFile, json_encode(['enabled' => false, 'password' => 'guest123']));
}

function loadUsers() {
    global $usersFile;
    if (file_exists($usersFile)) {
        return json_decode(file_get_contents($usersFile), true) ?: [];
    }
    return [];
}

function saveUsers($users) {
    global $usersFile;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

function getGuest() {
    global $guestFile;
    if (file_exists($guestFile)) {
        return json_decode(file_get_contents($guestFile), true) ?: ['enabled' => false, 'password' => 'guest123'];
    }
    return ['enabled' => false, 'password' => 'guest123'];
}

$error = "";

// Debug mode
if (isset($_GET['debug'])) {
    echo '<h2>Debug Info</h2>';
    echo 'users.json exists: ' . (file_exists($usersFile) ? 'YES' : 'NO') . '<br>';
    echo 'guest.json exists: ' . (file_exists($guestFile) ? 'YES' : 'NO') . '<br>';
    echo 'Directory writable: ' . (is_writable('.') ? 'YES' : 'NO') . '<br>';
    echo 'users.json content: ' . htmlspecialchars(file_get_contents($usersFile)) . '<br>';
    echo 'guest.json content: ' . htmlspecialchars(file_get_contents($guestFile)) . '<br>';
    die();
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $pass = trim($_POST['password']);
    $users = loadUsers();
    $guest = getGuest();

    if ($pass === $ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
        header("Location: main.php");
        exit;
    }

    if ($guest['enabled'] && $pass === $guest['password']) {
        $_SESSION['logged_in'] = true;
        header("Location: main.php");
        exit;
    }

    foreach ($users as &$user) {
        if ($user['password'] === $pass && $user['used'] < $user['maxUses']) {
            $user['used']++;
            saveUsers($users);
            $_SESSION['logged_in'] = true;
            header("Location: main.php");
            exit;
        }
    }

    $error = "Wrong password or limit reached!";
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
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>BUB Hub</title>
  <style>
    body { margin:0; height:100vh; background:linear-gradient(#1e3a8a,#3b82f6); font-family:system-ui; color:white; overflow:hidden; }
    .lock { position:fixed; inset:0; background:rgba(0,0,0,0.97); display:flex; align-items:center; justify-content:center; }
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
      <?php if ($error): ?>
        <p class="error">❌ <?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <p style="margin-top:10px; color:#aaa;">Guest Password: <strong>asd</strong></p>
    </div>
  </div>
<?php endif; ?>

<?php if (isset($_SESSION['logged_in'])): ?>
  <header style="background:#4a9c4a;padding:20px;text-align:center;font-size:28px;">
    BUB Hub 
    <a href="?logout=1" style="float:right;color:white;font-size:18px;">Logout</a>
  </header>
  <main style="padding:40px;text-align:center;">
    <h2>🎮 Your Local Games</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;max-width:1200px;margin:40px auto;">
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/kdata.html','_blank')">Kdata Game Hub</div>
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/thegub.html','_blank')">thegub</div>
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/thehub.html','_blank')">thehub</div>
      <div style="background:#272d31;border:6px solid #4ade80;padding:30px;border-radius:12px;cursor:pointer;" onclick="window.open('games/myai.html','_blank')">MyAI - Echo</div>
    </div>
  </main>
<?php endif; ?>

</body>
</html>
