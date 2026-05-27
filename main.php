<?php
session_start();

define('ADMIN_PASS', 'Password.');
define('EMERGENCY_PASS', 'Emergency123!');

$usersFile = 'users.json';
$guestFile = 'guest.json';

// Simple functions
function loadUsers() {
    global $usersFile;
    return file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) ?: [] : [];
}

function saveUsers($users) {
    global $usersFile;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

function getGuest() {
    global $guestFile;
    return file_exists($guestFile) ? json_decode(file_get_contents($guestFile), true) ?: ['enabled' => false, 'password' => 'guest123'] : ['enabled' => false, 'password' => 'guest123'];
}

// Handle Login
if (isset($_POST['password'])) {
    $pass = trim($_POST['password']);
    $users = loadUsers();
    $guest = getGuest();

    if ($pass === ADMIN_PASS || $pass === EMERGENCY_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
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
    
    // Refresh page after login attempt
    header("Location: main.php");
    exit;
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
    :root { --glow: #4ade80; }
    * { box-sizing: border-box; }
    html,body {
      height:100%; margin:0; padding:0;
      font-family: system-ui, sans-serif;
      background: linear-gradient(180deg, #1e3a8a, #3b82f6);
      color: #fff;
      overflow: hidden;
    }

    header {
      background: linear-gradient(180deg, #5cb85c, #4a9c4a);
      padding: 20px;
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }

    .lock {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.97);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 10000;
    }

    .lock-box {
      background: #1f2528;
      border: 10px solid var(--glow);
      padding: 50px 40px;
      border-radius: 16px;
      text-align: center;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 0 60px rgba(74, 222, 128, 0.9);
    }

    input {
      width: 100%;
      padding: 18px;
      font-size: 22px;
      background: #111;
      color: white;
      border: 6px solid var(--glow);
      border-radius: 8px;
      margin-bottom: 20px;
    }

    button {
      width: 100%;
      padding: 16px;
      font-size: 18px;
      font-weight: bold;
      background: #4ade80;
      color: black;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<?php if (!isset($_SESSION['logged_in'])): ?>
  <div class="lock">
    <div class="lock-box">
      <h2>🔒 BUB HUB</h2>
      <p>Enter your password to continue</p>
      <form method="post">
        <input type="password" name="password" placeholder="Password" required autofocus>
        <button type="submit">UNLOCK HUB</button>
      </form>
    </div>
  </div>
<?php endif; ?>

<header onclick="adminClick()">
  BUB Hub
  <?php if (isset($_SESSION['logged_in'])): ?>
    <a href="?logout=1" style="float:right; color:white; font-size:18px; margin-top:6px;">Logout</a>
  <?php endif; ?>
</header>

<?php if (isset($_SESSION['logged_in'])): ?>
  <main style="padding:40px; text-align:center;">
    <h2 style="margin-bottom:30px;">🎮 Your Local Games</h2>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:20px; max-width:1200px; margin:auto;">
      <div style="background:#272d31; border:6px solid #4ade80; padding:25px; border-radius:12px; cursor:pointer;" onclick="window.open('games/kdata.html', '_blank')">Kdata Game Hub</div>
      <div style="background:#272d31; border:6px solid #4ade80; padding:25px; border-radius:12px; cursor:pointer;" onclick="window.open('games/thegub.html', '_blank')">thegub</div>
      <div style="background:#272d31; border:6px solid #4ade80; padding:25px; border-radius:12px; cursor:pointer;" onclick="window.open('games/thehub.html', '_blank')">thehub</div>
      <div style="background:#272d31; border:6px solid #4ade80; padding:25px; border-radius:12px; cursor:pointer;" onclick="window.open('games/myai.html', '_blank')">MyAI - Echo</div>
    </div>
  </main>
<?php endif; ?>

<script>
let clicks = 0;
function adminClick() {
  clicks++;
  if (clicks >= 5) {
    const pass = prompt("Enter Admin Password:");
    if (pass === "<?= ADMIN_PASS ?>" || pass === "<?= EMERGENCY_PASS ?>") {
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
