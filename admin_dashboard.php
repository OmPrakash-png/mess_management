<?php
include 'config.php';
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$meal_types = ['Breakfast','Lunch','Snacks','Dinner'];
$meal_icons = ['Breakfast'=>'🌅','Lunch'=>'🍛','Snacks'=>'☕','Dinner'=>'🌙'];

// If editing, pre-fill form
$edit_row = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM menu WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $edit_row = $stmt->get_result()->fetch_assoc();
}

// Calculate KPI Metrics
$total_menu_count = $conn->query("SELECT COUNT(*) as cnt FROM menu")->fetch_assoc()['cnt'] ?? 0;
$total_feedback_count = $conn->query("SELECT COUNT(*) as cnt FROM feedback")->fetch_assoc()['cnt'] ?? 0;
$avg_rating_res = $conn->query("SELECT AVG(rating) as avg_r FROM feedback")->fetch_assoc();
$avg_rating = $avg_rating_res['avg_r'] ? round($avg_rating_res['avg_r'], 1) : 0;
$special_count = $conn->query("SELECT COUNT(*) as cnt FROM menu WHERE is_special=1")->fetch_assoc()['cnt'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="sunset">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Mess Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="min-h-screen">

<!-- Ambient Glowing Orbs -->
<div class="bg-ambient-orb bg-ambient-orb-1"></div>
<div class="bg-ambient-orb bg-ambient-orb-2"></div>

<!-- ==========================================
     ADMIN GLASS NAVIGATION BAR
     ========================================== -->
<nav class="glass-nav px-4 lg:px-8 py-3.5">
  <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-600 flex items-center justify-center text-xl shadow-lg shadow-orange-500/30">
        ⚙️
      </div>
      <div>
        <h1 class="text-lg font-bold tracking-tight leading-none text-white">Admin Command Center</h1>
        <p class="text-xs text-gray-400 font-medium tracking-wide uppercase mt-0.5">Mess Management System</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-3">
      <!-- Theme Dropdown -->
      <div class="theme-dropdown">
        <button id="themeDropdownBtn" type="button" class="btn-outline text-xs py-2 px-3">
          <span>🎨</span>
          <span id="activeThemeLabel" class="hidden sm:inline">Theme</span>
          <svg class="w-3 h-3 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
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

      <!-- Nutrition Manager -->
      <a href="admin_nutrition.php" class="btn-outline text-xs py-2 px-3">
        <span>🥗</span>
        <span class="hidden sm:inline">Nutrition</span>
      </a>

      <!-- View Student Page -->
      <a href="index.php" target="_blank" class="btn-outline text-xs py-2 px-3">
        <span>👁️</span>
        <span class="hidden sm:inline">Student View</span>
      </a>

      <!-- Logout -->
      <a href="logout.php" class="px-3 py-2 rounded-xl text-xs font-semibold bg-red-500/20 text-red-300 border border-red-500/30 hover:bg-red-500/30 transition">
        Logout 🚪
      </a>
    </div>
  </div>
</nav>

<!-- ==========================================
     MAIN DASHBOARD CONTENT
     ========================================== -->
<main class="max-w-7xl mx-auto px-4 lg:px-8 py-8 relative z-10 space-y-8">

  <!-- Toast Notification on Save / Delete -->
  <?php if (isset($_GET['msg'])): ?>
    <div id="toastNotification" class="toast-notification">
      <?php if ($_GET['msg'] === 'saved'): ?>
        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</div>
        <div>
          <h4 class="font-bold text-sm text-white">Menu Updated!</h4>
          <p class="text-xs text-gray-300">The meal roster changes are now live for students.</p>
        </div>
      <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <div class="w-8 h-8 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center font-bold">🗑️</div>
        <div>
          <h4 class="font-bold text-sm text-white">Item Removed</h4>
          <p class="text-xs text-gray-300">The selected menu item was successfully deleted.</p>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- ==========================================
       TOP METRICS KPI CARDS
       ========================================== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Meals -->
    <div class="glass-card p-5 anim-fade-up delay-1">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Menu Slots</p>
          <h3 class="text-3xl font-extrabold text-white mt-1"><?php echo $total_menu_count; ?></h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-2xl">
          🍲
        </div>
      </div>
      <p class="text-[11px] text-gray-400 mt-3 flex items-center gap-1">
        <span class="text-emerald-400">Active</span> across 7 days a week
      </p>
    </div>

    <!-- Student Reviews -->
    <div class="glass-card p-5 anim-fade-up delay-2">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Reviews Received</p>
          <h3 class="text-3xl font-extrabold text-white mt-1"><?php echo $total_feedback_count; ?></h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-purple-500/15 border border-purple-500/30 flex items-center justify-center text-2xl">
          💬
        </div>
      </div>
      <p class="text-[11px] text-gray-400 mt-3 flex items-center gap-1">
        <span class="text-purple-400">Student</span> voice & ratings
      </p>
    </div>

    <!-- Avg Rating -->
    <div class="glass-card p-5 anim-fade-up delay-3">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Average Rating</p>
          <h3 class="text-3xl font-extrabold text-amber-400 mt-1">
            <?php echo $avg_rating > 0 ? $avg_rating . " / 5" : "N/A"; ?>
          </h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-yellow-500/15 border border-yellow-500/30 flex items-center justify-center text-2xl">
          ⭐
        </div>
      </div>
      <p class="text-[11px] text-gray-400 mt-3 flex items-center gap-1">
        Based on real student feedback
      </p>
    </div>

    <!-- Special Notices -->
    <div class="glass-card p-5 anim-fade-up delay-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Special Notices</p>
          <h3 class="text-3xl font-extrabold text-white mt-1"><?php echo $special_count; ?></h3>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-2xl">
          📢
        </div>
      </div>
      <p class="text-[11px] text-gray-400 mt-3 flex items-center gap-1">
        Highlighted substitutions
      </p>
    </div>
  </div>

  <!-- ==========================================
       SECTION: ADD OR EDIT MENU ITEM FORM
       ========================================== -->
  <div class="glass-card p-6 anim-fade-up" id="menuFormSection">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-base">
          <?php echo $edit_row ? '✏️' : '➕'; ?>
        </div>
        <div>
          <h2 class="text-lg font-bold text-white">
            <?php echo $edit_row ? 'Edit Menu Entry' : 'Add / Update Menu Slot'; ?>
          </h2>
          <p class="text-xs text-gray-400">Changes immediately reflect on student devices and timetable</p>
        </div>
      </div>

      <?php if ($edit_row): ?>
        <a href="admin_dashboard.php" class="text-xs text-gray-400 hover:text-white px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 transition">
          ✕ Cancel Edit
        </a>
      <?php endif; ?>
    </div>

    <form action="admin_save_menu.php" method="POST" class="space-y-4">
      <?php if ($edit_row): ?>
        <input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>">
      <?php endif; ?>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Day of Week</label>
          <select name="day_of_week" required class="custom-select">
            <?php foreach ($days as $d): 
              $sel = ($edit_row && $edit_row['day_of_week'] == $d) ? 'selected' : '';
            ?>
              <option value="<?php echo $d; ?>" <?php echo $sel; ?>><?php echo $d; ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Meal Type</label>
          <select name="meal_type" required class="custom-select">
            <?php foreach ($meal_types as $m): 
              $sel = ($edit_row && $edit_row['meal_type'] == $m) ? 'selected' : '';
            ?>
              <option value="<?php echo $m; ?>" <?php echo $sel; ?>>
                <?php echo $meal_icons[$m]; ?> <?php echo $m; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Menu Dishes (Comma separated)</label>
        <textarea name="item_name" placeholder="e.g. Paneer Butter Masala, Jeera Rice, Tandoori Roti, Gulab Jamun" required class="custom-textarea"><?php echo $edit_row ? htmlspecialchars($edit_row['item_name']) : ''; ?></textarea>
      </div>

      <!-- Special Notice Checkbox / Toggle -->
      <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
        <input type="checkbox" id="isSpecialCheck" name="is_special" value="1" <?php echo ($edit_row && $edit_row['is_special']) ? 'checked' : ''; ?> class="w-4 h-4 text-amber-500 rounded border-gray-600 focus:ring-amber-500">
        <label for="isSpecialCheck" class="text-xs font-medium text-gray-200 cursor-pointer">
          <strong class="text-amber-300">Mark as Special or Substitution:</strong> Displays a glowing announcement banner on the student portal.
        </label>
      </div>

      <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="btn-primary py-2.5 px-6 text-sm">
          <span><?php echo $edit_row ? '💾 Update Menu Item' : '✨ Save Menu Item'; ?></span>
        </button>
      </div>
    </form>
  </div>

  <!-- ==========================================
       SECTION: CURRENT MENU ROSTER TABLE
       ========================================== -->
  <div class="glass-card p-6 anim-fade-up">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
      <div>
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
          <span>📋</span> Configured Menu Schedule
        </h2>
        <p class="text-xs text-gray-400">All registered meal slots sorted by day and time</p>
      </div>

      <!-- Live Search / Filter -->
      <div class="w-full sm:w-64">
        <input type="text" id="adminMenuSearch" placeholder="🔍 Search menu items..." class="custom-input text-xs py-2">
      </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-white/10">
      <table class="custom-table">
        <thead>
          <tr>
            <th class="w-28">Day</th>
            <th class="w-28">Meal</th>
            <th>Menu Items</th>
            <th class="w-24 text-center">Special</th>
            <th class="w-28 text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="adminMenuTableBody">
          <?php
          $result = $conn->query("SELECT * FROM menu ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), FIELD(meal_type,'Breakfast','Lunch','Snacks','Dinner')");
          if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
          ?>
            <tr class="menu-row">
              <td class="font-bold text-white whitespace-nowrap"><?php echo $row['day_of_week']; ?></td>
              <td class="whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs bg-white/5 border border-white/10">
                  <?php echo $meal_icons[$row['meal_type']] ?? '🍽️'; ?> <?php echo $row['meal_type']; ?>
                </span>
              </td>
              <td class="text-xs text-gray-300">
                <div class="flex flex-wrap gap-1">
                  <?php 
                  $parts = explode(',', $row['item_name']);
                  foreach ($parts as $p):
                    $p = trim($p);
                    if (empty($p)) continue;
                  ?>
                    <span class="px-2 py-0.5 rounded bg-white/5 text-[11px] text-gray-200 border border-white/5">
                      <?php echo htmlspecialchars($p); ?>
                    </span>
                  <?php endforeach; ?>
                </div>
              </td>
              <td class="text-center whitespace-nowrap">
                <?php if ($row['is_special']): ?>
                  <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                    ★ Yes
                  </span>
                <?php else: ?>
                  <span class="text-gray-500 text-xs">-</span>
                <?php endif; ?>
              </td>
              <td class="text-right whitespace-nowrap space-x-2">
                <a href="admin_dashboard.php?edit=<?php echo $row['id']; ?>#menuFormSection" class="px-2.5 py-1 text-xs rounded-lg font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30 hover:bg-blue-500/30 transition">
                  Edit
                </a>
                <a href="admin_delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this menu slot (<?php echo $row['day_of_week'] . ' ' . $row['meal_type']; ?>)?')" class="px-2.5 py-1 text-xs rounded-lg font-semibold bg-red-500/20 text-red-300 border border-red-500/30 hover:bg-red-500/30 transition">
                  Delete
                </a>
              </td>
            </tr>
          <?php 
            endwhile;
          else: 
          ?>
            <tr>
              <td colspan="5" class="text-center py-6 text-xs text-gray-500 italic">No menu items found. Add one above!</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ==========================================
       SECTION: STUDENT FEEDBACK & REVIEWS
       ========================================== -->
  <div class="glass-card p-6 anim-fade-up">
    <div class="flex items-center justify-between mb-5">
      <div>
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
          <span>🌟</span> Student Reviews & Quality Reports
        </h2>
        <p class="text-xs text-gray-400">Incoming feedback submitted through the student portal</p>
      </div>
      <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/5 border border-white/10 text-gray-300">
        <?php echo $total_feedback_count; ?> Total Reviews
      </span>
    </div>

    <div class="overflow-x-auto rounded-xl border border-white/10">
      <table class="custom-table">
        <thead>
          <tr>
            <th>Student</th>
            <th>Day & Meal</th>
            <th>Rating</th>
            <th>Comment / Suggestions</th>
            <th class="text-right">Received</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $fb = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
          if ($fb && $fb->num_rows > 0):
            while ($row = $fb->fetch_assoc()):
          ?>
            <tr>
              <td class="font-bold text-white whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center text-xs">
                    👤
                  </div>
                  <span><?php echo htmlspecialchars($row['student_name'] ?: 'Anonymous'); ?></span>
                </div>
              </td>
              <td class="whitespace-nowrap text-xs text-gray-300">
                <?php echo $row['day_of_week']; ?> • <span class="text-amber-400 font-medium"><?php echo $row['meal_type']; ?></span>
              </td>
              <td class="whitespace-nowrap">
                <span class="text-amber-400 text-sm">
                  <?php echo str_repeat('★', $row['rating']); ?><span class="text-gray-600"><?php echo str_repeat('★', 5 - $row['rating']); ?></span>
                </span>
                <span class="text-[11px] text-gray-400 ml-1 font-bold">(<?php echo $row['rating']; ?>/5)</span>
              </td>
              <td class="text-xs text-gray-300 max-w-md">
                <?php if (!empty($row['comment'])): ?>
                  "<?php echo htmlspecialchars($row['comment']); ?>"
                <?php else: ?>
                  <span class="text-gray-500 italic">No comment provided</span>
                <?php endif; ?>
              </td>
              <td class="text-right text-[11px] text-gray-400 whitespace-nowrap">
                <?php echo date('d M, h:i A', strtotime($row['created_at'])); ?>
              </td>
            </tr>
          <?php 
            endwhile;
          else: 
          ?>
            <tr>
              <td colspan="5" class="text-center py-6 text-xs text-gray-500 italic">No student reviews recorded yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<script src="assets/app.js"></script>
<script>
  // Filter menu roster in admin
  const adminSearch = document.getElementById('adminMenuSearch');
  const adminRows = document.querySelectorAll('#adminMenuTableBody .menu-row');

  if (adminSearch) {
    adminSearch.addEventListener('input', (e) => {
      const q = e.target.value.toLowerCase().trim();
      adminRows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = (q === '' || text.includes(q)) ? '' : 'none';
      });
    });
  }
</script>
</body>
</html>
