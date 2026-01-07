<?php 
require_once 'config/db.php';
$pageTitle = 'Sistema de Gerenciamento de Biblioteca';

// Buscar estatísticas do banco de dados
try {
    $stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM books) as total_livros,
            (SELECT COUNT(*) FROM users) as total_usuarios,
            (SELECT COUNT(*) FROM loans WHERE status = 'ativo') as emprestimos_ativos,
            (SELECT COUNT(*) FROM loans WHERE DATE(data_devolucao_real) = CURDATE() AND status = 'devolvido') as devolucoes_hoje
    ")->fetch();
    
    // Buscar atividades recentes
    $atividades = $pdo->query("
        SELECT l.*, b.titulo, u.nome, u.matricula,
               CASE 
                   WHEN l.status = 'ativo' THEN 'aluguel'
                   WHEN l.status = 'devolvido' THEN 'devolucao'
                   ELSE 'outro'
               END as tipo_atividade
        FROM loans l 
        JOIN books b ON l.book_id = b.id 
        JOIN users u ON l.user_id = u.id 
        ORDER BY l.created_at DESC 
        LIMIT 10
    ")->fetchAll();
    
    // Buscar livros para modal de aluguel
    $livros_disponiveis = $pdo->query("
        SELECT b.*, 
               (b.num_exemplares - COALESCE(COUNT(l.id), 0)) as exemplares_disponiveis
        FROM books b 
        LEFT JOIN loans l ON b.id = l.book_id AND l.status = 'ativo'
        GROUP BY b.id
        HAVING exemplares_disponiveis > 0
        ORDER BY b.titulo
    ")->fetchAll();
    
    // Buscar usuários para modal de aluguel
    $usuarios = $pdo->query("SELECT id, nome, matricula FROM users ORDER BY nome")->fetchAll();
    
    // Buscar empréstimos ativos para modal de devolução
    $emprestimos_ativos = $pdo->query("
        SELECT l.*, b.titulo, u.nome, u.matricula,
               CASE 
                   WHEN l.data_devolucao < CURDATE() THEN 1 
                   ELSE 0 
               END as atrasado
        FROM loans l 
        JOIN books b ON l.book_id = b.id 
        JOIN users u ON l.user_id = u.id 
        WHERE l.status = 'ativo'
        ORDER BY l.data_devolucao ASC
    ")->fetchAll();
    
    // Buscar devoluções programadas para o calendário
    $devolucoes_programadas = $pdo->query("
        SELECT l.id, l.data_devolucao, b.titulo, u.nome,
               CASE 
                   WHEN l.data_devolucao < CURDATE() THEN 'atrasado'
                   WHEN l.data_devolucao = CURDATE() THEN 'hoje'
                   ELSE 'futuro'
               END as status_devolucao
        FROM loans l 
        JOIN books b ON l.book_id = b.id 
        JOIN users u ON l.user_id = u.id 
        WHERE l.status = 'ativo'
        ORDER BY l.data_devolucao ASC
        LIMIT 20
    ")->fetchAll();
    
} catch (PDOException $e) {
    $stats = ['total_livros' => 0, 'total_usuarios' => 0, 'emprestimos_ativos' => 0, 'devolucoes_hoje' => 0];
    $atividades = [];
    $livros_disponiveis = [];
    $usuarios = [];
    $emprestimos_ativos = [];
    $devolucoes_programadas = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <style>
        .quick-action-btn {
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
            height: 100%;
            min-height: 100px;
        }
        
        .quick-action-btn:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .quick-action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .activity-item {
            border-left: 3px solid #e9ecef;
            transition: all 0.2s ease;
        }
        
        .activity-item:hover {
            border-left-color: #0d6efd;
            background-color: #f8f9fa;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .activity-rental {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .activity-return {
            background-color: #cfe2ff;
            color: #084298;
        }
        
        .calendar-card {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .calendar-item {
            border-left: 4px solid;
            transition: all 0.2s ease;
        }
        
        .calendar-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        
        .calendar-item.hoje {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }
        
        .calendar-item.atrasado {
            border-left-color: #dc3545;
            background-color: #f8d7da;
        }
        
        .calendar-item.futuro {
            border-left-color: #198754;
        }
        
        .badge-status {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Login Page -->
    <div id="loginPage" class="login-container">
        <div class="card shadow-lg login-card">
            <div class="card-body p-4">
                <div class="login-icon">
                    <i class="bi bi-book"></i>
                </div>
                <h2 class="card-title text-center mb-2">Sistema de Biblioteca</h2>
                <p class="text-center text-muted mb-2">Entre com suas credenciais para acessar o sistema</p>
                <p class="text-center text-secondary mb-2">Login: seu nome | Senha: 1q2w3e</p>
                <form id="loginForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="username" placeholder="Digite seu usuário" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" class="form-control" id="password" placeholder="Digite sua senha" required>
                    </div>
                    <button type="submit" class="btn btn-primary-custom w-100">Entrar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Dashboard Page -->
    <div id="dashboardPage" class="hidden">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="d-flex align-items-center">
                            <div class="header-logo">
                                <i class="bi bi-book"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">Sistema de Biblioteca</h5>
                                <small class="text-muted">Bem-vindo, <span id="currentUserName"></span></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-outline-secondary btn-sm" id="logoutBtn">
                            <i class="bi bi-box-arrow-right me-1"></i>Sair
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container-fluid py-4">
            <!-- Navigation Cards -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="pages/users.php" class="card navigation-card h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Cadastrar</p>
                                    <h5 class="mb-0">Usuários</h5>
                                </div>
                                <div class="nav-icon" style="background-color: #8b5cf6;">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">Gerenciar usuários do sistema</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="pages/books.php" class="card navigation-card h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Cadastrar</p>
                                    <h5 class="mb-0">Livros</h5>
                                </div>
                                <div class="nav-icon" style="background-color: #3b82f6;">
                                    <i class="bi bi-book"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">Gerenciar acervo de livros</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="pages/loans.php" class="card navigation-card h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Gerenciar</p>
                                    <h5 class="mb-0">Empréstimos</h5>
                                </div>
                                <div class="nav-icon" style="background-color: #059669;">
                                    <i class="bi bi-arrow-left-right"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">Controle de empréstimos</small>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-12 col-sm-6 col-lg-3">
                    <a href="pages/reports.php" class="card navigation-card h-100 text-decoration-none">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted mb-1 small">Visualizar</p>
                                    <h5 class="mb-0">Relatórios</h5>
                                </div>
                                <div class="nav-icon" style="background-color: #f97316;">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">Relatórios do sistema</small>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="mb-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Ações Rápidas</h5>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <button class="btn quick-action-btn w-100" data-bs-toggle="modal" data-bs-target="#rentalModal">
                        <div class="quick-action-icon text-success">
                            <i class="bi bi-plus-circle-fill"></i>
                        </div>
                        <div class="fw-bold">Novo Empréstimo</div>
                        <small class="text-muted">Registrar aluguel</small>
                    </button>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <button class="btn quick-action-btn w-100" data-bs-toggle="modal" data-bs-target="#returnModal">
                        <div class="quick-action-icon text-primary">
                            <i class="bi bi-arrow-return-left"></i>
                        </div>
                        <div class="fw-bold">Registrar Devolução</div>
                        <small class="text-muted">Devolver livro</small>
                    </button>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <a href="pages/books.php" class="btn quick-action-btn w-100 text-decoration-none">
                        <div class="quick-action-icon text-info">
                            <i class="bi bi-book-fill"></i>
                        </div>
                        <div class="fw-bold">Cadastrar Livro</div>
                        <small class="text-muted">Adicionar ao acervo</small>
                    </a>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <a href="pages/users.php" class="btn quick-action-btn w-100 text-decoration-none">
                        <div class="quick-action-icon text-secondary">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div class="fw-bold">Cadastrar Usuário</div>
                        <small class="text-muted">Novo membro</small>
                    </a>
                </div>
            </div>

            <!-- Activities and Calendar Row -->
            <div class="row g-3">
                <!-- Recent Activities -->
                <div class="col-12 col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history text-primary me-2"></i>Atividades Recentes
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($atividades)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Nenhuma atividade registrada</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($atividades as $atividade): ?>
                                        <div class="list-group-item activity-item">
                                            <div class="d-flex align-items-start">
                                                <div class="activity-icon <?= $atividade['tipo_atividade'] == 'aluguel' ? 'activity-rental' : 'activity-return' ?> me-3">
                                                    <i class="bi <?= $atividade['tipo_atividade'] == 'aluguel' ? 'bi-box-arrow-right' : 'bi-box-arrow-in-left' ?>"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1"><?= htmlspecialchars($atividade['titulo']) ?></h6>
                                                            <p class="mb-1 text-muted small">
                                                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($atividade['nome']) ?>
                                                                <span class="ms-2 text-secondary">(<?= htmlspecialchars($atividade['matricula']) ?>)</span>
                                                            </p>
                                                        </div>
                                                        <span class="badge <?= $atividade['tipo_atividade'] == 'aluguel' ? 'bg-success' : 'bg-primary' ?> badge-status">
                                                            <?= $atividade['tipo_atividade'] == 'aluguel' ? 'Empréstimo' : 'Devolução' ?>
                                                        </span>
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-calendar3 me-1"></i>
                                                        <?= date('d/m/Y H:i', strtotime($atividade['created_at'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="pages/loans.php" class="btn btn-sm btn-outline-primary">
                                Ver todas as atividades <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="col-12 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-event text-warning me-2"></i>Devoluções Programadas
                            </h5>
                        </div>
                        <div class="card-body calendar-card p-2">
                            <?php if (empty($devolucoes_programadas)): ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                                    <p class="mt-2">Nenhuma devolução programada</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($devolucoes_programadas as $devolucao): ?>
                                        <div class="list-group-item calendar-item <?= $devolucao['status_devolucao'] ?> px-3 py-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 small"><?= htmlspecialchars($devolucao['titulo']) ?></h6>
                                                    <p class="mb-1 text-muted" style="font-size: 0.75rem;">
                                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($devolucao['nome']) ?>
                                                    </p>
                                                    <small class="text-muted" style="font-size: 0.7rem;">
                                                        <i class="bi bi-calendar3 me-1"></i>
                                                        <?= date('d/m/Y', strtotime($devolucao['data_devolucao'])) ?>
                                                    </small>
                                                </div>
                                                <?php if ($devolucao['status_devolucao'] == 'atrasado'): ?>
                                                    <span class="badge bg-danger badge-status">Atrasado</span>
                                                <?php elseif ($devolucao['status_devolucao'] == 'hoje'): ?>
                                                    <span class="badge bg-warning text-dark badge-status">Hoje</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success badge-status">No prazo</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="pages/loans.php" class="btn btn-sm btn-outline-warning">
                                Ver calendário completo <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Rental Modal -->
    <div class="modal fade" id="rentalModal" tabindex="-1" aria-labelledby="rentalModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rentalModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Novo Empréstimo
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rentalForm">
                        <div class="mb-3">
                            <label for="userId" class="form-label">Usuário</label>
                            <select class="form-select" id="userId" required>
                                <option value="">Selecione um usuário</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id'] ?>">
                                        <?= htmlspecialchars($usuario['nome']) ?> - <?= htmlspecialchars($usuario['matricula']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="bookId" class="form-label">Livro</label>
                            <select class="form-select" id="bookId" required>
                                <option value="">Selecione um livro</option>
                                <?php foreach ($livros_disponiveis as $livro): ?>
                                    <option value="<?= $livro['id'] ?>">
                                        <?= htmlspecialchars($livro['titulo']) ?> (<?= $livro['exemplares_disponiveis'] ?> disponíveis)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="returnDate" class="form-label">Data de Devolução</label>
                            <input type="date" class="form-control" id="returnDate" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-2"></i>Confirmar Empréstimo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">
                        <i class="bi bi-arrow-return-left me-2"></i>Registrar Devolução
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="returnForm">
                        <div class="mb-3">
                            <label for="loanId" class="form-label">Empréstimo</label>
                            <select class="form-select" id="loanId" required>
                                <option value="">Selecione um empréstimo</option>
                                <?php foreach ($emprestimos_ativos as $emprestimo): ?>
                                    <option value="<?= $emprestimo['id'] ?>" class="<?= $emprestimo['atrasado'] ? 'text-danger' : '' ?>">
                                        <?= htmlspecialchars($emprestimo['titulo']) ?> - <?= htmlspecialchars($emprestimo['nome']) ?>
                                        <?= $emprestimo['atrasado'] ? '(ATRASADO)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Confirmar Devolução
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <span id="toastMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>