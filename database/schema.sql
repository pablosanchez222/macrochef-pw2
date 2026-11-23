CREATE DATABASE IF NOT EXISTS macrochef_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE macrochef_db;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'student', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    description VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE ingredients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    supermarket ENUM('Generic', 'Lidl', 'Maxima', 'Rimi', 'Iki') NOT NULL DEFAULT 'Generic',
    kcal_per_100g DECIMAL(7,2) NOT NULL CHECK (kcal_per_100g >= 0),
    protein_per_100g DECIMAL(6,2) NOT NULL CHECK (protein_per_100g >= 0),
    carbs_per_100g DECIMAL(6,2) NOT NULL CHECK (carbs_per_100g >= 0),
    fat_per_100g DECIMAL(6,2) NOT NULL CHECK (fat_per_100g >= 0),
    price_per_100g DECIMAL(6,2) NOT NULL CHECK (price_per_100g >= 0),
    allergen_info VARCHAR(100) NOT NULL DEFAULT 'None',
    UNIQUE KEY unique_ingredient_store (name, supermarket)
) ENGINE=InnoDB;

CREATE TABLE recipes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(120) NOT NULL,
    instructions TEXT NOT NULL,
    prep_time_min INT UNSIGNED NOT NULL CHECK (prep_time_min > 0),
    servings INT UNSIGNED NOT NULL DEFAULT 1 CHECK (servings > 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recipes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_recipes_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE recipe_ingredients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    ingredient_id INT UNSIGNED NOT NULL,
    grams DECIMAL(7,2) NOT NULL CHECK (grams > 0),
    UNIQUE KEY unique_recipe_ingredient (recipe_id, ingredient_id),
    CONSTRAINT fk_ri_recipe FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ri_ingredient FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE RESTRICT
) ENGINE=InnoDB;