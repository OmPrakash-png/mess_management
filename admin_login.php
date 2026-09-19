<?php
include 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows == 1) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="sunset">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal Login - Mess Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="flex flex-col min-h-screen justify-between p-4">

<!-- Ambient Glowing Orbs -->
<div class="bg-ambient-orb bg-ambient-orb-1"></div>
<div class="bg-ambient-orb bg-ambient-orb-2"></div>

<!-- Top Minimal Navbar for Theme Toggle & Back -->
<div class="max-w-5xl w-full mx-auto flex items-center justify-between py-2 relative z-20">
  <a href="index.php" class="btn-outline text-xs py-1.5 px-3">
    <span>←</span>
    <span>Back to Student Portal</span>
  </a>

  <!-- Theme Dropdown -->
  <div class="theme-dropdown">
    <button id="themeDropdownBtn" type="button" class="btn-outline text-xs py-1.5 px-3">
      <span>🎨</span>
      <span id="activeThemeLabel">Theme</span>
    </button>
    <div id="themeDropdownMenu" class="theme-dropdown-menu">
      <button type="button" class="theme-opt-btn" data-set-theme="sunset">
        <span class="color-dot" style="background: linear-gradient(135deg, #ff5e3a, #ff9500);"></span>
        <span>Sunset Ember</span>
      </button>
      <button type="button" class="theme-opt-btn" data-set-theme="midnight">
        <span class="color-dot" style="background: linear-gradient(135deg, #8b5cf6, #3b82f6);"></span>
        <span>Midnight Neon</span>
      </button>
      <button type="button" class="theme-opt-btn" data-set-theme="emerald">
        <span class="color-dot" style="background: linear-gradient(135deg, #10b981, #14b8a6);"></span>
        <span>Fresh Emerald</span>
      </button>
      <button type="button" class="theme-opt-btn" data-set-theme="ocean">
        <span class="color-dot" style="background: linear-gradient(135deg, #0ea5e9, #0284c7);"></span>
        <span>Ocean Azure</span>
      </button>
      <button type="button" class="theme-opt-btn" data-set-theme="light">
        <span class="color-dot" style="background: linear-gradient(135deg, #f97316, #ea580c); border:1px solid #ccc;"></span>
        <span>Light Luxe</span>
      </button>
    </div>
  </div>
</div>

<!-- Center Card Container -->
<div class="flex-1 flex items-center justify-center relative z-10 my-8">
  <div class="glass-card w-full max-w-md p-8 anim-fade-up <?php echo $error ? 'shake-animation' : ''; ?>">
    
    <!-- Header with Icon -->
    <div class="text-center mb-6">
      <div class="inline-flex p-3 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 text-3xl mb-3 shadow-lg shadow-orange-500/30">
        🔐
      </div>
      <h1 class="text-2xl font-extrabold tracking-tight text-white">Administrator Access</h1>
      <p class="text-xs text-gray-400 mt-1">Sign in to manage daily menus, announcements & reviews</p>
    </div>

    <!-- Error Alert -->
    <?php if ($error): ?>
      <div class="mb-5 p-3 rounded-xl bg-red-500/15 border border-red-500/30 text-red-400 text-xs flex items-center gap-2">
        <span class="text-base">⚠️</span>
        <span><?php echo htmlspecialchars($error); ?></span>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Username</label>
        <div class="relative">
          <input type="text" id="loginUsername" name="username" placeholder="Enter admin username" required class="custom-input pl-10 text-sm">
          <span class="absolute left-3.5 top-3 text-gray-400 text-sm">👤</span>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Password</label>
        <div class="relative">
          <input type="password" id="loginPassword" name="password" placeholder="Enter password" required class="custom-input pl-10 pr-10 text-sm">
          <span class="absolute left-3.5 top-3 text-gray-400 text-sm">🔒</span>
          <button type="button" id="togglePasswordBtn" class="absolute right-3 top-2.5 text-gray-400 hover:text-white text-xs p-1" title="Show/Hide Password">
            👁️
          </button>
        </div>
      </div>

      <!-- Quick Demo Credentials Chip -->
      <div class="flex items-center justify-between pt-1">
        <button type="button" id="autoFillBtn" class="text-[11px] font-semibold text-amber-400 hover:text-amber-300 flex items-center gap-1.5 transition">
          <span>⚡ Auto-fill Default (admin / admin123)</span>
        </button>
      </div>

      <button type="submit" class="btn-primary w-full py-3 text-sm mt-2">
        <span>Sign In to Dashboard</span>
        <span>→</span>
      </button>
    </form>

    <!-- Footer Note -->
    <div class="mt-6 pt-5 border-t border-white/10 text-center text-xs text-gray-500">
      Authorized personnel only • Mess Committee Panel
    </div>

  </div>
</div>

<!-- Bottom Spacer -->
<div class="py-2 text-center text-xs text-gray-600 relative z-10">
  Campus Mess Management System
</div>

<script src="assets/app.js"></script>
<script>
  // Password Visibility Toggle
  const toggleBtn = document.getElementById('togglePasswordBtn');
  const passInput = document.getElementById('loginPassword');
  if (toggleBtn && passInput) {
    toggleBtn.addEventListener('click', () => {
      const isPass = passInput.type === 'password';
      passInput.type = isPass ? 'text' : 'password';
      toggleBtn.textContent = isPass ? '🙈' : '👁️';
    });
  }

  // Quick Auto-fill button
  const autoFillBtn = document.getElementById('autoFillBtn');
  if (autoFillBtn) {
    autoFillBtn.addEventListener('click', () => {
      document.getElementById('loginUsername').value = 'admin';
      document.getElementById('loginPassword').value = 'admin123';
      autoFillBtn.classList.add('text-emerald-400');
      autoFillBtn.innerHTML = '<span>✓ Credentials Loaded</span>';
      setTimeout(() => {
        autoFillBtn.classList.remove('text-emerald-400');
        autoFillBtn.innerHTML = '<span>⚡ Auto-fill Default (admin / admin123)</span>';
      }, 2000);
    });
  }
</script>
</body>
</html>
