<?php
require_once '../config/db.php';

$pageTitle = 'Gerenciamento de Livros';
$message = '';
$messageType = '';

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['add'])) {
            $titulo = trim($_POST['titulo']);
            $autor = trim($_POST['autor']);
            $editora = trim($_POST['editora']);
            $ano = !empty($_POST['ano_publicacao']) ? (int)$_POST['ano_publicacao'] : null;
            $exemplares = (int)$_POST['num_exemplares'];
            
            // Validação
            if (empty($titulo) || empty($autor) || $exemplares <= 0) {
                throw new Exception('Preencha todos os campos obrigatórios corretamente.');
            }
            
            $stmt = $pdo->prepare("INSERT INTO books (titulo, autor, editora, ano_publicacao, num_exemplares) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$titulo, $autor, $editora, $ano, $exemplares]);
            $message = 'Livro adicionado com sucesso!';
            $messageType = 'success';
            
        } elseif (isset($_POST['update'])) {
            $id = (int)$_POST['id'];
            $titulo = trim($_POST['titulo']);
            $autor = trim($_POST['autor']);
            $editora = trim($_POST['editora']);
            $ano = !empty($_POST['ano_publicacao']) ? (int)$_POST['ano_publicacao'] : null;
            $exemplares = (int)$_POST['num_exemplares'];
            
            // Validação
            if (empty($titulo) || empty($autor) || $exemplares <= 0) {
                throw new Exception('Preencha todos os campos obrigatórios corretamente.');
            }
            
            $stmt = $pdo->prepare("UPDATE books SET titulo=?, autor=?, editora=?, ano_publicacao=?, num_exemplares=? WHERE id=?");
            $stmt->execute([$titulo, $autor, $editora, $ano, $exemplares, $id]);
            $message = 'Livro atualizado com sucesso!';
            $messageType = 'success';
            
        } elseif (isset($_POST['delete'])) {
            $id = (int)$_POST['id'];
            
            // Verificar se há empréstimos ativos
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE book_id = ? AND status = 'ativo'");
            $stmt->execute([$id]);
            $activeLoans = $stmt->fetchColumn();
            
            if ($activeLoans > 0) {
                throw new Exception('Não é possível excluir um livro com empréstimos ativos.');
            }
            
            $stmt = $pdo->prepare("DELETE FROM books WHERE id=?");
            $stmt->execute([$id]);
            $message = 'Livro excluído com sucesso!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Processar filtros
$filtros = [
    'titulo' => $_GET['filtro_titulo'] ?? '',
    'autor' => $_GET['filtro_autor'] ?? '',
    'editora' => $_GET['filtro_editora'] ?? '',
    'ano' => $_GET['filtro_ano'] ?? '',
    'exemplares_min' => $_GET['filtro_exemplares_min'] ?? '',
    'exemplares_max' => $_GET['filtro_exemplares_max'] ?? ''
];

// Construir query com filtros
$whereConditions = [];
$params = [];

if (!empty($filtros['titulo'])) {
    $whereConditions[] = "titulo LIKE ?";
    $params[] = '%' . $filtros['titulo'] . '%';
}

if (!empty($filtros['autor'])) {
    $whereConditions[] = "autor LIKE ?";
    $params[] = '%' . $filtros['autor'] . '%';
}

if (!empty($filtros['editora'])) {
    $whereConditions[] = "editora LIKE ?";
    $params[] = '%' . $filtros['editora'] . '%';
}

if (!empty($filtros['ano'])) {
    $whereConditions[] = "ano_publicacao = ?";
    $params[] = (int)$filtros['ano'];
}

if (!empty($filtros['exemplares_min'])) {
    $whereConditions[] = "num_exemplares >= ?";
    $params[] = (int)$filtros['exemplares_min'];
}

if (!empty($filtros['exemplares_max'])) {
    $whereConditions[] = "num_exemplares <= ?";
    $params[] = (int)$filtros['exemplares_max'];
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
$query = "SELECT * FROM books $whereClause ORDER BY titulo";

// Buscar livros com filtros
try {
    if (!empty($params)) {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $books = $stmt->fetchAll();
    } else {
        $books = $pdo->query($query)->fetchAll();
    }
    
    // Buscar dados para os filtros
    $anos = $pdo->query("SELECT DISTINCT ano_publicacao FROM books WHERE ano_publicacao IS NOT NULL ORDER BY ano_publicacao DESC")->fetchAll();
    $editoras = $pdo->query("SELECT DISTINCT editora FROM books WHERE editora IS NOT NULL AND editora != '' ORDER BY editora")->fetchAll();
    
} catch (PDOException $e) {
    $books = [];
    $anos = [];
    $editoras = [];
    $message = 'Erro ao carregar livros.';
    $messageType = 'danger';
}

include '../includes/nav-pages.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-book"></i> Gerenciamento de Livros</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Formulário de Cadastro/Edição -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Adicionar Novo Livro</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="bookForm">
                        <input type="hidden" name="id" id="bookId" value="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label">Título *</label>
                                <input type="text" name="titulo" id="titulo" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="autor" class="form-label">Autor *</label>
                                <input type="text" name="autor" id="autor" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editora" class="form-label">Editora</label>
                                <input type="text" name="editora" id="editora" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="ano_publicacao" class="form-label">Ano de Publicação</label>
                                <input type="number" name="ano_publicacao" id="ano_publicacao" class="form-control" min="1000" max="<?= date('Y') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="num_exemplares" class="form-label">Número de Exemplares *</label>
                                <input type="number" name="num_exemplares" id="num_exemplares" class="form-control" required min="1">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="add" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-plus"></i> Adicionar Livro
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancelBtn" style="display: none;" onclick="cancelEdit()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <!-- botão de filtro -->
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="bi bi-funnel"></i> Abrir Filtros
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Filtros -->
            <!-- <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-section">
                        <div class="row filter-row">
                            <div class="col-md-3">
                                <label for="filtro_titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="filtro_titulo" name="filtro_titulo" 
                                       value="<?= htmlspecialchars($filtros['titulo']) ?>" placeholder="Buscar por título...">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_autor" class="form-label">Autor</label>
                                <input type="text" class="form-control" id="filtro_autor" name="filtro_autor" 
                                       value="<?= htmlspecialchars($filtros['autor']) ?>" placeholder="Buscar por autor...">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_editora" class="form-label">Editora</label>
                                <select class="form-select" id="filtro_editora" name="filtro_editora">
                                    <option value="">Todas as editoras</option>
                                    <?php foreach ($editoras as $editora): ?>
                                        <option value="<?= htmlspecialchars($editora['editora']) ?>" 
                                                <?= $filtros['editora'] == $editora['editora'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($editora['editora']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_ano" class="form-label">Ano de Publicação</label>
                                <select class="form-select" id="filtro_ano" name="filtro_ano">
                                    <option value="">Todos os anos</option>
                                    <?php foreach ($anos as $ano): ?>
                                        <option value="<?= $ano['ano_publicacao'] ?>" 
                                                <?= $filtros['ano'] == $ano['ano_publicacao'] ? 'selected' : '' ?>>
                                            <?= $ano['ano_publicacao'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row filter-row">
                            <div class="col-md-3">
                                <label for="filtro_exemplares_min" class="form-label">Exemplares (mínimo)</label>
                                <input type="number" class="form-control" id="filtro_exemplares_min" name="filtro_exemplares_min" 
                                       value="<?= htmlspecialchars($filtros['exemplares_min']) ?>" min="1" placeholder="Mínimo">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_exemplares_max" class="form-label">Exemplares (máximo)</label>
                                <input type="number" class="form-control" id="filtro_exemplares_max" name="filtro_exemplares_max" 
                                       value="<?= htmlspecialchars($filtros['exemplares_max']) ?>" min="1" placeholder="Máximo">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="btn-group w-100">
                                    <button type="submit" class="btn btn-primary btn-filter">
                                        <i class="bi bi-search"></i> Filtrar
                                    </button>
                                    <a href="books.php" class="btn btn-outline-secondary btn-filter">
                                        <i class="bi bi-x-circle"></i> Limpar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div> -->

            <!-- Lista de Livros -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Lista de Livros</h5>
                        <span class="badge bg-primary"><?= count($books) ?> livros</span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <small class="text-muted">
                                <strong>Legenda dos Exemplares:</strong>
                                <span class="badge badge-exemplares-1 me-1">1 exemplar</span>
                                <span class="badge badge-exemplares-2-5 me-1">2-5 exemplares</span>
                                <span class="badge badge-exemplares-6-8 me-1">6-8 exemplares</span>
                                <span class="badge badge-exemplares-9-plus me-1">9+ exemplares</span>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($books)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum livro cadastrado ainda.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <!-- <th>ID</th> -->
                                        <th>Título</th>
                                        <th>Autor</th>
                                        <th>Editora</th>
                                        <th>Ano</th>
                                        <th>Exemplares</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($books as $book): ?>
                                    <tr>
                                        <!-- <td><?= $book['id'] ?></td> -->
                                        <td><?= htmlspecialchars($book['titulo']) ?></td>
                                        <td><?= htmlspecialchars($book['autor']) ?></td>
                                        <td><?= htmlspecialchars($book['editora'] ?? '') ?></td>
                                        <td><?= $book['ano_publicacao'] ?? '-' ?></td>
                                        <td>
                                            <?php
                                            $exemplares = $book['num_exemplares'];
                                            $badgeClass = '';
                                            
                                            if ($exemplares == 1) {
                                                $badgeClass = 'badge-exemplares-1';
                                            } elseif ($exemplares >= 2 && $exemplares <= 5) {
                                                $badgeClass = 'badge-exemplares-2-5';
                                            } elseif ($exemplares >= 6 && $exemplares <= 8) {
                                                $badgeClass = 'badge-exemplares-6-8';
                                            } elseif ($exemplares >= 9) {
                                                $badgeClass = 'badge-exemplares-9-plus';
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $exemplares ?> exemplar(es)</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditModal(<?= htmlspecialchars(json_encode($book)) ?>)">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                               <button type="button" class="btn btn-sm btn-outline-danger" onclick="openDeleteModal(<?= $book['id'] ?>)">
                                                    <i class="fas fa-trash"></i> Excluir
                                               </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        Tem certeza que deseja excluir este livro?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Excluir</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Edição de Livro -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editModalLabel">Editar Livro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <form id="editBookForm" method="POST">
          <input type="hidden" name="id" id="editBookId">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editTitulo" class="form-label">Título *</label>
              <input type="text" name="titulo" id="editTitulo" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="editAutor" class="form-label">Autor *</label>
              <input type="text" name="autor" id="editAutor" class="form-control" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editEditora" class="form-label">Editora</label>
              <input type="text" name="editora" id="editEditora" class="form-control">
            </div>
            <div class="col-md-3 mb-3">
              <label for="editAno" class="form-label">Ano de Publicação</label>
              <input type="number" name="ano_publicacao" id="editAno" class="form-control" min="1000" max="<?= date('Y') ?>">
            </div>
            <div class="col-md-3 mb-3">
              <label for="editNumExemplares" class="form-label">Num. de Exemplares *</label>
              <input type="number" name="num_exemplares" id="editNumExemplares" class="form-control" required min="1">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" form="editBookForm" name="update" class="btn btn-primary">Salvar Alterações</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Filtros -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="GET" id="filterForm">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel"><i class="bi bi-funnel"></i> Filtros de Livros</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="modal_filtro_titulo" class="form-label">Título</label>
              <input type="text" class="form-control" id="modal_filtro_titulo" name="filtro_titulo" 
                     placeholder="Buscar por título..." value="<?= htmlspecialchars($filtros['titulo']) ?>">
            </div>
            <div class="col-md-6">
              <label for="modal_filtro_autor" class="form-label">Autor</label>
              <input type="text" class="form-control" id="modal_filtro_autor" name="filtro_autor" 
                     placeholder="Buscar por autor..." value="<?= htmlspecialchars($filtros['autor']) ?>">
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="modal_filtro_editora" class="form-label">Editora</label>
              <select class="form-select" id="modal_filtro_editora" name="filtro_editora">
                <option value="">Todas as editoras</option>
                <?php foreach ($editoras as $editora): ?>
                  <option value="<?= htmlspecialchars($editora['editora']) ?>" 
                          <?= $filtros['editora'] == $editora['editora'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($editora['editora']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="modal_filtro_ano" class="form-label">Ano de Publicação</label>
              <select class="form-select" id="modal_filtro_ano" name="filtro_ano">
                <option value="">Todos os anos</option>
                <?php foreach ($anos as $ano): ?>
                  <option value="<?= $ano['ano_publicacao'] ?>" 
                          <?= $filtros['ano'] == $ano['ano_publicacao'] ? 'selected' : '' ?>>
                    <?= $ano['ano_publicacao'] ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="modal_filtro_exemplares_min" class="form-label">Exemplares (mínimo)</label>
              <input type="number" class="form-control" id="modal_filtro_exemplares_min" name="filtro_exemplares_min" 
                     value="<?= htmlspecialchars($filtros['exemplares_min']) ?>" min="1" placeholder="Mínimo">
            </div>
            <div class="col-md-6">
              <label for="modal_filtro_exemplares_max" class="form-label">Exemplares (máximo)</label>
              <input type="number" class="form-control" id="modal_filtro_exemplares_max" name="filtro_exemplares_max" 
                     value="<?= htmlspecialchars($filtros['exemplares_max']) ?>" min="1" placeholder="Máximo">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrar</button>
          <a href="books.php" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i> Limpar</a>
        </div>
      </form>
    </div>
  </div>
</div>



<script>
function editBook(book) {
    document.getElementById('bookId').value = book.id;
    document.getElementById('titulo').value = book.titulo;
    document.getElementById('autor').value = book.autor;
    document.getElementById('editora').value = book.editora || '';
    document.getElementById('ano_publicacao').value = book.ano_publicacao || '';
    document.getElementById('num_exemplares').value = book.num_exemplares;
    
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Atualizar Livro';
    document.getElementById('submitBtn').name = 'update';
    document.getElementById('cancelBtn').style.display = 'inline-block';
    
    // Scroll para o formulário
    document.getElementById('bookForm').scrollIntoView({ behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('bookForm').reset();
    document.getElementById('bookId').value = '';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus"></i> Adicionar Livro';
    document.getElementById('submitBtn').name = 'add';
    document.getElementById('cancelBtn').style.display = 'none';
}

// Funcionalidade dos filtros
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit quando mudar select de editora ou ano
    const editoraSelect = document.getElementById('filtro_editora');
    const anoSelect = document.getElementById('filtro_ano');
    
    if (editoraSelect) {
        editoraSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    if (anoSelect) {
        anoSelect.addEventListener('change', function() {
            this.form.submit();
        });
    }
    
    // Validação dos campos de exemplares
    const exemplaresMin = document.getElementById('filtro_exemplares_min');
    const exemplaresMax = document.getElementById('filtro_exemplares_max');
    
    if (exemplaresMin && exemplaresMax) {
        exemplaresMin.addEventListener('input', function() {
            if (this.value && exemplaresMax.value && parseInt(this.value) > parseInt(exemplaresMax.value)) {
                exemplaresMax.value = this.value;
            }
        });
        
        exemplaresMax.addEventListener('input', function() {
            if (this.value && exemplaresMin.value && parseInt(this.value) < parseInt(exemplaresMin.value)) {
                exemplaresMin.value = this.value;
            }
        });
    }
    
    // Busca em tempo real nos campos de texto
    const tituloInput = document.getElementById('filtro_titulo');
    const autorInput = document.getElementById('filtro_autor');
    
    let searchTimeout;
    
    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (tituloInput.value.length >= 2 || autorInput.value.length >= 2) {
                tituloInput.form.submit();
            }
        }, 500);
    }
    
    if (tituloInput) {
        tituloInput.addEventListener('input', performSearch);
    }
    
    if (autorInput) {
        autorInput.addEventListener('input', performSearch);
    }
});

let bookIdToDelete = null;

function openDeleteModal(bookId) {
    bookIdToDelete = bookId;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (bookIdToDelete) {
        // Criar um formulário dinamicamente para enviar o POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = bookIdToDelete;

        const inputDelete = document.createElement('input');
        inputDelete.type = 'hidden';
        inputDelete.name = 'delete';
        inputDelete.value = '1';

        form.appendChild(inputId);
        form.appendChild(inputDelete);
        document.body.appendChild(form);
        form.submit();
    }
});

function openEditModal(book) {
    document.getElementById('editBookId').value = book.id;
    document.getElementById('editTitulo').value = book.titulo;
    document.getElementById('editAutor').value = book.autor;
    document.getElementById('editEditora').value = book.editora || '';
    document.getElementById('editAno').value = book.ano_publicacao || '';
    document.getElementById('editNumExemplares').value = book.num_exemplares;

    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit para selects
    const editoraSelect = document.getElementById('modal_filtro_editora');
    const anoSelect = document.getElementById('modal_filtro_ano');

    if (editoraSelect) editoraSelect.addEventListener('change', () => editoraSelect.form.submit());
    if (anoSelect) anoSelect.addEventListener('change', () => anoSelect.form.submit());

    // Validação exemplares min/max
    const minInput = document.getElementById('modal_filtro_exemplares_min');
    const maxInput = document.getElementById('modal_filtro_exemplares_max');

    if (minInput && maxInput) {
        minInput.addEventListener('input', () => {
            if (minInput.value && maxInput.value && parseInt(minInput.value) > parseInt(maxInput.value)) {
                maxInput.value = minInput.value;
            }
        });
        maxInput.addEventListener('input', () => {
            if (maxInput.value && minInput.value && parseInt(maxInput.value) < parseInt(minInput.value)) {
                minInput.value = maxInput.value;
            }
        });
    }

    // Busca em tempo real para título e autor
    let searchTimeout;
    const titleInput = document.getElementById('modal_filtro_titulo');
    const authorInput = document.getElementById('modal_filtro_autor');

    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (titleInput.value.length >= 2 || authorInput.value.length >= 2) {
                titleInput.form.submit();
            }
        }, 500);
    }

    if (titleInput) titleInput.addEventListener('input', performSearch);
    if (authorInput) authorInput.addEventListener('input', performSearch);
});
</script>

<?php include '../includes/footer-pages.php'; ?>
