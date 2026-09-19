<?php
/**
 * admin_nutrition.php
 * Admin page to Add / Edit / Delete food nutrition information.
 * Matches the existing site's glassmorphic dark theme exactly.
 */
include 'config.php';
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit; }

$meal_types = ['Any','Breakfast','Lunch','Snacks','Dinner'];

// Pre-fill form if editing
$edit_row = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $s = $conn->prepare("SELECT * FROM nutrition_info WHERE id=?");
    $s->bind_param("i", $id);
    $s->execute();
    $edit_row = $s->get_result()->fetch_assoc();
}

// Fetch all nutrition records
$records = $conn->query("SELECT * FROM nutrition_info ORDER BY meal_type, food_name")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-theme="sunset">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nutrition Management – Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body class="min-h-screen">

<div class="bg-ambient-orb bg-ambient-orb-1"></div>
<div class="bg-ambient-orb bg-ambient-orb-2"></div>

<!-- NAVBAR — identical pattern to admin_dashboard.php -->
<nav class="glass-nav px-4 lg:px-8 py-3.5">
  <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-xl shadow-lg shadow-emerald-500/30">
        🥗
      </div>
      <div>
        <h1 class="text-lg font-bold tracking-tight leading-none text-white">Nutrition Manager</h1>
        <p class="text-xs text-gray-400 font-medium tracking-wide uppercase mt-0.5">Food Info Panel</p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <!-- Theme Dropdown -->
      <div class="theme-dropdown">
        <button id="themeDropdownBtn" type="button" class="btn-outline text-xs py-2 px-3">
          <span>🎨</span><span id="activeThemeLabel" class="hidden sm:inline">Theme</span>
        </button>
        <div id="themeDropdownMenu" class="theme-dropdown-menu">
          <button type="button" class="theme-opt-btn" data-set-theme="sunset"><span class="color-dot" style="background:linear-gradient(135deg,#ff5e3a,#ff9500)"></span>Sunset Ember</button>
          <button type="button" class="theme-opt-btn" data-set-theme="midnight"><span class="color-dot" style="background:linear-gradient(135deg,#8b5cf6,#3b82f6)"></span>Midnight Neon</button>
          <button type="button" class="theme-opt-btn" data-set-theme="emerald"><span class="color-dot" style="background:linear-gradient(135deg,#10b981,#14b8a6)"></span>Fresh Emerald</button>
          <button type="button" class="theme-opt-btn" data-set-theme="ocean"><span class="color-dot" style="background:linear-gradient(135deg,#0ea5e9,#0284c7)"></span>Ocean Azure</button>
          <button type="button" class="theme-opt-btn" data-set-theme="light"><span class="color-dot" style="background:linear-gradient(135deg,#f97316,#ea580c);border:1px solid #ccc"></span>Light Luxe</button>
        </div>
      </div>
      <a href="admin_dashboard.php" class="btn-outline text-xs py-2 px-3">← Dashboard</a>
      <a href="logout.php" class="px-3 py-2 rounded-xl text-xs font-semibold bg-red-500/20 text-red-300 border border-red-500/30 hover:bg-red-500/30 transition">Logout 🚪</a>
    </div>
  </div>
</nav>

<main class="max-w-7xl mx-auto px-4 lg:px-8 py-8 relative z-10 space-y-8">

  <!-- Toast Notification -->
  <?php if (isset($_GET['msg'])): ?>
    <div id="toastNotification" class="toast-notification">
      <?php if ($_GET['msg'] === 'saved'): ?>
        <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</div>
        <div><h4 class="font-bold text-sm text-white">Nutrition Info Saved!</h4><p class="text-xs text-gray-300">Record updated on the student nutrition page.</p></div>
      <?php elseif ($_GET['msg'] === 'deleted'): ?>
        <div class="w-8 h-8 rounded-full bg-red-500/20 text-red-400 flex items-center justify-center font-bold">🗑️</div>
        <div><h4 class="font-bold text-sm text-white">Entry Deleted</h4><p class="text-xs text-gray-300">Nutrition record removed.</p></div>
      <?php elseif ($_GET['msg'] === 'error'): ?>
        <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center">⚠️</div>
        <div><h4 class="font-bold text-sm text-white">Validation Error</h4><p class="text-xs text-gray-300">Food name is required.</p></div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- ADD / EDIT FORM -->
  <div class="glass-card p-6" id="nutritionFormSection">
    <div class="flex items-center justify-between mb-5">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center"><?php echo $edit_row ? '✏️' : '➕'; ?></div>
        <div>
          <h2 class="text-lg font-bold text-white"><?php echo $edit_row ? 'Edit Nutrition Entry' : 'Add Nutrition Info'; ?></h2>
          <p class="text-xs text-gray-400">All nutritional values per serving size specified</p>
        </div>
      </div>
      <?php if ($edit_row): ?>
        <a href="admin_nutrition.php" class="text-xs text-gray-400 hover:text-white px-3 py-1.5 rounded-lg bg-white/5 border border-white/10 transition">✕ Cancel</a>
      <?php endif; ?>
    </div>

    <form action="admin_save_nutrition.php" method="POST" class="space-y-4">
      <?php if ($edit_row): ?><input type="hidden" name="id" value="<?php echo $edit_row['id']; ?>"><?php endif; ?>

      <!-- Row 1: Food name + Meal type -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Food / Dish Name *</label>
          <input type="text" name="food_name" required placeholder="e.g. Paneer Butter Masala"
                 value="<?php echo $edit_row ? htmlspecialchars($edit_row['food_name']) : ''; ?>"
                 class="custom-input">
        </div>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Meal Type</label>
          <select name="meal_type" class="custom-select">
            <?php foreach ($meal_types as $m): ?>
              <option value="<?php echo $m; ?>" <?php echo ($edit_row && $edit_row['meal_type'] === $m) ? 'selected' : ''; ?>><?php echo $m; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Row 2: Macros -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php
        $macro_fields = [
          'calories'      => ['Calories (kcal)', '200'],
          'protein'       => ['Protein (g)',      '5.0'],
          'carbohydrates' => ['Carbs (g)',         '30.0'],
          'fat'           => ['Fat (g)',           '3.0'],
          'fiber'         => ['Fiber (g)',         '2.0'],
        ];
        foreach ($macro_fields as $fname => [$label, $placeholder]):
          $val = $edit_row ? $edit_row[$fname] : '';
        ?>
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5"><?php echo $label; ?></label>
          <input type="number" name="<?php echo $fname; ?>" step="0.1" min="0" placeholder="<?php echo $placeholder; ?>"
                 value="<?php echo $val; ?>" class="custom-input">
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Row 3: Serving size + description -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Serving Size</label>
          <input type="text" name="serving_size" placeholder="e.g. 1 bowl (200g)"
                 value="<?php echo $edit_row ? htmlspecialchars($edit_row['serving_size']) : ''; ?>"
                 class="custom-input">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Description (Optional)</label>
          <input type="text" name="description" placeholder="Short note about this dish..."
                 value="<?php echo $edit_row ? htmlspecialchars($edit_row['description']) : ''; ?>"
                 class="custom-input">
        </div>
      </div>

      <div class="pt-1">
        <button type="submit" class="btn-primary py-2.5 px-6 text-sm">
          <?php echo $edit_row ? '💾 Update Entry' : '✨ Add Nutrition Info'; ?>
        </button>
      </div>
    </form>
  </div>

  <!-- NUTRITION TABLE -->
  <div class="glass-card p-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
      <div>
        <h2 class="text-lg font-bold text-white flex items-center gap-2">🥦 Nutrition Database</h2>
        <p class="text-xs text-gray-400"><?php echo count($records); ?> food items tracked</p>
      </div>
      <input type="text" id="nutriSearchInput" placeholder="🔍 Search foods..." class="custom-input text-xs py-2 w-full sm:w-60">
    </div>

    <div class="overflow-x-auto rounded-xl border border-white/10">
      <table class="custom-table" id="nutriTable">
        <thead>
          <tr>
            <th>Food Item</th>
            <th>Meal</th>
            <th class="text-center">Cal</th>
            <th class="text-center">Protein</th>
            <th class="text-center">Carbs</th>
            <th class="text-center">Fat</th>
            <th class="text-center">Fiber</th>
            <th>Serving</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="nutriTableBody">
          <?php if (empty($records)): ?>
            <tr><td colspan="9" class="text-center py-6 text-xs text-gray-500 italic">No nutrition data yet. Add the first entry above!</td></tr>
          <?php else: ?>
            <?php foreach ($records as $r): ?>
              <tr class="nutri-row">
                <td class="font-semibold text-white"><?php echo htmlspecialchars($r['food_name']); ?></td>
                <td>
                  <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-white/5 border border-white/10">
                    <?php echo $r['meal_type']; ?>
                  </span>
                </td>
                <td class="text-center font-bold text-amber-400"><?php echo $r['calories']; ?></td>
                <td class="text-center text-blue-300"><?php echo $r['protein']; ?>g</td>
                <td class="text-center text-green-300"><?php echo $r['carbohydrates']; ?>g</td>
                <td class="text-center text-rose-300"><?php echo $r['fat']; ?>g</td>
                <td class="text-center text-purple-300"><?php echo $r['fiber']; ?>g</td>
                <td class="text-xs text-gray-400"><?php echo htmlspecialchars($r['serving_size']); ?></td>
                <td class="text-right space-x-2 whitespace-nowrap">
                  <a href="admin_nutrition.php?edit=<?php echo $r['id']; ?>#nutritionFormSection"
                     class="px-2.5 py-1 text-xs rounded-lg font-semibold bg-blue-500/20 text-blue-300 border border-blue-500/30 hover:bg-blue-500/30 transition">Edit</a>
                  <a href="admin_delete_nutrition.php?id=<?php echo $r['id']; ?>"
                     onclick="return confirm('Delete <?php echo addslashes($r['food_name']); ?>?')"
                     class="px-2.5 py-1 text-xs rounded-lg font-semibold bg-red-500/20 text-red-300 border border-red-500/30 hover:bg-red-500/30 transition">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<script src="assets/app.js"></script>
<script>
  // Search filter for nutrition table
  document.getElementById('nutriSearchInput').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#nutriTableBody .nutri-row').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
</script>
</body>
</html>
