<?php
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../src/helpers.php';

// Solo puede entrar el Admin (si entra un usuario normal le da error 403)
require_admin();

$allowedSupermarkets = ['Generic', 'Lidl', 'Maxima', 'Rimi', 'Iki'];
$errors = [];

$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$formData = [
    'id' => 0,
    'name' => '',
    'supermarket' => 'Generic',
    'kcal_per_100g' => '',
    'protein_per_100g' => '',
    'carbs_per_100g' => '',
    'fat_per_100g' => '',
    'price_per_100g' => '',
    'allergen_info' => 'None',
];

// Si pulsaste "Edit", cargamos ese ingrediente en el formulario
if ($editId > 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare('SELECT * FROM ingredients WHERE id = ?');
    $stmt->execute([$editId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $formData = $existing;
    } else {
        $errors['general'] = 'Ingredient not found.';
    }
}

// Procesar Crear, Editar o Borrar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $deleteId = (int)($_POST['id'] ?? 0);
        if ($deleteId > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM ingredients WHERE id = ?');
                $stmt->execute([$deleteId]);
                header('Location: admin_ingredients.php?msg=deleted');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errors['general'] = 'Cannot delete this ingredient because it is currently used in a recipe (ON DELETE RESTRICT).';
                } else {
                    error_log('Delete ingredient error: ' . $e->getMessage());
                    $errors['general'] = 'Could not delete ingredient.';
                }
            }
        }
    } elseif ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $supermarket = $_POST['supermarket'] ?? 'Generic';
        $kcal = $_POST['kcal_per_100g'] ?? '';
        $protein = $_POST['protein_per_100g'] ?? '';
        $carbs = $_POST['carbs_per_100g'] ?? '';
        $fat = $_POST['fat_per_100g'] ?? '';
        $price = $_POST['price_per_100g'] ?? '';
        $allergen = trim($_POST['allergen_info'] ?? 'None');
        if ($allergen === '') {
            $allergen = 'None';
        }

        $formData = [
            'id' => $id,
            'name' => $name,
            'supermarket' => $supermarket,
            'kcal_per_100g' => $kcal,
            'protein_per_100g' => $protein,
            'carbs_per_100g' => $carbs,
            'fat_per_100g' => $fat,
            'price_per_100g' => $price,
            'allergen_info' => $allergen,
        ];

        // Validaciones en el servidor
        if (strlen($name) < 2 || strlen($name) > 100) {
            $errors['name'] = 'Name must be between 2 and 100 characters.';
        }
        if (!in_array($supermarket, $allowedSupermarkets, true)) {
            $errors['supermarket'] = 'Invalid supermarket selected.';
        }
        if (!is_numeric($kcal) || (float)$kcal < 0 || (float)$kcal > 1000) {
            $errors['kcal_per_100g'] = 'Calories must be between 0 and 1000.';
        }
        if (!is_numeric($protein) || (float)$protein < 0 || (float)$protein > 100) {
            $errors['protein_per_100g'] = 'Protein must be between 0 and 100g.';
        }
        if (!is_numeric($carbs) || (float)$carbs < 0 || (float)$carbs > 100) {
            $errors['carbs_per_100g'] = 'Carbs must be between 0 and 100g.';
        }
        if (!is_numeric($fat) || (float)$fat < 0 || (float)$fat > 100) {
            $errors['fat_per_100g'] = 'Fat must be between 0 and 100g.';
        }
        if (!is_numeric($price) || (float)$price < 0 || (float)$price > 500) {
            $errors['price_per_100g'] = 'Price must be between 0 and 500 EUR.';
        }
        if (strlen($allergen) > 100) {
            $errors['allergen_info'] = 'Allergen info must be under 100 characters.';
        }

        if (empty($errors)) {
            try {
                if ($id > 0) {
                    $stmt = $pdo->prepare(
                        'UPDATE ingredients
                         SET name = ?, supermarket = ?, kcal_per_100g = ?, protein_per_100g = ?,
                             carbs_per_100g = ?, fat_per_100g = ?, price_per_100g = ?, allergen_info = ?
                         WHERE id = ?'
                    );
                    $stmt->execute([
                        $name, $supermarket, (float)$kcal, (float)$protein,
                        (float)$carbs, (float)$fat, (float)$price, $allergen, $id
                    ]);
                    header('Location: admin_ingredients.php?msg=updated');
                    exit;
                } else {
                    $stmt = $pdo->prepare(
                        'INSERT INTO ingredients
                         (name, supermarket, kcal_per_100g, protein_per_100g, carbs_per_100g, fat_per_100g, price_per_100g, allergen_info)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $name, $supermarket, (float)$kcal, (float)$protein,
                        (float)$carbs, (float)$fat, (float)$price, $allergen
                    ]);
                    header('Location: admin_ingredients.php?msg=created');
                    exit;
                }
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errors['general'] = 'This ingredient already exists for the selected supermarket.';
                } else {
                    error_log('Save ingredient error: ' . $e->getMessage());
                    $errors['general'] = 'Could not save ingredient.';
                }
            }
        }
    }
}

$ingredients = $pdo->query('SELECT * FROM ingredients ORDER BY name ASC, supermarket ASC')->fetchAll();

$pageTitle = 'Admin - Ingredient Catalog';
require_once __DIR__ . '/../src/header.php';
?>

<h1 class="h2 mb-3">Master Ingredient Catalog (Admin)</h1>

<?php if (isset($_GET['msg'])): ?>
    <?php
    $messages = [
        'created' => 'Ingredient added successfully.',
        'updated' => 'Ingredient updated successfully.',
        'deleted' => 'Ingredient deleted successfully.',
    ];
    ?>
    <?php if (isset($messages[$_GET['msg']])): ?>
        <div class="alert alert-success"><?= e($messages[$_GET['msg']]) ?></div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger"><?= e($errors['general']) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header fw-bold">
        <?= (int)$formData['id'] > 0 ? 'Edit Ingredient' : 'Add New Ingredient' ?>
    </div>
    <div class="card-body">
        <form method="POST" action="admin_ingredients.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= (int)$formData['id'] ?>">

            <div class="row g-3">
                <div class="col-md-5">
                    <label for="name" class="form-label">Ingredient Name</label>
                    <input type="text" id="name" name="name"
                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                           value="<?= e((string)$formData['name']) ?>" required minlength="2" maxlength="100">
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label for="supermarket" class="form-label">Supermarket</label>
                    <select id="supermarket" name="supermarket" class="form-select <?= isset($errors['supermarket']) ? 'is-invalid' : '' ?>">
                        <?php foreach ($allowedSupermarkets as $store): ?>
                            <option value="<?= e($store) ?>" <?= $formData['supermarket'] === $store ? 'selected' : '' ?>>
                                <?= e($store) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="allergen_info" class="form-label">Allergen Info</label>
                    <input type="text" id="allergen_info" name="allergen_info"
                           class="form-control <?= isset($errors['allergen_info']) ? 'is-invalid' : '' ?>"
                           value="<?= e((string)$formData['allergen_info']) ?>" maxlength="100" placeholder="None, Milk, Gluten...">
                </div>

                <div class="col-md-2 col-6">
                    <label for="kcal_per_100g" class="form-label">Kcal / 100g</label>
                    <input type="number" step="0.01" min="0" max="1000" id="kcal_per_100g" name="kcal_per_100g"
                           class="form-control <?= isset($errors['kcal_per_100g']) ? 'is-invalid' : '' ?>"
                           value="<?= e((string)$formData['kcal_per_100g']) ?>" required>
                </div>

                <div class="col-md-2 col-6">
                    <label for="protein_per_100g" class="form-label">Protein (g)</label>
                    <input type="number" step="0.01" min="0" max="100" id="protein_per_100g" name="protein_per_100g"
                           class="form-control <?= isset($errors['protein_per_100g']) ? 'is-invalid' : '' ?>"
                           value="<?= e((string)$formData['protein_per_100g']) ?>" required>
                </div>

                <div class="col-md-2 col-6">
                    <label for="carbs_per_100g" class="form-label">Carbs (g)</label>
                    <input type="number" step="0.01" min="0" max="100" id="carbs_per_100g" name="carbs_per_100g"
                           class="form-control <?= isset($errors['carbs_per_100g']) ? 'is-invalid' : '' ?>"
                           value="<?= e((string)$formData['carbs_per_100g']) ?>" required>
                </div>

                <div class="col-md-2 col-6">
                    <label for="fat_per_100g" class="form-label">Fat (g)</label>
                    <input type="number" step="0.01" min="0" max="100" id="fat_per_100g" name="fat_per_100g"
                           class="form-control <?= isset($errors['fat_per_100g']) ? 'is-invalid' : '' ?>"
                           value="<?= e((string)$formData['fat_per_100g']) ?>" required>
                </div>

                <div class="col-md-2 col-6">
                    <label for="price_per_100g" class="form-label">Price (EUR)</label>
                    <input type="number" step="0.01" min="0" max="500" id="price_per_100g" name="price_per_100g"
                           class="form-control <?= isset($errors['price_per_100g']) ? 'is-invalid' : '' ?>"
                           value="<?= e((string)$formData['price_per_100g']) ?>" required>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <?= (int)$formData['id'] > 0 ? 'Update' : 'Add' ?>
                    </button>
                    <?php if ((int)$formData['id'] > 0): ?>
                        <a href="admin_ingredients.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Ingredient</th>
                    <th>Supermarket</th>
                    <th>Kcal</th>
                    <th>Protein</th>
                    <th>Carbs</th>
                    <th>Fat</th>
                    <th>Price / 100g</th>
                    <th>Allergens</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ingredients as $ing): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($ing['name']) ?></td>
                        <td><span class="badge bg-secondary"><?= e($ing['supermarket']) ?></span></td>
                        <td><?= e((string)$ing['kcal_per_100g']) ?></td>
                        <td><?= e((string)$ing['protein_per_100g']) ?>g</td>
                        <td><?= e((string)$ing['carbs_per_100g']) ?>g</td>
                        <td><?= e((string)$ing['fat_per_100g']) ?>g</td>
                        <td><?= e((string)$ing['price_per_100g']) ?> &euro;</td>
                        <td><?= e($ing['allergen_info']) ?></td>
                        <td class="text-end text-nowrap">
                            <a href="admin_ingredients.php?edit_id=<?= (int)$ing['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="POST" action="admin_ingredients.php" class="d-inline js-confirm-delete"
                                  data-item="<?= e($ing['name'] . ' (' . $ing['supermarket'] . ')') ?>">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$ing['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.querySelectorAll('.js-confirm-delete').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const itemName = form.dataset.item || 'this ingredient';
        if (!confirm(`Are you sure you want to delete "${itemName}"?`)) {
            event.preventDefault();
        }
    });
});
</script>

<?php require_once __DIR__ . '/../src/footer.php'; ?>