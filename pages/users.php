<?php
require_once '../config/db.php';

$pageTitle = 'Gerenciamento de Usuários';
$message = '';
$messageType = '';

// Função de limpeza e formatação do telefone
function formatPhone($phone) {
    // Remove tudo que não for número
    $digits = preg_replace('/\D/', '', $phone);

    // Formata de acordo com o número de dígitos
    if (strlen($digits) === 11) {
        // (XX) XXXXX-XXXX
        return sprintf("(%s) %s-%s",
            substr($digits, 0, 2),
            substr($digits, 2, 5),
            substr($digits, 7)
        );
    } elseif (strlen($digits) === 10) {
        // (XX) XXXX-XXXX
        return sprintf("(%s) %s-%s",
            substr($digits, 0, 2),
            substr($digits, 2, 4),
            substr($digits, 6)
        );
    } else {
        // Retorna apenas os números se estiver fora do padrão
        return $digits;
    }
}

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['add']) || isset($_POST['update'])) {
            $nome = trim($_POST['nome']);
            $matricula = trim($_POST['matricula']);
            $email = trim($_POST['email']);
            $telefone = trim($_POST['telefone']);

            // Validação
            if (empty($nome) || empty($matricula)) {
                throw new Exception('Nome e matrícula são obrigatórios.');
            }

            // Verificar se matrícula já existe (diferente para update e add)
            if (isset($_POST['add'])) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE matricula = ?");
                $stmt->execute([$matricula]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Matrícula já cadastrada.');
                }
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE matricula = ? AND id != ?");
                $stmt->execute([$matricula, $id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Matrícula já cadastrada para outro usuário.');
                }
            }

            // Validar email se fornecido
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido.');
            }

            // Validação e formatação de telefone
            if (!empty($telefone)) {
                $telefoneLimpo = preg_replace('/\D/', '', $telefone);
                if (strlen($telefoneLimpo) < 10 || strlen($telefoneLimpo) > 11) {
                    throw new Exception('Telefone inválido. Use o formato (XX) XXXXX-XXXX.');
                }
                $telefone = formatPhone($telefone);
            } else {
                $telefone = null;
            }

            if (isset($_POST['add'])) {
                $stmt = $pdo->prepare("INSERT INTO users (nome, matricula, email, telefone) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nome, $matricula, $email, $telefone]);
                $message = 'Usuário adicionado com sucesso!';
                $messageType = 'success';
            } else {
                $stmt = $pdo->prepare("UPDATE users SET nome=?, matricula=?, email=?, telefone=? WHERE id=?");
                $stmt->execute([$nome, $matricula, $email, $telefone, $id]);
                $message = 'Usuário atualizado com sucesso!';
                $messageType = 'success';
            }

        } elseif (isset($_POST['delete'])) {
            $id = (int)$_POST['id'];

            // Verificar se há empréstimos ativos
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = ? AND status = 'ativo'");
            $stmt->execute([$id]);
            $activeLoans = $stmt->fetchColumn();

            if ($activeLoans > 0) {
                throw new Exception('Não é possível excluir um usuário com empréstimos ativos.');
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
            $stmt->execute([$id]);
            $message = 'Usuário excluído com sucesso!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

// Buscar usuários
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY nome")->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $message = 'Erro ao carregar usuários.';
    $messageType = 'danger';
}

include '../includes/nav-pages.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-users"></i> Gerenciamento de Usuários</h2>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Formulário de Cadastro/Edição -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Adicionar Novo Usuário</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="userForm">
                        <input type="hidden" name="id" id="userId" value="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nome" class="form-label">Nome Completo *</label>
                                <input type="text" name="nome" id="nome" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="matricula" class="form-label">Matrícula *</label>
                                <input type="text" name="matricula" id="matricula" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" name="email" id="email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="telefone" class="form-label">Telefone</label>
                                <input type="text" name="telefone" id="telefone" class="form-control" placeholder="(11) 99999-9999" maxlength="15">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" name="add" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-plus"></i> Adicionar Usuário
                            </button>
                            <!-- <button type="button" class="btn btn-warning" onclick="openFilterModal()">
                                <i class="fas fa-filter"></i> Filtrar
                            </button> -->

                            <button type="button" class="btn btn-secondary" id="cancelBtn" style="display: none;" onclick="cancelEdit()">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Usuários -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Lista de Usuários</h5>
                    <span class="badge bg-primary"><?= count($users) ?> usuários</span>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nenhum usuário cadastrado ainda.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <!-- <th>ID</th> -->
                                        <th>Nome</th>
                                        <th>Matrícula</th>
                                        <th>E-mail</th>
                                        <th>Telefone</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <!-- <td><?= $user['id'] ?></td> -->
                                        <td><?= htmlspecialchars($user['nome']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($user['matricula']) ?></span></td>
                                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($user['telefone'] ?? '') ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick='openEditModal(<?= json_encode($user) ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $user['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
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
<!-- MODAIS -->
<!-- Modal de Edição de Usuário -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editForm">
        <input type="hidden" name="id" id="editUserId">
        <div class="modal-header">
          <h5 class="modal-title" id="editModalLabel">Editar Usuário</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nome *</label>
            <input type="text" name="nome" id="editNome" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Matrícula *</label>
            <input type="text" name="matricula" id="editMatricula" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" id="editEmail" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" id="editTelefone" class="form-control" placeholder="(11) 99999-9999" maxlength="15">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="update" class="btn btn-primary">Atualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="deleteForm">
        <input type="hidden" name="id" id="deleteUserId">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Confirmar Exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          Tem certeza que deseja excluir este usuário?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="delete" class="btn btn-danger">Excluir</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal de Filtro -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="GET" id="filterForm">
        <div class="modal-header">
          <h5 class="modal-title" id="filterModalLabel">Filtrar Usuários</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" id="filterName" class="form-control" value="<?= $_GET['nome'] ?? '' ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Matrícula</label>
            <input type="text" name="matricula" id="filterMatricula" class="form-control" value="<?= $_GET['matricula'] ?? '' ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="text" name="email" id="filterEmail" class="form-control" value="<?= $_GET['email'] ?? '' ?>">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Função para abrir modal de edição com dados preenchidos
function openEditModal(user){
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editNome').value = user.nome;
    document.getElementById('editMatricula').value = user.matricula;
    document.getElementById('editEmail').value = user.email || '';
    document.getElementById('editTelefone').value = user.telefone || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// Função para abrir modal de exclusão
function confirmDelete(id){
    document.getElementById('deleteUserId').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Função para abrir modal de filtro
function openFilterModal(){
    new bootstrap.Modal(document.getElementById('filterModal')).show();
}

// Máscara de telefone no modal de edição
document.getElementById('editTelefone')?.addEventListener('input', function(e){
    let value = e.target.value.replace(/\D/g, '');
    if(value.length>11) value = value.slice(0,11);
    if(value.length>6) e.target.value = `(${value.slice(0,2)}) ${value.slice(2,7)}-${value.slice(7)}`;
    else if(value.length>2) e.target.value = `(${value.slice(0,2)}) ${value.slice(2)}`;
    else e.target.value = value;
});
</script>

<?php include '../includes/footer-pages.php'; ?>
