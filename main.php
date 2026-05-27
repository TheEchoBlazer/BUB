<?php
session_start();

define('ADMIN_PASS', 'Password.');
define('EMERGENCY_PASS', 'Emergency123!');

$usersFile = 'users.json';
$guestFile = 'guest.json';

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

function saveGuest($data) {
    global $guestFile;
    file_put_contents($guestFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Handle Login
if (isset($_POST['login'])) {
    $pass = trim($_POST['password']);
    $users = loadUsers();
    $guest = getGuest();

    if ($pass === ADMIN_PASS || $pass === EMERGENCY_PASS) {
        $_SESSION['logged_in'] = true;
        $_SESSION['is_admin'] = true;
    } elseif ($guest['enabled'] && $pass === $guest['password']) {
        $_SESSION['logged_in'] = true;
    } else {
        $found = false;
        foreach ($users as &$user) {
            if ($user['password'] === $pass && $user['used'] < $user['maxUses']) {
                $user['used']++;
                saveUsers($users);
                $_SESSION['logged_in'] = true;
                $found = true;
                break;
            }
        }
        if (!$found && isset($_POST['login'])) {
            $error = true;
        }
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
    :root {
      --grass: #5cb85c;
      --darkgrass: #4a9c4a;
    }
    * { box-sizing: border-box; }
    html,body {
      height:100%; margin:0; 
      font-family: system-ui, sans-serif;
      background: linear-gradient(180deg, #1e3a8a 0%, #3b82f6 100%);
      color: #fff;
      overflow: hidden;
    }

    header {
      background: linear-gradient(180deg, var(--grass), var(--darkgrass));
      padding: 20px;
      text-align: center;
      font-size: 28px;
      font-weight: bold;
      box-shadow: 0 6px 0 #8b5a2b;
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
      border: 10px solid #4ade80;
      padding: 50px 40px;
      border-radius: 16px;
      text-align: center;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 0 60px rgba(74, 222, 128, 0.9);
    }

    .lock-box h2 {
      color: #4ade80;
      font-size: 36px;
      margin: 0 0 10px 0;
    }

    input {
      width: 100%;
      padding: 18px;
      font-size: 22px;
      background: #111;
      color: white;
      border: 6px solid #4ade80;
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

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 20px;
      padding: 40px;
      max-width: 1300px;
      margin: auto;
    }

    .card {
      background: #272d31;
      border: 6px solid #4ade80;
      padding: 25px;
      border-radius: 12px;
      text-align: center;
      cursor: pointer;
      transition: 0.3s;
    }
    .card:hover {
      transform: scale(1.08);
      border-color: #fbbf24;
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
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">UNLOCK HUB</button>
      </form>
      <?php if (isset($error)): ?>
        <p style="color:#ff6666; margin-top:15px;">❌ Wrong password or limit reached</p>
      <?php endif; ?>
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
  <main>
    <h2 style="text-align:center; margin:40px 0 20px;">🎮 Your Local Games</h2>
    <div class="grid">
      <div class="card" onclick="window.open('games/kdata.html', '_blank')">Kdata Game Hub</div>
      <div class="card" onclick="window.open('games/thegub.html', '_blank')">thegub</div>
      <div class="card" onclick="window.open('games/thehub.html', '_blank')">thehub</div>
      <div class="card" onclick="window.open('games/myai.html', '_blank')">MyAI - Echo</div>
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
