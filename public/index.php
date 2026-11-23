<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

$sql = "SELECT r.id, r.title, r.prep_time_min, r.servings,
               c.name AS category_name, u.username AS author_name
        FROM recipes r
        JOIN categories c ON r.category_id = c.id
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC";

$stmt = $pdo->query($sql);
$recipes = $stmt->fetchAll();

$pageTitle = 'Home';
require_once __DIR__ . '/../src/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h2 mb-1">Welcome to MacroChef</h1>
        <p class="text-muted mb-0">Smart Recipe Nutrition &amp; Supermarket Cost Analyzer</p>
    </div>
</div>

<?php if (empty($recipes)): ?>
    <div class="alert alert-info">No recipes found yet.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($recipes as $recipe): ?>
            <div class="col-md-4">
                <article class="card h-100 shadow-sm">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2"><?= e($recipe['category_name']) ?></span>
                        <h2 class="h5 card-title"><?= e($recipe['title']) ?></h2>
                        <p class="card-text text-muted small">
                            By <strong><?= e($recipe['author_name']) ?></strong> &bull;
                            <?= (int)$recipe['prep_time_min'] ?> min &bull;
                            <?= (int)$recipe['servings'] ?> serving(s)
                        </p>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../src/footer.php'; ?>