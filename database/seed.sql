USE macrochef_db;

INSERT INTO users (username, email, password_hash, role) VALUES
('admin', 'admin@macrochef.local', '$2y$12$MgyA44ue1DSFAhAGEsLRXOa9RZfR55u2AsT2MBCgpw2bXM1LmRRG6', 'admin'),
('pablo_student', 'pablo@student.vu.lt', '$2y$12$MgyA44ue1DSFAhAGEsLRXOa9RZfR55u2AsT2MBCgpw2bXM1LmRRG6', 'student'),
('alex_fit', 'alex@fitness.local', '$2y$12$MgyA44ue1DSFAhAGEsLRXOa9RZfR55u2AsT2MBCgpw2bXM1LmRRG6', 'user');

INSERT INTO categories (name, description) VALUES
('Breakfast', 'Quick morning meals to start the day with energy.'),
('High Protein Lunch', 'Balanced lunches with high protein content.'),
('Quick Dinner', 'Light and fast recipes under 20 minutes.'),
('Post-Workout', 'Meals and shakes for muscle recovery.');

INSERT INTO ingredients (name, supermarket, kcal_per_100g, protein_per_100g, carbs_per_100g, fat_per_100g, price_per_100g, allergen_info) VALUES
('Chicken Breast', 'Lidl', 110.00, 23.00, 0.00, 1.50, 0.69, 'None'),
('Chicken Breast', 'Maxima', 114.00, 22.50, 0.00, 2.00, 0.78, 'None'),
('Basmati Rice', 'Rimi', 350.00, 8.00, 77.00, 0.80, 0.28, 'None'),
('Rolled Oats', 'Lidl', 372.00, 13.50, 58.70, 7.00, 0.14, 'Gluten'),
('Greek Yogurt 2%', 'Maxima', 73.00, 9.00, 4.00, 2.00, 0.45, 'Milk'),
('Eggs', 'Iki', 143.00, 12.60, 0.70, 9.50, 0.42, 'Eggs'),
('Salmon Fillet', 'Rimi', 208.00, 20.00, 0.00, 13.00, 1.85, 'Fish'),
('Avocado', 'Lidl', 160.00, 2.00, 8.50, 14.70, 0.75, 'None'),
('Banana', 'Maxima', 89.00, 1.10, 22.80, 0.30, 0.15, 'None'),
('Olive Oil', 'Generic', 884.00, 0.00, 0.00, 100.00, 1.10, 'None'),
('Whey Protein Powder', 'Generic', 380.00, 80.00, 6.00, 4.00, 2.50, 'Milk'),
('Broccoli', 'Iki', 34.00, 2.80, 6.60, 0.40, 0.25, 'None');

INSERT INTO recipes (user_id, category_id, title, instructions, prep_time_min, servings) VALUES
(2, 2, 'Lidl Student Chicken & Rice', '1. Boil the basmati rice for 12 minutes.\n2. Cook the chicken breast in a pan with olive oil.\n3. Steam the broccoli and serve everything together.', 20, 1),
(2, 1, 'High Protein Morning Oats', '1. Mix rolled oats with water or milk and microwave for 2 minutes.\n2. Stir in whey protein and Greek yogurt.\n3. Top with sliced banana.', 10, 1),
(3, 3, 'Salmon & Avocado Power Bowl', '1. Pan-sear the salmon fillet for 4 minutes each side.\n2. Serve over cooked basmati rice.\n3. Add sliced avocado on top.', 15, 1);

INSERT INTO recipe_ingredients (recipe_id, ingredient_id, grams) VALUES
(1, 1, 200.00),
(1, 3, 100.00),
(1, 12, 150.00),
(1, 10, 10.00),
(2, 4, 80.00),
(2, 11, 30.00),
(2, 5, 100.00),
(2, 9, 100.00),
(3, 7, 180.00),
(3, 3, 90.00),
(3, 8, 80.00);