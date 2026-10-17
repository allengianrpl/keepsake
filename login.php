<?php
// login.php
require_once 'includes/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
$user_name = $_SESSION['user_name'] ?? 'THANUSE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AUTH MODE // KEEPSAKE V2.0</title>
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
            <a href="login.php" class="active">👤 Auth Mode</a>
            <a href="items/create.php" class="btn-add">+ Add Keepsake</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Log Out</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <!-- Collections Hero Header Style -->
        <div class="collections-hero" style="border-color: var(--neon-cyan); margin-bottom: 1.5rem;">
            <span style="font-size:0.65rem; background:var(--neon-cyan); color:#000; padding:2px 8px; border-radius:3px; font-family:'Orbitron', sans-serif; font-weight:bold;">SECURITY PORTAL</span>
            <h2 style="color:var(--neon-cyan); text-shadow: 0 0 10px var(--neon-cyan); margin: 0.5rem 0 0.3rem 0;">👤 System Authentication</h2>
            <p style="color: var(--text-main); margin:0; font-size: 0.85rem;">Access your personal archives, collection bookmarks, and saved media links.</p>
        </div>

        <!-- Matching Collections Directory Card Box -->
        <div class="widget-box" style="border-color: var(--border-purple); padding: 1.8rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-purple); padding-bottom: 1rem; margin-bottom: 1.5rem;">
                <h3 style="font-family:'Orbitron', sans-serif; color: var(--neon-cyan); margin: 0; font-size: 1.1rem; text-shadow: 0 0 8px var(--neon-cyan);">
                    + ACCESS // LOGIN
                </h3>
                <div class="auth-tabs" style="border: none; margin: 0;">
                    <a href="login.php" class="auth-tab active" style="padding: 0.4rem 1rem;">LOG IN</a>
                    <a href="register.php" class="auth-tab" style="padding: 0.4rem 1rem;">REGISTER</a>
                </div>
            </div>

            <?php if ($error): ?>
                <p style="color: var(--neon-pink); font-size: 0.85rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">USER EMAIL ADDRESS:</label>
                    <input type="email" name="email" required placeholder="thanuse@keepsake.local" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px;">
                </div>
                
                <div class="form-group" style="margin: 0;">
                    <label style="color: var(--text-muted); font-size: 0.75rem; margin-bottom: 0.4rem;">PASSWORD:</label>
                    <input type="password" name="password" required placeholder="••••••••" style="background: var(--card-bg); border: 1px solid var(--border-purple); color: var(--neon-cyan); padding: 0.75rem 1rem; border-radius: 6px;">
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
                    <button type="submit" class="btn-nav" style="background: var(--neon-cyan); border-color: var(--neon-cyan); color: #000; padding: 0.75rem 2rem; cursor: pointer;">
                        AUTHENTICATE SYSTEM ↗
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