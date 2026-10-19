<?php
// register.php
require_once 'includes/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email is already registered.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            if ($insertStmt->execute([$name, $email, $password_hash])) {
                $success = 'Registration successful! <a href="login.php" style="color: var(--neon-cyan);">Log in here</a>.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
$user_name = (isset($_SESSION['user_id']) && !empty($_SESSION['user_name'])) ? $_SESSION['user_name'] : 'YOU';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>REGISTER // KEEPSAKE V2.0</title>
    <link rel="stylesheet" href="assets/css/style.css?v=4.0">
</head>
<body>
    <header class="header-dashboard">
        <div class="logo-brand">
            <h1>💎 KEEPSAKE</h1>
            <span class="version-tag">V2.0</span>
        </div>
        <nav>
            <a href="dashboard.php">⚙️ Dashboard</a>
            <a href="collections/index.php">📂 Collections</a>
            <a href="register.php" class="active">👤 Auth Mode</a>
            <a href="items/create.php" class="btn-add">+ Add Keepsake</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Log Out</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <div class="collections-hero" style="border-color: var(--neon-purple); margin-bottom: 1.5rem;">
            <span style="font-size:0.65rem; background:var(--neon-purple); color:#fff; padding:2px 8px; border-radius:3px; font-family:'Orbitron', sans-serif; font-weight:bold;">INITIALIZATION PORTAL</span>
            <h2 style="color:var(--neon-cyan); text-shadow: 0 0 10px var(--neon-cyan); margin: 0.5rem 0 0.3rem 0;">👤 Initialize Agent Profile</h2>
            <p style="color: var(--text-main); margin:0; font-size: 0.85rem;">Create a new user account to start building custom bookmark archives.</p>
        </div>

        <div class="widget-box" style="border-color: var(--border-purple); padding: 1.8rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-purple); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-family:'Orbitron', sans-serif; color: var(--neon-cyan); margin: 0; font-size: 1.1rem; text-shadow: 0 0 8px var(--neon-cyan);">
                    + ACCOUNT // INITIALIZE
                </h3>
                <div class="auth-tabs" style="border: none; margin: 0;">
                    <a href="login.php" class="auth-tab" style="padding: 0.4rem 1rem;">LOG IN</a>
                    <a href="register.php" class="auth-tab active" style="padding: 0.4rem 1rem;">REGISTER</a>
                </div>
            </div>

            <?php if ($error): ?>
                <p style="color: var(--neon-pink); font-size: 0.85rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p style="color: var(--neon-cyan); font-size: 0.85rem; margin-bottom: 1rem;"><?php echo $success; ?></p>
            <?php endif; ?>

            <form action="register.php" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">AGENT NAME:</label>
                    <input type="text" name="name" required placeholder="Thanuse" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px;">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">EMAIL ADDRESS:</label>
                    <input type="email" name="email" required placeholder="thanuse@keepsake.local" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px;">
                </div>

                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">PASSWORD:</label>
                    <input type="password" name="password" required placeholder="••••••••" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px;">
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="submit" class="btn-nav" style="background: var(--neon-purple); border-color: var(--neon-purple); color: #fff; padding: 0.75rem 2rem; cursor: pointer;">
                        CREATE ACCOUNT ↗
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        KEEPSAKE ARCHIVE SYSTEM // DESIGNED FOR <span><?php echo strtoupper(htmlspecialchars($user_name)); ?></span> // ALL SYSTEMS NOMINAL
    </footer>
</body>
</html>