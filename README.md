# 🍽️ MessMeter — Smart Campus Mess Management System

A **production-quality, hackathon-winning** digital mess management platform built with PHP + MySQL + Vanilla JS + Tailwind CSS.

---

## ✨ Features

### Student Portal (`index.php`)
| Feature | Description |
|---|---|
| 📅 Today's Live Menu | 4-card layout (Breakfast, Lunch, Snacks, Dinner) with live meal status |
| 📆 Weekly Timetable | Filterable by day, searchable by dish name |
| ⭐ Feedback Portal | Star-rating (1–5) with animated sentiment feedback |
| 🏆 Most Loved Foods | Like leaderboard — top 10 foods ranked by student hearts |
| 🥦🍗 Food Preference | Veg vs Non-Veg live pie chart with one-click vote |
| 🥗 Nutrition Values | Macro cards (Calories, Protein, Carbs, Fat, Fiber) per serving |
| 🎨 5 Themes | Sunset Ember, Midnight Neon, Fresh Emerald, Ocean Azure, Light Luxe |
| 📢 Special Banners | Auto-shows when admin marks a meal as "Special / Substitution" |

### Admin Panel (`admin_login.php` → `admin_dashboard.php`)
| Feature | Description |
|---|---|
| 🔐 Secure Login | Session-based authentication |
| ✏️ Menu CRUD | Add/Edit/Delete any day's meal with one click |
| 🥗 Nutrition Manager | `admin_nutrition.php` — Add/Edit/Delete nutrition data |
| 📊 Feedback Analytics | View all student ratings per meal/day |
| 📋 KPI Cards | Total menu items, feedback count, avg rating, special count |
| 🔔 Notification Logging | Saving a "Special" meal auto-logs a notification for students |
| 📤 Week Clone | Clone current week's menu to fill missing slots |

---

## 🚀 Quick Setup (5 minutes)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL)

### Steps

```bash
# 1. Clone the repo into your XAMPP htdocs
git clone https://github.com/YOUR_USERNAME/messmeter.git C:/xampp/htdocs/mess_management

# 2. Copy and configure the DB connection
cp config.example.php config.php
# Edit config.php with your MySQL credentials (default: root / no password)

# 3. Import the database
# Open phpMyAdmin → Create database "mess_db" → Import database.sql
# Then run add_tables.sql to add new feature tables with demo data
```

### Database Import Order
1. `database.sql` — core schema + 28 menu items
2. `add_tables.sql` — preferences, nutrition, likes tables + demo data

### Access
| URL | Description |
|---|---|
| `http://localhost/mess_management/` | Student Portal |
| `http://localhost/mess_management/admin_login.php` | Admin Login |

**Default admin credentials:** `admin` / `admin123`

---

## 🗃️ Database Schema

```
mess_db
├── admin              — admin credentials
├── menu               — day + meal type + items (28 rows seeded)
├── feedback           — student ratings & comments
├── notifications_log  — special meal update log (for polling)
├── food_preferences   — veg/non-veg votes (100 rows seeded)
├── nutrition_info     — macro nutrition per food item (16 rows seeded)
├── food_likes         — aggregated like counts (15 rows seeded)
└── food_like_votes    — per-session like tracking (prevents duplicates)
```

---

## 🗂️ File Structure

```
mess_management/
├── index.php                  # Student portal (6 sections)
├── admin_login.php            # Admin login page
├── admin_dashboard.php        # Admin menu management
├── admin_nutrition.php        # Admin nutrition CRUD
├── admin_save_menu.php        # Save/update menu item
├── admin_save_nutrition.php   # Save/update nutrition item
├── admin_delete.php           # Delete menu item
├── admin_delete_nutrition.php # Delete nutrition item
├── admin_clone_week.php       # Clone week's menu
├── submit_feedback.php        # Student feedback handler
├── submit_preference.php      # Veg/Non-Veg vote handler (AJAX/JSON)
├── submit_like.php            # Food like handler (AJAX/JSON)
├── check_updates.php          # Polling endpoint for notifications
├── logout.php                 # Session destroy
├── config.php                 # DB config (gitignored)
├── config.example.php         # Template for config.php
├── database.sql               # Core schema + seed data
├── add_tables.sql             # New feature tables + demo data
└── assets/
    ├── style.css              # Global design system (5 themes, glassmorphism)
    └── app.js                 # Theme switcher, live clock, star ratings
```

---

## 🎨 Tech Stack

- **Backend:** PHP 7.4+ (procedural, no framework)
- **Database:** MySQL / MariaDB via MySQLi
- **Frontend:** HTML5, Vanilla JS (ES6+), Tailwind CSS (CDN)
- **Charts:** Chart.js 4.4 (Doughnut pie chart for preferences)
- **Design:** Glassmorphism, CSS variables, keyframe animations
- **Auth:** PHP sessions
- **No build steps** — works directly on XAMPP

---

## 👨‍💻 Demo Script (for Judges)

1. Open `http://localhost/mess_management/`
2. Show **Today's Menu** cards with live meal status
3. Filter the **Weekly Timetable** by day / search a dish
4. Submit a **star rating** via the Feedback Portal
5. Click **❤️ Like** on the Most Loved Foods leaderboard
6. Vote **Veg / Non-Veg** and watch the pie chart update live
7. Browse **Nutrition Values** — filter by food name
8. Login as admin → Update a meal as "Special" → Banner appears on student page
9. Add a new **nutrition entry** via Nutrition Manager
10. Switch themes using 🎨 button (Sunset → Midnight → Ocean etc.)

---

## 📄 License

MIT — free for educational and hackathon use.
