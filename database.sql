-- ============================================================
-- MESS MANAGEMENT SYSTEM - COMPLETE DATABASE SETUP
-- Run this file in phpMyAdmin > SQL tab OR import it directly
-- ============================================================

CREATE DATABASE IF NOT EXISTS mess_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mess_db;

-- ============================================================
-- TABLE: admin (single admin account)
-- ============================================================
CREATE TABLE IF NOT EXISTS admin (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50)  UNIQUE NOT NULL,
  password VARCHAR(100) NOT NULL
);
-- Default credentials: admin / admin123
INSERT IGNORE INTO admin (username, password) VALUES ('admin', 'admin123');

-- ============================================================
-- TABLE: menu (day-wise, meal-wise items)
-- ============================================================
CREATE TABLE IF NOT EXISTS menu (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  day_of_week  VARCHAR(20)  NOT NULL,
  meal_type    VARCHAR(20)  NOT NULL,   -- Breakfast / Lunch / Snacks / Dinner
  item_name    TEXT         NOT NULL,
  is_special   TINYINT(1)   DEFAULT 0, -- 1 = special / substitution
  updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_day_meal (day_of_week, meal_type)
);

-- ============================================================
-- TABLE: feedback
-- ============================================================
CREATE TABLE IF NOT EXISTS feedback (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  meal_type    VARCHAR(20)  NOT NULL,
  day_of_week  VARCHAR(20)  NOT NULL,
  student_name VARCHAR(100) DEFAULT 'Anonymous',
  rating       TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment      TEXT,
  created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_meal_day (meal_type, day_of_week)
);

-- ============================================================
-- TABLE: notifications_log (tracks special meal updates for polling)
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications_log (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  message      VARCHAR(255) NOT NULL,
  created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SEED DATA: Complete 7-day menu for demo
-- (Uses INSERT IGNORE so re-running won't duplicate)
-- ============================================================
TRUNCATE TABLE menu;

INSERT INTO menu (day_of_week, meal_type, item_name, is_special) VALUES
-- MONDAY
('Monday','Breakfast','Poha, Masala Tea, Banana, Boiled Egg',0),
('Monday','Lunch','Dal Tadka, Jeera Rice, Chapati, Aloo Gobhi, Salad',0),
('Monday','Snacks','Samosa (2 pcs), Green Chutney, Masala Tea',0),
('Monday','Dinner','Paneer Butter Masala, Butter Naan, Steamed Rice, Raita',1),
-- TUESDAY
('Tuesday','Breakfast','Idli (4 pcs), Sambhar, Coconut Chutney, Coffee',0),
('Tuesday','Lunch','Rajma Masala, Steamed Rice, Tandoori Roti, Boondi Raita, Pickle',0),
('Tuesday','Snacks','Bread Pakora (2 pcs), Tomato Ketchup, Ginger Tea',0),
('Tuesday','Dinner','Egg Curry / Veg Kofta, Steamed Rice, Chapati, Dal Soup',0),
-- WEDNESDAY
('Wednesday','Breakfast','Aloo Paratha (2), Dahi, Pickle, Butter, Chai',0),
('Wednesday','Lunch','Chole Masala, Bhatura (2), Onion Salad, Lassi',0),
('Wednesday','Snacks','Maggi Noodles, Lemon Tea',0),
('Wednesday','Dinner','Veg Biryani, Chicken Curry (Non-Veg Counter), Boondi Raita, Papad',1),
-- THURSDAY
('Thursday','Breakfast','Upma, Coconut Chutney, Boiled Egg, Tea',0),
('Thursday','Lunch','Mix Dal, Chapati, Veg Pulao, Aloo Jeera, Curd',0),
('Thursday','Snacks','Onion Bhaji, Mint Chutney, Tea',0),
('Thursday','Dinner','Mutton/Soya Curry, Rumali Roti, Dal Makhani, Salad',0),
-- FRIDAY
('Friday','Breakfast','Dosa (2 pcs), Sambhar, Red Chutney, Filter Coffee',0),
('Friday','Lunch','Kadhi Pakora, Rice, Puri (3), Mix Sabzi, Pickle',0),
('Friday','Snacks','Veg Puff, Chai, Banana',0),
('Friday','Dinner','Palak Paneer, Tandoori Roti, Steamed Rice, Kheer (Dessert)',1),
-- SATURDAY
('Saturday','Breakfast','Puri (4), Aloo Sabzi, Lassi, Boiled Egg',0),
('Saturday','Lunch','Dal Fry, Steamed Rice, Chapati, Baingan Bharta, Papad',0),
('Saturday','Snacks','Dhokla, Green Chutney, Cold Drink',0),
('Saturday','Dinner','Paneer Tikka, Butter Naan, Veg Jalfrezi, Fruit Custard',1),
-- SUNDAY
('Sunday','Breakfast','Chole Bhature, Pickle, Jalebi, Chai',1),
('Sunday','Lunch','Biryani (Veg + Egg), Cucumber Raita, Salad, Papad, Cold Drink',1),
('Sunday','Snacks','Pav Bhaji, Butter, Lemon, Chai',0),
('Sunday','Dinner','Chicken Tikka Masala / Paneer Kadai, Garlic Naan, Gulab Jamun',1);
