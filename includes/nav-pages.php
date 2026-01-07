<?php
// Iniciar sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Sistema Biblioteca' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="bi bi-book me-2"></i>Sistema Biblioteca
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../index.php">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="books.php">
                    <i class="bi bi-book me-1"></i>Livros
                </a>
                <a class="nav-link" href="users.php">
                    <i class="bi bi-people me-1"></i>Usuários
                </a>
                <a class="nav-link" href="loans.php">
                    <i class="bi bi-arrow-left-right me-1"></i>Empréstimos
                </a>
            </div>
        </div>
    </nav>