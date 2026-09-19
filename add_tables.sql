-- ============================================================
-- ADD NEW TABLES: Preferences, Nutrition, Food Likes
-- Safe to run multiple times (uses IF NOT EXISTS)
-- ============================================================
USE mess_db;

-- 1. Veg/Non-Veg Food Preference votes
CREATE TABLE IF NOT EXISTS food_preferences (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  preference    ENUM('veg','non-veg') NOT NULL,
  session_token VARCHAR(64) DEFAULT NULL,  -- browser fingerprint to avoid duplicates
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_pref (preference)
);

-- Seed realistic demo data (65 veg : 35 non-veg)
INSERT INTO food_preferences (preference) VALUES
('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),
('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),
('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),
('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),
('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),
('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),('veg'),
('veg'),('veg'),('veg'),('veg'),('veg'),
('non-veg'),('non-veg'),('non-veg'),('non-veg'),('non-veg'),
('non-veg'),('non-veg'),('non-veg'),('non-veg'),('non-veg'),
('non-veg'),('non-veg'),('non-veg'),('non-veg'),('non-veg'),
('non-veg'),('non-veg'),('non-veg'),('non-veg'),('non-veg'),
('non-veg'),('non-veg'),('non-veg'),('non-veg'),('non-veg'),
('non-veg'),('non-veg'),('non-veg'),('non-veg'),('non-veg'),
('non-veg'),('non-veg'),('non-veg'),('non-veg'),('non-veg');

-- 2. Food Nutrition Information
CREATE TABLE IF NOT EXISTS nutrition_info (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  food_name     VARCHAR(100) NOT NULL,
  meal_type     VARCHAR(20)  DEFAULT 'Any',
  calories      INT          DEFAULT 0,
  protein       DECIMAL(5,1) DEFAULT 0.0,
  carbohydrates DECIMAL(5,1) DEFAULT 0.0,
  fat           DECIMAL(5,1) DEFAULT 0.0,
  fiber         DECIMAL(5,1) DEFAULT 0.0,
  serving_size  VARCHAR(50)  DEFAULT '1 serving',
  description   TEXT,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed realistic nutrition data for common Indian mess items
INSERT INTO nutrition_info (food_name, meal_type, calories, protein, carbohydrates, fat, fiber, serving_size, description) VALUES
('Steamed Rice',       'Lunch',     206,  4.3,  44.5,  0.4,  0.6, '1 cup (200g)',        'Plain steamed white rice, staple carbohydrate source'),
('Dal Tadka',          'Lunch',     180,  9.2,  25.0,  4.5,  6.0, '1 bowl (200ml)',      'Yellow lentils tempered with cumin, garlic and spices'),
('Chapati / Roti',     'Lunch',      71,  2.7,  14.6,  0.8,  2.1, '1 piece (40g)',       'Whole wheat flatbread, rich in complex carbohydrates'),
('Paneer Butter Masala','Dinner',   310, 14.5,  18.0, 20.5,  2.0, '1 bowl (250ml)',      'Cottage cheese in rich tomato-cream gravy'),
('Poha',               'Breakfast', 158,  3.2,  32.0,  2.5,  1.8, '1 plate (150g)',      'Flattened rice cooked with onion, mustard, turmeric'),
('Idli',               'Breakfast',  58,  2.0,  12.0,  0.2,  0.5, '1 piece (50g)',       'Steamed rice & lentil cake, fermented for probiotics'),
('Sambhar',            'Breakfast',  80,  4.5,  12.5,  1.8,  4.0, '1 cup (150ml)',       'South Indian lentil & vegetable stew, protein-rich'),
('Veg Biryani',        'Dinner',    320,  7.0,  60.0,  7.0,  4.5, '1 plate (350g)',      'Aromatic basmati rice layered with mixed vegetables'),
('Chole Masala',       'Lunch',     210, 10.0,  30.0,  5.0,  8.5, '1 bowl (200ml)',      'Chickpea curry — excellent source of plant protein & fiber'),
('Aloo Paratha',       'Breakfast', 290,  6.5,  45.0,  9.5,  3.5, '1 paratha (120g)',    'Stuffed whole wheat flatbread with spiced potato filling'),
('Dosa',               'Breakfast', 133,  3.5,  25.0,  2.5,  1.0, '1 piece (90g)',       'Crispy fermented rice & lentil crepe'),
('Rajma Curry',        'Lunch',     225, 12.5,  35.0,  4.0,  9.0, '1 bowl (200ml)',      'Kidney bean curry, high protein & fiber vegetarian meal'),
('Egg Curry',          'Dinner',    185, 12.0,  10.0, 10.5,  1.5, '2 eggs + gravy',      'Hard-boiled eggs in spiced onion-tomato gravy'),
('Kheer',              'Dinner',    195,  4.8,  32.0,  6.0,  0.3, '1 cup (150ml)',       'Sweet rice pudding made with milk, sugar, cardamom'),
('Lassi',              'Lunch',     120,  4.5,  18.0,  3.5,  0.0, '1 glass (250ml)',     'Sweet yogurt-based drink, rich in probiotics'),
('Fruit Custard',      'Dinner',    175,  4.0,  30.0,  4.5,  1.5, '1 bowl (200g)',       'Chilled vanilla custard mixed with seasonal fruits');

-- 3. Food Likes / Most Loved Foods
CREATE TABLE IF NOT EXISTS food_likes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  food_name     VARCHAR(100) NOT NULL,
  meal_type     VARCHAR(20)  DEFAULT 'Any',
  likes_count   INT          DEFAULT 0,
  total_rating  INT          DEFAULT 0,   -- sum of all ratings
  rating_count  INT          DEFAULT 0,   -- number of ratings
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_food (food_name, meal_type)
);

-- Table to prevent duplicate likes per browser session
CREATE TABLE IF NOT EXISTS food_like_votes (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  food_name     VARCHAR(100) NOT NULL,
  meal_type     VARCHAR(20)  DEFAULT 'Any',
  session_token VARCHAR(64)  NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_vote (food_name, meal_type, session_token)
);

-- Seed popular food likes for demo
INSERT IGNORE INTO food_likes (food_name, meal_type, likes_count, total_rating, rating_count) VALUES
('Sunday Biryani (Veg + Egg)',     'Dinner',    156, 760, 170),
('Paneer Butter Masala',           'Dinner',    132, 620, 140),
('Chole Bhature',                  'Breakfast', 118, 568, 126),
('Chicken Tikka Masala',           'Dinner',    109, 520, 115),
('Palak Paneer',                   'Dinner',     98, 450, 100),
('Aloo Paratha + Dahi',            'Breakfast',  87, 400,  92),
('Dal Makhani',                    'Dinner',     82, 380,  88),
('Pav Bhaji',                      'Snacks',     76, 350,  80),
('Idli Sambhar',                   'Breakfast',  71, 325,  74),
('Rajma Chawal',                   'Lunch',      65, 290,  68),
('Veg Biryani',                    'Dinner',     60, 270,  64),
('Dhokla',                         'Snacks',     54, 235,  56),
('Bread Pakora',                   'Snacks',     49, 205,  50),
('Kheer',                          'Dinner',     44, 185,  46),
('Gulab Jamun',                    'Dinner',     40, 170,  42);
