<?php
require_once '../config/db.php';

$pageTitle = 'Consulta de Livros';
$results = [];
$query = '';

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $query = trim($_GET['query']);
    $searchTerm = '%' . $query . '%';
    
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, 
                   (b.num_exemplares - COALESCE(COUNT(l.id), 0)) as exemplares_disponiveis
            FROM books b 
            LEFT JOIN loans l ON b.id = l.book_id AND l.status = 'ativo'
            WHERE b.titulo LIKE ? OR b.autor LIKE ? OR b.editora LIKE ?
            GROUP BY b.id
            ORDER BY b.titulo
        ");
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $results = $stmt->fetchAll();
        
        // Adicionar informação de disponibilidade
        foreach ($results as &$book) {
            $book['disponivel'] = $book['exemplares_disponiveis'] > 0;
            $book['status_text'] = $book['disponivel'] ? 
                "Disponível ({$book['exemplares_disponiveis']} exemplar(es))" : 
                "Indisponível";
        }
        
    } catch (PDOException $e) {
        $message = 'Erro na consulta: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

include '../includes/nav-pages.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-search"></i> Consulta de Livros</h2>
            
            <!-- Formulário de Busca -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="query" class="form-control form-control-lg" 
                                       placeholder="Digite o título, autor ou editora do livro..." 
                                       value="<?= htmlspecialchars($query) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                    
                    <?php if (!empty($query)): ?>
                        <div class="mt-3">
                            <small class="text-muted">
                                Resultados para: <strong>"<?= htmlspecialchars($query) ?>"</strong>
                                <?php if (!empty($results)): ?>
                                    - <?= count($results) ?> livro(s) encontrado(s)
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Resultados da Busca -->
            <?php if (!empty($query)): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Resultados da Busca</h5>
                        <?php if (!empty($results)): ?>
                            <span class="badge bg-primary"><?= count($results) ?> resultado(s)</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($results)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum livro encontrado</h5>
                                <p class="text-muted">Tente usar termos diferentes ou verifique a ortografia.</p>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>Dicas para uma busca mais eficiente:</strong><br>
                                        • Use palavras-chave do título<br>
                                        • Digite o nome do autor<br>
                                        • Informe a editora<br>
                                        • Use termos parciais
                                    </small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Título</th>
                                            <th>Autor</th>
                                            <th>Editora</th>
                                            <th>Ano</th>
                                            <th>Exemplares</th>
                                            <th>Disponibilidade</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $book): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($book['titulo']) ?></strong>
                                                    <?php if (!empty($book['isbn'])): ?>
                                                        <br><small class="text-muted">ISBN: <?= htmlspecialchars($book['isbn']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($book['autor']) ?></td>
                                                <td><?= htmlspecialchars($book['editora'] ?? '') ?></td>
                                                <td><?= $book['ano_publicacao'] ?? '-' ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= $book['num_exemplares'] ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($book['disponivel']): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> <?= $book['status_text'] ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times"></i> <?= $book['status_text'] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($book['disponivel']): ?>
                                                        <a href="loans.php" class="btn btn-sm btn-primary" 
                                                           data-bs-toggle="tooltip" title="Realizar empréstimo">
                                                            <i class="fas fa-plus"></i> Emprestar
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Indisponível</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Instruções quando não há busca -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-book fa-4x text-primary mb-4"></i>
                        <h4>Busque por Livros</h4>
                        <p class="text-muted mb-4">
                            Use o campo de busca acima para encontrar livros por título, autor ou editora.
                        </p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <i class="fas fa-font fa-2x text-primary mb-2"></i>
                                        <h6>Por Título</h6>
                                        <small class="text-muted">Digite o nome do livro</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <i class="fas fa-user fa-2x text-success mb-2"></i>
                                        <h6>Por Autor</h6>
                                        <small class="text-muted">Digite o nome do autor</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0">
                                    <div class="card-body">
                                        <i class="fas fa-building fa-2x text-warning mb-2"></i>
                                        <h6>Por Editora</h6>
                                        <small class="text-muted">Digite o nome da editora</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer-pages.php'; ?>
