<?php 
include 'config.php'; 
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$meal_types = ['Breakfast', 'Lunch', 'Snacks', 'Dinner'];
$meal_icons = ['Breakfast'=>'🌅','Lunch'=>'🍛','Snacks'=>'☕','Dinner'=>'🌙'];
$meal_timings = [
  'Breakfast' => '07:30 AM - 10:00 AM',
  'Lunch' => '12:30 PM - 03:00 PM',
  'Snacks' => '04:30 PM - 06:00 PM',
  'Dinner' => '08:00 PM - 10:00 PM'
];

$today = date('l');
$current_date_formatted = date('l, d M Y');

// ── Data for new sections ────────────────────────────────────
// Food Preferences (Veg / Non-Veg)
$pref_counts = ['veg' => 0, 'non-veg' => 0];
$pref_res = $conn->query("SELECT preference, COUNT(*) AS cnt FROM food_preferences GROUP BY preference");
while ($pr = $pref_res->fetch_assoc()) { $pref_counts[$pr['preference']] = (int)$pr['cnt']; }
$pref_total   = $pref_counts['veg'] + $pref_counts['non-veg'];
$veg_pct      = $pref_total > 0 ? round($pref_counts['veg']       / $pref_total * 100) : 0;
$nonveg_pct   = $pref_total > 0 ? round($pref_counts['non-veg']   / $pref_total * 100) : 0;

// Most Loved Foods — top 10 by likes
$loved_foods = $conn->query(
    "SELECT food_name, meal_type, likes_count,
            CASE WHEN rating_count > 0 THEN ROUND(total_rating/rating_count,1) ELSE 0 END AS avg_rating
     FROM food_likes ORDER BY likes_count DESC LIMIT 10"
)->fetch_all(MYSQLI_ASSOC);

// Nutrition info — all records
$nutrition_records = $conn->query(
    "SELECT * FROM nutrition_info ORDER BY meal_type, food_name"
)->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" data-theme="sunset">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Mess &amp; Dining Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/style.css">
  <!-- Chart.js for Veg/Non-Veg pie chart -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>

<!-- Ambient Glowing Orbs -->
<div class="bg-ambient-orb bg-ambient-orb-1"></div>
<div class="bg-ambient-orb bg-ambient-orb-2"></div>

<!-- ==========================================
     GLASS NAVIGATION BAR
     ========================================== -->
<nav class="glass-nav px-4 lg:px-8 py-3.5">
  <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-4">
    <!-- Brand -->
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-orange-500 to-amber-400 flex items-center justify-center text-xl shadow-lg shadow-orange-500/30">
        🍽️
      </div>
      <div>
        <h1 class="text-lg font-bold tracking-tight leading-none text-white">Campus Mess Hub</h1>
        <p class="text-xs text-gray-400 font-medium tracking-wide uppercase mt-0.5">Daily Nutrition & Menu</p>
      </div>
    </div>

    <!-- Live Clock & Status Badge -->
    <div class="hidden sm:flex items-center gap-4 text-xs font-semibold text-gray-300">
      <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 border border-white/10">
        <span>📅 <?php echo $current_date_formatted; ?></span>
        <span class="text-gray-500">•</span>
        <span id="liveClock" class="font-mono text-amber-400 font-bold">--:--:--</span>
      </div>
    </div>

    <!-- Actions: Theme Switcher & Admin Link -->
    <div class="flex items-center gap-3">
      <!-- Theme Dropdown -->
      <div class="theme-dropdown">
        <button id="themeDropdownBtn" type="button" class="btn-outline text-xs py-2 px-3">
          <span class="text-base">🎨</span>
          <span id="activeThemeLabel" class="hidden sm:inline">Theme</span>
          <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
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
            <span class="color-dot" style="background: linear-gradient(135deg, #f97316, #ea580c); border: 1px solid #ccc;"></span>
            <span>Light Luxe</span>
          </button>
        </div>
      </div>

      <!-- Nutrition Page Link -->
      <a href="#section-nutrition" class="btn-outline text-xs py-2 px-3 hidden sm:inline-flex">
        <span>🥗</span>
        <span>Nutrition</span>
      </a>

      <!-- Admin Login -->
      <a href="admin_login.php" class="btn-primary text-xs py-2 px-3.5">
        <span>🔐</span>
        <span>Admin Login</span>
      </a>
    </div>
  </div>
</nav>

<!-- ==========================================
     MAIN CONTENT WRAPPER
     ========================================== -->
<main class="max-w-7xl mx-auto px-4 lg:px-8 py-8 relative z-10 space-y-10">

  <!-- Feedback Success Toast Notification -->
  <?php if (isset($_GET['feedback']) && $_GET['feedback'] === 'success'): ?>
    <div id="toastNotification" class="toast-notification">
      <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-lg border border-emerald-500/40">
        ✓
      </div>
      <div>
        <h4 class="font-bold text-sm text-white">Feedback Submitted!</h4>
        <p class="text-xs text-gray-300">Thank you for helping us elevate the dining quality.</p>
      </div>
    </div>
  <?php endif; ?>

  <!-- Special Announcements / Substitutions Banner -->
  <?php
  $special = $conn->query("SELECT * FROM menu WHERE is_special = 1 ORDER BY updated_at DESC LIMIT 5");
  if ($special && $special->num_rows > 0):
  ?>
    <div class="special-banner anim-fade-up">
      <div class="flex items-start gap-3.5">
        <span class="text-2xl animate-bounce">📢</span>
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <h3 class="font-bold text-amber-300 text-sm tracking-wide uppercase">Today's Special / Menu Notice</h3>
            <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-amber-500/30 text-amber-200 border border-amber-500/40">Active</span>
          </div>
          <div class="mt-1 space-y-1 text-sm text-amber-100">
            <?php while ($row = $special->fetch_assoc()): ?>
              <p class="leading-relaxed">
                <strong class="text-amber-200"><?php echo htmlspecialchars($row['day_of_week']); ?> (<?php echo htmlspecialchars($row['meal_type']); ?>):</strong> 
                <?php echo htmlspecialchars($row['item_name']); ?>
              </p>
            <?php endwhile; ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- ==========================================
       SECTION 1: TODAY'S LIVE MENU
       ========================================== -->
  <section class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <div class="flex items-center gap-2.5">
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Today's Menu</h2>
          <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 border border-white/15 text-amber-400">
            <?php echo $today; ?>
          </span>
        </div>
        <p class="text-sm text-gray-400 mt-1">Freshly prepared meals served at the dining hall</p>
      </div>

      <!-- Real-time Meal Status Pill -->
      <div id="liveMealStatusText" class="self-start sm:self-auto">
        <span class="pulse-badge"><span class="pulse-dot"></span> Checking Status...</span>
      </div>
    </div>

    <!-- 4 Meal Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      <?php
      $delay_classes = ['delay-1', 'delay-2', 'delay-3', 'delay-4'];
      $idx = 0;
      foreach ($meal_types as $meal):
          $stmt = $conn->prepare("SELECT * FROM menu WHERE day_of_week = ? AND meal_type = ?");
          $stmt->bind_param("ss", $today, $meal);
          $stmt->execute();
          $res = $stmt->get_result();
          $row = $res->fetch_assoc();
          $delay = $delay_classes[$idx % 4];
          $idx++;
      ?>
        <div class="glass-card p-5 anim-fade-up <?php echo $delay; ?>" data-meal-card="<?php echo $meal; ?>">
          <!-- Header -->
          <div class="flex items-center justify-between mb-3.5">
            <div class="flex items-center gap-2.5">
              <span class="text-2xl p-2 rounded-xl bg-white/5 border border-white/10 shadow-inner">
                <?php echo $meal_icons[$meal]; ?>
              </span>
              <div>
                <h3 class="font-bold text-base text-white"><?php echo $meal; ?></h3>
                <span class="text-[11px] font-medium text-gray-400 block"><?php echo $meal_timings[$meal]; ?></span>
              </div>
            </div>
            <?php if ($row && $row['is_special']): ?>
              <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-yellow-500/20 text-yellow-300 border border-yellow-500/30">
                Special
              </span>
            <?php endif; ?>
          </div>

          <!-- Divider -->
          <div class="w-full h-px bg-white/10 my-3"></div>

          <!-- Items list -->
          <div class="min-h-[85px] flex flex-col justify-center">
            <?php if ($row && !empty(trim($row['item_name']))): ?>
              <div class="flex flex-wrap gap-1.5">
                <?php 
                $items = explode(',', $row['item_name']);
                foreach ($items as $itm):
                  $itm_clean = trim($itm);
                  if (empty($itm_clean)) continue;
                ?>
                  <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-medium bg-white/5 border border-white/10 text-gray-200">
                    <?php echo htmlspecialchars($itm_clean); ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-xs text-gray-500 italic py-2">Menu not updated yet for this meal</p>
            <?php endif; ?>
          </div>

          <!-- Bottom Footer -->
          <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between text-[11px] text-gray-400">
            <span>Dining Hall A</span>
            <span class="text-emerald-400 flex items-center gap-1 font-medium">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Live
            </span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ==========================================
       SECTION 2: WEEKLY TIMETABLE
       ========================================== -->
  <section class="glass-card p-6 anim-fade-up">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <div>
        <h2 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
          <span>📅</span> Weekly Timetable & Planner
        </h2>
        <p class="text-xs text-gray-400 mt-0.5">Filter by day or search for specific dishes</p>
      </div>

      <!-- Quick Search Bar -->
      <div class="w-full md:w-72 relative">
        <input type="text" id="menuSearchInput" placeholder="🔍 Search dishes (e.g. Paneer)..." class="custom-input text-xs py-2 pl-3 pr-8">
      </div>
    </div>

    <!-- Day Filter Tabs -->
    <div class="day-tabs mb-5">
      <button type="button" class="day-tab-btn active" data-day="all">All Days</button>
      <?php foreach ($days as $d): ?>
        <button type="button" class="day-tab-btn <?php echo ($d === $today) ? 'border-amber-500/50' : ''; ?>" data-day="<?php echo $d; ?>">
          <?php echo ($d === $today) ? "★ $d (Today)" : $d; ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Timetable -->
    <div class="overflow-x-auto rounded-xl border border-white/10">
      <table class="custom-table">
        <thead>
          <tr>
            <th class="w-28">Day</th>
            <th>🌅 Breakfast</th>
            <th>🍛 Lunch</th>
            <th>☕ Snacks</th>
            <th>🌙 Dinner</th>
          </tr>
        </thead>
        <tbody id="weeklyTableBody">
          <?php
          foreach ($days as $day):
              $is_today = ($day === $today);
              $row_class = $is_today ? 'highlight-today' : '';
          ?>
            <tr class="<?php echo $row_class; ?>" data-day="<?php echo $day; ?>">
              <td class="font-bold whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <span><?php echo $day; ?></span>
                  <?php if ($is_today): ?>
                    <span class="px-1.5 py-0.5 text-[9px] font-extrabold uppercase rounded bg-amber-500 text-gray-950">Today</span>
                  <?php endif; ?>
                </div>
              </td>
              <?php foreach ($meal_types as $meal):
                  $stmt = $conn->prepare("SELECT item_name, is_special FROM menu WHERE day_of_week=? AND meal_type=?");
                  $stmt->bind_param("ss", $day, $meal);
                  $stmt->execute();
                  $r = $stmt->get_result()->fetch_assoc();
              ?>
                <td class="text-xs">
                  <?php if ($r && !empty(trim($r['item_name']))): ?>
                    <span class="<?php echo $r['is_special'] ? 'text-amber-300 font-semibold' : 'text-gray-300'; ?>">
                      <?php echo htmlspecialchars($r['item_name']); ?>
                    </span>
                    <?php if ($r['is_special']): ?>
                      <span class="ml-1 text-[9px] px-1 py-0.2 rounded bg-amber-500/20 text-amber-300">★</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-gray-600">-</span>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <!-- ==========================================
       SECTION 3: INTERACTIVE STUDENT FEEDBACK
       ========================================== -->
  <section class="glass-card p-6 sm:p-8 anim-fade-up">
    <div class="max-w-2xl mx-auto">
      <div class="text-center mb-8">
        <div class="inline-flex p-3 rounded-2xl bg-gradient-to-tr from-amber-500/20 to-orange-500/20 border border-orange-500/30 text-2xl mb-3 shadow-lg">
          ✍️
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">Student Feedback Portal</h2>
        <p class="text-xs sm:text-sm text-gray-400 mt-1">Rate today's meals or share recommendations directly with the mess committee</p>
      </div>

      <form action="submit_feedback.php" method="POST" class="space-y-5">
        <!-- Name -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Your Name (Optional)</label>
          <input type="text" name="student_name" placeholder="e.g. Rahul Sharma or leave blank for Anonymous" class="custom-input">
        </div>

        <!-- Day and Meal Selection -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Day of Meal</label>
            <select name="day_of_week" required class="custom-select">
              <?php foreach ($days as $day): ?>
                <option value="<?php echo $day; ?>" <?php echo ($day === $today) ? 'selected' : ''; ?>>
                  <?php echo $day; ?><?php echo ($day === $today) ? ' (Today)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Meal Type</label>
            <select name="meal_type" required class="custom-select">
              <?php foreach ($meal_types as $meal): ?>
                <option value="<?php echo $meal; ?>"><?php echo $meal_icons[$meal]; ?> <?php echo $meal; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Star Rating Widget -->
        <div class="p-4 rounded-xl bg-white/5 border border-white/10">
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-2">Overall Food Rating</label>
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="star-rating-group">
              <input type="radio" id="star5" name="rating" value="5" required>
              <label for="star5" title="5 Stars">★</label>
              <input type="radio" id="star4" name="rating" value="4">
              <label for="star4" title="4 Stars">★</label>
              <input type="radio" id="star3" name="rating" value="3" checked>
              <label for="star3" title="3 Stars">★</label>
              <input type="radio" id="star2" name="rating" value="2">
              <label for="star2" title="2 Stars">★</label>
              <input type="radio" id="star1" name="rating" value="1">
              <label for="star1" title="1 Star">★</label>
            </div>
            <div id="ratingSentimentText" class="rating-sentiment">
              😐 3/5 - Average / Acceptable
            </div>
          </div>
        </div>

        <!-- Comments Textarea -->
        <div>
          <label class="block text-xs font-bold uppercase tracking-wider text-gray-300 mb-1.5">Comments or Suggestions</label>
          <textarea name="comment" placeholder="Tell us about the taste, quality, freshness, or any items you would love to see..." class="custom-textarea"></textarea>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <button type="submit" class="btn-primary w-full py-3 text-sm">
            <span>🚀</span>
            <span>Submit Feedback</span>
          </button>
        </div>
      </form>
    </div>
  </section>

  <!-- ==============================================
       SECTION 4: MOST LOVED FOODS 🏆
       ============================================== -->
  <section class="anim-fade-up" id="section-loved">
    <div class="text-center mb-6">
      <div class="inline-flex p-3 rounded-2xl bg-gradient-to-tr from-rose-500/20 to-pink-500/20 border border-rose-500/30 text-2xl mb-3">🏆</div>
      <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Most Loved Foods</h2>
      <p class="text-sm text-gray-400 mt-1">Ranked by student hearts &amp; ratings. Click ❤️ to vote!</p>
    </div>

    <!-- Top 3 Podium Cards -->
    <?php if (!empty($loved_foods)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <?php
      $podium_labels = ['🥇','🥈','🥉'];
      $podium_colors = [
        'from-amber-500/25 to-yellow-500/10 border-amber-400/40',
        'from-slate-400/20 to-gray-500/10 border-gray-400/30',
        'from-orange-700/25 to-amber-800/10 border-orange-600/35',
      ];
      for ($pi = 0; $pi < min(3, count($loved_foods)); $pi++):
        $f = $loved_foods[$pi];
        $avg = $f['avg_rating'] > 0 ? $f['avg_rating'] : 'N/A';
      ?>
        <div class="glass-card p-5 bg-gradient-to-br <?php echo $podium_colors[$pi]; ?> text-center relative overflow-visible">
          <div class="text-4xl mb-2"><?php echo $podium_labels[$pi]; ?></div>
          <h3 class="font-extrabold text-base text-white leading-tight mb-1">
            <?php echo htmlspecialchars($f['food_name']); ?>
          </h3>
          <span class="inline-block px-2 py-0.5 rounded-full text-[11px] bg-white/10 text-gray-300 border border-white/10 mb-3">
            <?php echo $f['meal_type']; ?>
          </span>
          <div class="flex items-center justify-center gap-4 text-sm">
            <div class="text-center">
              <div class="font-extrabold text-xl text-rose-400" id="likes-<?php echo $pi; ?>"><?php echo number_format($f['likes_count']); ?></div>
              <div class="text-[10px] text-gray-400 uppercase tracking-wider">Likes</div>
            </div>
            <?php if ($avg !== 'N/A'): ?>
            <div class="text-center">
              <div class="font-extrabold text-xl text-amber-400"><?php echo $avg; ?>⭐</div>
              <div class="text-[10px] text-gray-400 uppercase tracking-wider">Avg Rating</div>
            </div>
            <?php endif; ?>
          </div>
          <!-- Like button -->
          <button type="button"
                  class="like-btn mt-4 w-full py-2 rounded-xl text-xs font-bold bg-rose-500/20 border border-rose-500/30 text-rose-300 hover:bg-rose-500/35 transition"
                  data-food="<?php echo htmlspecialchars($f['food_name']); ?>"
                  data-meal="<?php echo $f['meal_type']; ?>"
                  data-idx="<?php echo $pi; ?>">
            ❤️ Like this
          </button>
        </div>
      <?php endfor; ?>
    </div>

    <!-- Full Ranked Table -->
    <div class="glass-card p-5">
      <h3 class="text-base font-bold text-white mb-4">📋 Complete Rankings</h3>
      <div class="overflow-x-auto rounded-xl border border-white/10">
        <table class="custom-table">
          <thead>
            <tr>
              <th class="w-12 text-center">#</th>
              <th>Food Item</th>
              <th>Meal</th>
              <th class="text-center">❤️ Likes</th>
              <th class="text-center">⭐ Avg Rating</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($loved_foods as $rank => $f):
              $avg = $f['avg_rating'] > 0 ? $f['avg_rating'] : '—';
            ?>
              <tr>
                <td class="text-center font-extrabold text-gray-400"><?php echo $rank + 1; ?></td>
                <td class="font-semibold text-white"><?php echo htmlspecialchars($f['food_name']); ?></td>
                <td>
                  <span class="px-2 py-0.5 rounded text-[11px] bg-white/5 border border-white/10">
                    <?php echo $f['meal_type']; ?>
                  </span>
                </td>
                <td class="text-center">
                  <span class="font-bold text-rose-400 like-count-cell" data-food="<?php echo htmlspecialchars($f['food_name']); ?>"><?php echo number_format($f['likes_count']); ?></span>
                </td>
                <td class="text-center">
                  <span class="font-bold text-amber-400"><?php echo $avg !== '—' ? $avg.'⭐' : '—'; ?></span>
                </td>
                <td class="text-center">
                  <button type="button"
                          class="like-btn px-3 py-1 text-xs font-bold rounded-lg bg-rose-500/15 border border-rose-500/25 text-rose-300 hover:bg-rose-500/30 transition"
                          data-food="<?php echo htmlspecialchars($f['food_name']); ?>"
                          data-meal="<?php echo $f['meal_type']; ?>"
                          data-idx="">
                    ❤️ Like
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php else: ?>
    <div class="glass-card p-10 text-center">
      <p class="text-4xl mb-3">🍽️</p>
      <p class="text-gray-400 text-sm">No food ratings yet. Be the first to rate a meal!</p>
    </div>
    <?php endif; ?>
  </section>

  <!-- ==============================================
       SECTION 5: VEG vs NON-VEG PREFERENCE 🥦🍗
       ============================================== -->
  <section class="anim-fade-up" id="section-preference">
    <div class="text-center mb-6">
      <div class="inline-flex p-3 rounded-2xl bg-gradient-to-tr from-green-500/20 to-emerald-500/20 border border-green-500/30 text-2xl mb-3">🥦🍗</div>
      <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Student Food Preference</h2>
      <p class="text-sm text-gray-400 mt-1">Cast your vote — helps the mess plan better meals for everyone</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Pie Chart -->
      <div class="glass-card p-6">
        <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">📊 Live Results</h3>
        <div class="flex flex-col sm:flex-row items-center gap-6">
          <div class="relative w-52 h-52 flex-shrink-0">
            <canvas id="prefPieChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
              <span class="text-2xl font-extrabold text-white" id="prefTotalCenter"><?php echo $pref_total; ?></span>
              <span class="text-xs text-gray-400">Students</span>
            </div>
          </div>
          <div class="space-y-4 flex-1">
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-sm font-semibold text-white">🥦 Vegetarian</span>
              </div>
              <div class="text-right">
                <span class="text-xl font-extrabold text-emerald-400" id="vegCount"><?php echo $pref_counts['veg']; ?></span>
                <span class="text-xs text-gray-400 ml-1">students</span>
                <span class="ml-2 px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold" id="vegPct"><?php echo $veg_pct; ?>%</span>
              </div>
            </div>
            <div class="w-full bg-white/5 rounded-full h-2">
              <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2 rounded-full transition-all duration-700" id="vegBar" style="width:<?php echo $veg_pct; ?>%"></div>
            </div>

            <div class="flex items-center justify-between gap-3 mt-2">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                <span class="text-sm font-semibold text-white">🍗 Non-Vegetarian</span>
              </div>
              <div class="text-right">
                <span class="text-xl font-extrabold text-rose-400" id="nonvegCount"><?php echo $pref_counts['non-veg']; ?></span>
                <span class="text-xs text-gray-400 ml-1">students</span>
                <span class="ml-2 px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 text-xs font-bold" id="nonvegPct"><?php echo $nonveg_pct; ?>%</span>
              </div>
            </div>
            <div class="w-full bg-white/5 rounded-full h-2">
              <div class="bg-gradient-to-r from-rose-500 to-orange-400 h-2 rounded-full transition-all duration-700" id="nonvegBar" style="width:<?php echo $nonveg_pct; ?>%"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Vote + Table panel -->
      <div class="space-y-4">
        <!-- Vote Card -->
        <div class="glass-card p-5">
          <h3 class="text-sm font-bold text-white mb-3">🗳️ Cast Your Vote</h3>
          <p class="text-xs text-gray-400 mb-4">Select your food preference. You can vote once per browser session.</p>
          <div id="voteArea" class="grid grid-cols-2 gap-3">
            <button type="button" id="btnVegPref"
                    class="py-4 rounded-xl border-2 border-emerald-500/40 bg-emerald-500/10 hover:bg-emerald-500/20 transition text-center font-bold text-emerald-400 text-sm"
                    onclick="submitPreference('veg')">
              🥦<br>I'm Vegetarian
            </button>
            <button type="button" id="btnNonVegPref"
                    class="py-4 rounded-xl border-2 border-rose-500/40 bg-rose-500/10 hover:bg-rose-500/20 transition text-center font-bold text-rose-400 text-sm"
                    onclick="submitPreference('non-veg')">
              🍗<br>I'm Non-Veg
            </button>
          </div>
          <div id="voteMsg" class="mt-3 text-center text-xs text-gray-400 hidden"></div>
        </div>

        <!-- Summary Table -->
        <div class="glass-card p-5">
          <h3 class="text-sm font-bold text-white mb-3">📋 Summary Table</h3>
          <div class="overflow-x-auto rounded-lg border border-white/10">
            <table class="custom-table text-sm">
              <thead>
                <tr>
                  <th>Preference</th>
                  <th class="text-center">Students</th>
                  <th class="text-center">Percentage</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="font-semibold"><span class="text-emerald-400">🥦 Veg</span></td>
                  <td class="text-center font-bold text-white" id="tableVeg"><?php echo $pref_counts['veg']; ?></td>
                  <td class="text-center"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300" id="tableVegPct"><?php echo $veg_pct; ?>%</span></td>
                </tr>
                <tr>
                  <td class="font-semibold"><span class="text-rose-400">🍗 Non-Veg</span></td>
                  <td class="text-center font-bold text-white" id="tableNonVeg"><?php echo $pref_counts['non-veg']; ?></td>
                  <td class="text-center"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-500/20 text-rose-300" id="tableNonVegPct"><?php echo $nonveg_pct; ?>%</span></td>
                </tr>
                <tr class="border-t border-white/15">
                  <td class="font-extrabold text-white">Total</td>
                  <td class="text-center font-extrabold text-amber-400" id="tableTotal"><?php echo $pref_total; ?></td>
                  <td class="text-center text-gray-400">100%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ==============================================
       SECTION 6: FOOD NUTRITION VALUES 🥗
       ============================================== -->
  <section class="anim-fade-up" id="section-nutrition">
    <div class="text-center mb-6">
      <div class="inline-flex p-3 rounded-2xl bg-gradient-to-tr from-teal-500/20 to-cyan-500/20 border border-teal-500/30 text-2xl mb-3">🥗</div>
      <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Food Nutrition Values</h2>
      <p class="text-sm text-gray-400 mt-1">Macro breakdown for common campus dining items — per serving</p>
    </div>

    <?php if (!empty($nutrition_records)): ?>

    <!-- Macro legend -->
    <div class="flex flex-wrap justify-center gap-3 mb-5 text-xs font-semibold">
      <span class="px-3 py-1 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/25">🔥 Calories (kcal)</span>
      <span class="px-3 py-1 rounded-full bg-blue-500/15 text-blue-300 border border-blue-500/25">💪 Protein (g)</span>
      <span class="px-3 py-1 rounded-full bg-green-500/15 text-green-300 border border-green-500/25">🌾 Carbs (g)</span>
      <span class="px-3 py-1 rounded-full bg-rose-500/15 text-rose-300 border border-rose-500/25">🥑 Fat (g)</span>
      <span class="px-3 py-1 rounded-full bg-purple-500/15 text-purple-300 border border-purple-500/25">🌿 Fiber (g)</span>
    </div>

    <!-- Nutrition Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
      <?php foreach ($nutrition_records as $ni): ?>
        <div class="glass-card p-5">
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-bold text-sm text-white leading-tight"><?php echo htmlspecialchars($ni['food_name']); ?></h3>
              <span class="text-[11px] text-gray-400"><?php echo $ni['meal_type']; ?> • <?php echo htmlspecialchars($ni['serving_size']); ?></span>
            </div>
            <span class="text-2xl font-extrabold text-amber-400 ml-3 flex-shrink-0"><?php echo $ni['calories']; ?></span>
          </div>
          <div class="text-[11px] text-gray-500 mb-3 leading-relaxed">
            <?php if (!empty($ni['description'])): ?>
              <?php echo htmlspecialchars($ni['description']); ?>
            <?php endif; ?>
          </div>
          <!-- Macro pills -->
          <div class="grid grid-cols-4 gap-1 text-center">
            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-1.5">
              <div class="font-extrabold text-blue-300 text-xs"><?php echo $ni['protein']; ?>g</div>
              <div class="text-[9px] text-gray-500 uppercase">Protein</div>
            </div>
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-1.5">
              <div class="font-extrabold text-green-300 text-xs"><?php echo $ni['carbohydrates']; ?>g</div>
              <div class="text-[9px] text-gray-500 uppercase">Carbs</div>
            </div>
            <div class="bg-rose-500/10 border border-rose-500/20 rounded-lg p-1.5">
              <div class="font-extrabold text-rose-300 text-xs"><?php echo $ni['fat']; ?>g</div>
              <div class="text-[9px] text-gray-500 uppercase">Fat</div>
            </div>
            <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg p-1.5">
              <div class="font-extrabold text-purple-300 text-xs"><?php echo $ni['fiber']; ?>g</div>
              <div class="text-[9px] text-gray-500 uppercase">Fiber</div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Full Nutrition Table -->
    <div class="glass-card p-5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <h3 class="text-base font-bold text-white">📊 Detailed Nutrition Table</h3>
        <input type="text" id="nutriFilterInput" placeholder="🔍 Filter by food..." class="custom-input text-xs py-1.5 w-full sm:w-56">
      </div>
      <div class="overflow-x-auto rounded-xl border border-white/10">
        <table class="custom-table text-xs" id="studentNutriTable">
          <thead>
            <tr>
              <th>Food Item</th>
              <th>Meal</th>
              <th class="text-center">🔥 Cal</th>
              <th class="text-center">💪 Protein</th>
              <th class="text-center">🌾 Carbs</th>
              <th class="text-center">🥑 Fat</th>
              <th class="text-center">🌿 Fiber</th>
              <th>Per Serving</th>
            </tr>
          </thead>
          <tbody id="studentNutriBody">
            <?php foreach ($nutrition_records as $ni): ?>
              <tr class="student-nutri-row">
                <td class="font-semibold text-white"><?php echo htmlspecialchars($ni['food_name']); ?></td>
                <td><?php echo $ni['meal_type']; ?></td>
                <td class="text-center font-bold text-amber-400"><?php echo $ni['calories']; ?></td>
                <td class="text-center text-blue-300 font-medium"><?php echo $ni['protein']; ?>g</td>
                <td class="text-center text-green-300 font-medium"><?php echo $ni['carbohydrates']; ?>g</td>
                <td class="text-center text-rose-300 font-medium"><?php echo $ni['fat']; ?>g</td>
                <td class="text-center text-purple-300 font-medium"><?php echo $ni['fiber']; ?>g</td>
                <td class="text-gray-400"><?php echo htmlspecialchars($ni['serving_size']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php else: ?>
    <div class="glass-card p-10 text-center">
      <p class="text-4xl mb-3">🥗</p>
      <p class="text-gray-400 text-sm">Nutrition data is being compiled by the mess team. Check back soon!</p>
    </div>
    <?php endif; ?>
  </section>

  <!-- ==========================================
       FOOTER
       ========================================== -->
  <footer class="text-center py-6 text-xs text-gray-500 border-t border-white/5">
    <p>Mess Management &amp; Dining Quality Portal • Built for Campus Excellence</p>
    <p class="mt-1">Designed with Dynamic Themes &amp; Real-time Tracking</p>
  </footer>

</main>

<script src="assets/app.js"></script>
<script>
// ────────────────────────────────────────────────
// Generate a stable session token (stored in localStorage)
// ────────────────────────────────────────────────
function getSessionToken() {
  let token = localStorage.getItem('mess_session_token');
  if (!token) {
    token = 'tok_' + Date.now() + '_' + Math.random().toString(36).slice(2, 11);
    localStorage.setItem('mess_session_token', token);
  }
  return token;
}

// ────────────────────────────────────────────────
// PIE CHART — Veg vs Non-Veg
// ────────────────────────────────────────────────
const prefCtx = document.getElementById('prefPieChart');
let prefChart;
if (prefCtx) {
  prefChart = new Chart(prefCtx, {
    type: 'doughnut',
    data: {
      labels: ['🥦 Vegetarian', '🍗 Non-Vegetarian'],
      datasets: [{
        data: [<?php echo $pref_counts['veg']; ?>, <?php echo $pref_counts['non-veg']; ?>],
        backgroundColor: ['rgba(16,185,129,0.7)', 'rgba(239,68,68,0.7)'],
        borderColor:      ['rgba(16,185,129,1)',   'rgba(239,68,68,1)'],
        borderWidth: 2,
        hoverOffset: 8,
      }]
    },
    options: {
      cutout: '68%',
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: ${ctx.parsed} students (${Math.round(ctx.parsed / (ctx.dataset.data.reduce((a,b)=>a+b,0)) * 100)}%)`
          }
        }
      }
    }
  });
}

// Update chart and counters with new data
function updatePrefUI(data) {
  if (prefChart) {
    prefChart.data.datasets[0].data = [data.veg, data.non_veg];
    prefChart.update();
  }
  document.getElementById('vegCount').textContent     = data.veg;
  document.getElementById('nonvegCount').textContent  = data.non_veg;
  document.getElementById('vegPct').textContent       = data.veg_pct + '%';
  document.getElementById('nonvegPct').textContent    = data.nonveg_pct + '%';
  document.getElementById('vegBar').style.width       = data.veg_pct + '%';
  document.getElementById('nonvegBar').style.width    = data.nonveg_pct + '%';
  document.getElementById('tableVeg').textContent     = data.veg;
  document.getElementById('tableNonVeg').textContent  = data.non_veg;
  document.getElementById('tableTotal').textContent   = data.total;
  document.getElementById('tableVegPct').textContent  = data.veg_pct + '%';
  document.getElementById('tableNonVegPct').textContent = data.nonveg_pct + '%';
  document.getElementById('prefTotalCenter').textContent = data.total;
}

// ────────────────────────────────────────────────
// Preference vote submission
// ────────────────────────────────────────────────
function submitPreference(pref) {
  const token = getSessionToken();
  const voteArea = document.getElementById('voteArea');
  const voteMsg  = document.getElementById('voteMsg');

  const formData = new FormData();
  formData.append('preference', pref);
  formData.append('session_token', token);

  fetch('submit_preference.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        updatePrefUI(data);
        voteArea.innerHTML = `<div class="col-span-2 py-4 text-center text-emerald-400 font-bold text-sm">✅ Thank you! Your ${pref === 'veg' ? '🥦 Vegetarian' : '🍗 Non-Veg'} vote is recorded.</div>`;
      } else if (data.message === 'already_voted') {
        voteMsg.textContent = '✅ You\'ve already voted. Results update live!';
        voteMsg.classList.remove('hidden');
      }
    })
    .catch(() => {
      voteMsg.textContent = 'Could not record vote. Please try again.';
      voteMsg.classList.remove('hidden');
    });
}

// Check if already voted on page load
(function() {
  const voted = localStorage.getItem('mess_pref_voted');
  if (voted) {
    const voteArea = document.getElementById('voteArea');
    if (voteArea) {
      voteArea.innerHTML = `<div class="col-span-2 py-3 text-center text-emerald-400 font-bold text-sm">✅ Your vote has been recorded. Thank you!</div>`;
    }
  }
})();

// ────────────────────────────────────────────────
// Food LIKE button handler
// ────────────────────────────────────────────────
document.querySelectorAll('.like-btn').forEach(btn => {
  const foodName = btn.dataset.food;
  const likedKey = 'liked_' + foodName.replace(/\s+/g, '_').toLowerCase();

  // Check if already liked
  if (localStorage.getItem(likedKey)) {
    btn.textContent = '❤️ Liked!';
    btn.disabled = true;
    btn.style.opacity = '0.6';
  }

  btn.addEventListener('click', function() {
    if (localStorage.getItem(likedKey)) return;

    const token = getSessionToken();
    const formData = new FormData();
    formData.append('food_name',     foodName);
    formData.append('meal_type',     btn.dataset.meal || 'Any');
    formData.append('session_token', token);

    fetch('submit_like.php', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success || data.message === 'already_liked') {
          localStorage.setItem(likedKey, '1');
          // Update all like-count cells for this food
          document.querySelectorAll('.like-count-cell').forEach(cell => {
            if (cell.dataset.food === foodName) {
              if (data.success) cell.textContent = data.likes_count.toLocaleString();
            }
          });
          // Update button UI
          document.querySelectorAll('.like-btn').forEach(b => {
            if (b.dataset.food === foodName) {
              b.textContent = '❤️ Liked!';
              b.disabled = true;
              b.style.opacity = '0.6';
            }
          });
          // Update podium count if applicable
          const idx = btn.dataset.idx;
          if (idx !== '' && idx !== undefined) {
            const counterElem = document.getElementById('likes-' + idx);
            if (counterElem && data.success) counterElem.textContent = data.likes_count.toLocaleString();
          }
        }
      });
  });
});

// ────────────────────────────────────────────────
// Nutrition filter on student page
// ────────────────────────────────────────────────
const nutriFilter = document.getElementById('nutriFilterInput');
if (nutriFilter) {
  nutriFilter.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#studentNutriBody .student-nutri-row').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}
</script>
</body>
</html>
