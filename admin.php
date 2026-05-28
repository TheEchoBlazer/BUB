<?php
session_start();

if (!isset($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}

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

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_user') {
            $users = loadUsers();
            $users[] = [
                'name' => trim($_POST['name']),
                'password' => trim($_POST['password']),
                'maxUses' => (int)$_POST['maxUses'],
                'used' => 0
            ];
            saveUsers($users);
            $message = "✅ Friend added successfully!";
        }
        if ($_POST['action'] === 'remove_user') {
            $users = loadUsers();
            array_splice($users, (int)$_POST['index'], 1);
            saveUsers($users);
            $message = "✅ User removed!";
        }
        if ($_POST['action'] === 'update_guest') {
            saveGuest([
                'enabled' => isset($_POST['guest_enabled']),
                'password' => trim($_POST['guest_password'])
            ]);
            $message = "✅ Guest settings saved!";
        }
    }
}

$users = loadUsers();
$guest = getGuest();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Panel - BUB Hub</title>
  <style>
    body { font-family:system-ui; background:#0f1620; color:white; padding:20px; }
    .container { max-width:1000px; margin:auto; background:#1f2528; border:10px solid #4ade80; border-radius:16px; padding:30px; }
    input, button { padding:12px; margin:6px 0; font-size:16px; }
    button { background:#4ade80; color:black; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
    .red { background:#ff6666; color:white; }
    .user-item { background:#2a2f34; padding:15px; margin:10px 0; border-radius:8px; display:flex; justify-content:space-between; align-items:center; }
    .success { color:#4ade80; font-weight:bold; }
  </style>
</head>
<body>
  <div class="container">
    <h1>🔧 ADMIN PANEL</h1>
    <p><strong>Admin Password:</strong> <code>NewAdmin123!</code></p>
    <a href="index.php">← Back to Hub</a> | 
    <a href="index.php?logout=1" style="color:#ff6666;">Logout</a>

    <?php if ($message): ?>
      <p class="success"><?= $message ?></p>
    <?php endif; ?>

    <hr>

    <h2>Guest Password</h2>
    <form method="post">
      <input type="hidden" name="action" value="update_guest">
      <label><input type="checkbox" name="guest_enabled" <?= $guest['enabled'] ? 'checked' : '' ?>> Enable Guest Mode</label><br><br>
      <input type="text" name="guest_password" value="<?= htmlspecialchars($guest['password']) ?>" placeholder="Guest Password">
      <button type="submit">Save Guest</button>
    </form>

    <h2>Add New Friend</h2>
    <form method="post">
      <input type="hidden" name="action" value="add_user">
      <input type="text" name="name" placeholder="Friend Name" required>
      <input type="text" name="password" placeholder="Password" required>
      <input type="number" name="maxUses" value="20">
      <button type="submit">Add Friend</button>
    </form>

    <h2>Registered Friends</h2>
    <?php foreach ($users as $i => $user): ?>
      <div class="user-item">
        <div>
          <strong><?= htmlspecialchars($user['name']) ?></strong> — 
          <?= htmlspecialchars($user['password']) ?><br>
          <small>Uses: <?= $user['used'] ?>/<?= $user['maxUses'] ?></small>
        </div>
        <form method="post" style="display:inline;">
          <input type="hidden" name="action" value="remove_user">
          <input type="hidden" name="index" value="<?= $i ?>">
          <button type="submit" class="red" onclick="return confirm('Remove this friend?')">Remove</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
