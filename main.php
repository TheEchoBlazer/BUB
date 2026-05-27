<?php
session_start();

// Admin Password
define('ADMIN_PASS', 'Password.');

// Simple file-based storage
$usersFile = 'users.json';
$guestFile = 'guest.json';

// Load data
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
        return json_decode(file_get_contents($guestFile), true);
    }
    return ['enabled' => false, 'password' => 'guest123'];
}

function saveGuest($data) {
    global $guestFile;
    file_put_contents($guestFile, json_encode($data));
}

// Handle Login
if ($_POST['login'] ?? false) {
    $pass = trim($_POST['password']);
    $users = loadUsers();
    $guest = getGuest();

    if ($pass === ADMIN_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['role'] = 'admin';
    } elseif ($guest['enabled'] && $pass === $guest['password']) {
        $_SESSION['logged_in'] = true;
    } else {
        foreach ($users as &$user) {
            if ($user['password'] === $pass && $user['used'] < $user['maxUses']) {
                $user['used']++;
                saveUsers($users);
                $_SESSION['logged_in'] = true;
                break;
            }
        }
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
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
    body { margin:0; font-family:system-ui; background:linear-gradient(#1e3a8a,#3b82f6); color:white; min-height:100vh; }
    .lock { position:fixed; inset:0; background:rgba(0,0,0,0.97); display:flex; align-items:center; justify-content:center; z-index:1000; }
    .box { background:#1f2528; border:10px solid #4ade80; padding:40px; border-radius:16px; text-align:center; max-width:400px; width:90%; }
    .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:20px; padding:20px; }
    .card { background:#272d31; border:6px solid #4ade80; padding:20px; border-radius:12px; text-align:center; cursor:pointer; }
    .card:hover { transform:scale(1.05); border-color:#fbbf24; }
  </style>
</head>
<body>

<?php if (!isset($_SESSION['logged_in'])): ?>
  <div class="lock">
    <div class="box">
      <h2>🔒 BUB HUB</h2>
      <p>Enter your password</p>
      <form method="post">
        <input type="password" name="password" style="width:100%;padding:15px;font-size:18px;margin:15px 0;" required>
        <button type="submit" name="login" style="width:100%;padding:15px;background:#4ade80;color:black;font-weight:bold;">UNLOCK HUB</button>
      </form>
      <?php if ($_POST['login'] ?? false): ?>
        <p style="color:red;">Wrong password or limit reached.</p>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<header style="background:#4a9c4a;padding:20px;text-align:center;font-size:24px;">
  <div onclick="adminClick()" style="cursor:pointer;">BUB Hub</div>
</header>

<main style="max-width:1200px;margin:40px auto;">
  <h2 style="text-align:center;">🎮 Your Local Games</h2>
  <div class="grid">
    <div class="card" onclick="openGame('games/kdata.html')">Kdata Game Hub</div>
    <div class="card" onclick="openGame('games/thegub.html')">thegub</div>
    <div class="card" onclick="openGame('games/thehub.html')">thehub</div>
    <div class="card" onclick="openGame('games/myai.html')">MyAI - Echo</div>
  </div>
</main>

<script>
function openGame(path) {
  window.open(path, '_blank');
}

let clicks = 0;
function adminClick() {
  clicks++;
  if (clicks >= 5) {
    const pass = prompt("Admin Password:");
    if (pass === "<?= ADMIN_PASS ?>") {
      window.location.href = "admin.php";
    } else {
      alert("Wrong admin password");
    }
    clicks = 0;
  }
}
</script>
</body>
</html>
