<?php
// src/header.php - Cabecera y barra de navegación reutilizable
$pageTitle = $pageTitle ?? 'MacroChef';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - MacroChef</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand fw-bold" href="index.php">MacroChef</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>" href="index.php">Home</a>
                        </li>
                        <?php if (is_logged_in()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentPage === 'recipe_form.php' ? 'active' : '' ?>" href="recipe_form.php">+ New Recipe</a>
                            </li>
                            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link <?= $currentPage === 'admin_ingredients.php' ? 'active' : '' ?>" href="admin_ingredients.php">Admin Catalog</a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <span class="nav-link text-info">
                                    Hi, <?= e($_SESSION['username']) ?>
                                    <?php if (($_SESSION['role'] ?? '') === 'student'): ?>
                                        <span class="badge bg-success ms-1">Student -50%</span>
                                    <?php elseif (($_SESSION['role'] ?? '') === 'admin'): ?>
                                        <span class="badge bg-warning text-dark ms-1">Admin</span>
                                    <?php endif; ?>
                                </span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentPage === 'login.php' ? 'active' : '' ?>" href="login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $currentPage === 'register.php' ? 'active' : '' ?>" href="register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main class="container my-4 flex-grow-1">