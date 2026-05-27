<?php
session_start();
if (!isset($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}

define('ADMIN_PASS', 'Password.');

$usersFile = 'users.json';
$guestFile = 'guest.json';

function loadUsers() { /* same as above */ 
    global $usersFile;
    return file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) ?: [] : [];
}
function saveUsers($users) {
    global $usersFile;
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}
function getGuest() { /* same */ 
    global $guestFile;
    return file_exists($guestFile) ? json_decode(file_get_contents($guestFile), true) ?: ['enabled'=>false,'password'=>'guest123'] : ['enabled'=>false,'password'=>'guest123'];
}
function saveGuest($data) {
    global $guestFile;
    file_put_contents($guestFile, json_encode($data, JSON_PRETTY_PRINT));
}

// Handle form submissions
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
    }
    if ($_POST['action'] === 'remove_user') {
        $users = loadUsers();
        array_splice($users, $_POST['index'], 1);
        saveUsers($users);
    }
    if ($_POST['action'] === 'update_guest') {
        saveGuest([
            'enabled' => isset($_POST['guest_enabled']),
            'password' => trim($_POST['guest_password'])
        ]);
    }
}

$users = loadUsers();
$guest = getGuest();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Panel - BUB Hub</title>
  <style>
    body { font-family:system-ui; background:#111; color:white; padding:20px; }
    input, button { padding:10px; margin:5px; }
    .user { background:#222; padding:15px; margin:10px 0; border-radius:8px; }
  </style>
</head>
<body>
  <h1>🔧 ADMIN PANEL</h1>
  <a href="index.php">← Back to Hub</a>

  <h2>Guest Password</h2>
  <form method="post">
    <input type="hidden" name="action" value="update_guest">
    <label><input type="checkbox" name="guest_enabled" <?= $guest['enabled'] ? 'checked' : '' ?>> Enable Guest Mode</label><br><br>
    <input type="text" name="guest_password" value="<?= htmlspecialchars($guest['password']) ?>" placeholder="Guest Password">
    <button type="submit">Save Guest Settings</button>
  </form>

  <h2>Add New Friend</h2>
  <form method="post">
    <input type="hidden" name="action" value="add_user">
    <input type="text" name="name" placeholder="Friend Name" required>
    <input type="text" name="password" placeholder="Password" required>
    <input type="number" name="maxUses" value="20" placeholder="Max Uses">
    <button type="submit">Add Friend</button>
  </form>

  <h2>Registered Friends</h2>
  <?php foreach ($users as $i => $user): ?>
    <div class="user">
      <strong><?= htmlspecialchars($user['name']) ?></strong> — 
      <?= htmlspecialchars($user['password']) ?> 
      (<?= $user['used'] ?>/<?= $user['maxUses'] ?>)
      <form method="post" style="display:inline;">
        <input type="hidden" name="action" value="remove_user">
        <input type="hidden" name="index" value="<?= $i ?>">
        <button type="submit" onclick="return confirm('Remove?')">Remove</button>
      </form>
    </div>
  <?php endforeach; ?>
</body>
</html>
