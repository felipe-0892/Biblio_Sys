<?php
require_once '../config/db.php';

// Verificar se as dependências estão instaladas
$dependencias_instaladas = file_exists('../vendor/autoload.php');
if ($dependencias_instaladas) {
    require_once '../vendor/autoload.php';
}

$pageTitle = 'Relatórios';
$message = '';
$messageType = '';

// Processar exportação
if (isset($_GET['export']) && $dependencias_instaladas) {
    $tipo = $_GET['export'];
    $formato = $_GET['format'] ?? 'pdf';
    
    try {
        switch($tipo) {
            case 'livros_mais_emprestados':
                exportLivrosMaisEmprestados($formato);
                break;
            case 'usuarios_pendentes':
                exportUsuariosPendentes($formato);
                break;
            case 'estatisticas_gerais':
                exportEstatisticasGerais($formato);
                break;
            case 'emprestimos_mensais':
                exportEmprestimosMensais($formato);
                break;
            case 'relatorio_completo':
                exportRelatorioCompleto($formato);
                break;
            default:
                throw new Exception('Tipo de relatório inválido.');
        }
        exit;
    } catch (Exception $e) {
        $message = 'Erro ao exportar relatório: ' . $e->getMessage();
        $messageType = 'danger';
    }
} elseif (isset($_GET['export']) && !$dependencias_instaladas) {
    $message = 'Dependências não instaladas. Execute "composer install" para gerar relatórios.';
    $messageType = 'warning';
}

// Buscar dados para relatórios
try {
    // Livros mais emprestados
    $livrosMaisEmprestados = $pdo->query("
        SELECT b.titulo, b.autor, b.editora, COUNT(l.id) as total_emprestimos 
        FROM books b 
        LEFT JOIN loans l ON b.id = l.book_id 
        GROUP BY b.id 
        ORDER BY total_emprestimos DESC 
        LIMIT 10
    ")->fetchAll();
    
    // Usuários com devoluções pendentes
    $usuariosPendentes = $pdo->query("
        SELECT u.nome, u.matricula, u.email, u.telefone, COUNT(l.id) as emprestimos_pendentes 
        FROM users u 
        JOIN loans l ON u.id = l.user_id 
        WHERE l.status IN ('ativo', 'vencido') 
        GROUP BY u.id 
        ORDER BY emprestimos_pendentes DESC
    ")->fetchAll();
    
    // Estatísticas gerais
    $stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM books) as total_livros,
            (SELECT COUNT(*) FROM users) as total_usuarios,
            (SELECT COUNT(*) FROM loans WHERE status = 'ativo') as emprestimos_ativos,
            (SELECT COUNT(*) FROM loans WHERE status = 'vencido') as emprestimos_vencidos,
            (SELECT COUNT(*) FROM loans WHERE status = 'devolvido') as emprestimos_devolvidos,
            (SELECT COUNT(*) FROM loans) as total_emprestimos
    ")->fetch();
    
    // Empréstimos por mês (últimos 6 meses)
    $emprestimosPorMes = $pdo->query("
        SELECT 
            DATE_FORMAT(data_emprestimo, '%Y-%m') as mes,
            DATE_FORMAT(data_emprestimo, '%M %Y') as mes_formatado,
            COUNT(*) as total
        FROM loans 
        WHERE data_emprestimo >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(data_emprestimo, '%Y-%m')
        ORDER BY mes DESC
    ")->fetchAll();
    
} catch (PDOException $e) {
    $message = 'Erro ao carregar relatórios: ' . $e->getMessage();
    $messageType = 'danger';
    $livrosMaisEmprestados = [];
    $usuariosPendentes = [];
    $stats = ['total_livros' => 0, 'total_usuarios' => 0, 'emprestimos_ativos' => 0, 'emprestimos_vencidos' => 0, 'emprestimos_devolvidos' => 0, 'total_emprestimos' => 0];
    $emprestimosPorMes = [];
}


include '../includes/nav-pages.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-chart-bar"></i> Relatórios e Estatísticas</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (!$dependencias_instaladas): ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Dependências Não Instaladas</h5>
                <p>Para gerar relatórios em PDF e Excel, é necessário instalar as dependências do Composer.</p>
                <ol>
                    <li>Abra o terminal/prompt na pasta do projeto</li>
                    <li>Execute: <code>composer install</code></li>
                    <li>Aguarde a instalação das dependências</li>
                </ol>
                <p class="mb-0"><small>Se não tiver o Composer instalado, baixe em: <a href="https://getcomposer.org/" target="_blank">getcomposer.org</a></small></p>
            </div>
            <?php endif; ?>
            
            <!-- Botões de Exportação -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-download"></i> Exportar Relatórios</h5>
                </div>
                <div class="card-body">
                    <?php if ($dependencias_instaladas): ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <h6>Exportar para PDF</h6>
                                    <div class="btn-group-vertical w-100">
                                        <a href="?export=livros_mais_emprestados&format=pdf" class="btn btn-outline-danger btn-sm mb-1">
                                            Livros Mais Emprestados
                                        </a>
                                        <a href="?export=usuarios_pendentes&format=pdf" class="btn btn-outline-danger btn-sm mb-1">
                                            Usuários Pendentes
                                        </a>
                                        <a href="?export=estatisticas_gerais&format=pdf" class="btn btn-outline-danger btn-sm mb-1">
                                            Estatísticas Gerais
                                        </a>
                                        <a href="?export=emprestimos_mensais&format=pdf" class="btn btn-outline-danger btn-sm mb-1">
                                            Empréstimos Mensais
                                        </a>
                                        <a href="?export=relatorio_completo&format=pdf" class="btn btn-danger btn-sm">
                                            Relatório Completo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                                    <h6>Exportar para Excel</h6>
                                    <div class="btn-group-vertical w-100">
                                        <a href="?export=livros_mais_emprestados&format=xlsx" class="btn btn-outline-success btn-sm mb-1">
                                            Livros Mais Emprestados
                                        </a>
                                        <a href="?export=usuarios_pendentes&format=xlsx" class="btn btn-outline-success btn-sm mb-1">
                                            Usuários Pendentes
                                        </a>
                                        <a href="?export=estatisticas_gerais&format=xlsx" class="btn btn-outline-success btn-sm mb-1">
                                            Estatísticas Gerais
                                        </a>
                                        <a href="?export=emprestimos_mensais&format=xlsx" class="btn btn-outline-success btn-sm mb-1">
                                            Empréstimos Mensais
                                        </a>
                                        <a href="?export=relatorio_completo&format=xlsx" class="btn btn-success btn-sm">
                                            Relatório Completo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fas fa-download fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Instale as dependências para habilitar a exportação de relatórios.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Estatísticas Gerais -->
            <div class="row mb-4">
                <div class="col-md-2 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-book fa-2x text-primary mb-2"></i>
                            <h5 class="card-title">Total de Livros</h5>
                            <p class="card-text display-6 text-primary"><?= $stats['total_livros'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x text-success mb-2"></i>
                            <h5 class="card-title">Total de Usuários</h5>
                            <p class="card-text display-6 text-success"><?= $stats['total_usuarios'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-exchange-alt fa-2x text-warning mb-2"></i>
                            <h5 class="card-title">Empréstimos Ativos</h5>
                            <p class="card-text display-6 text-warning"><?= $stats['emprestimos_ativos'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                            <h5 class="card-title">Empréstimos Vencidos</h5>
                            <p class="card-text display-6 text-danger"><?= $stats['emprestimos_vencidos'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-check-circle fa-2x text-secondary mb-2"></i>
                            <h5 class="card-title">Empréstimos Devolvidos</h5>
                            <p class="card-text display-6 text-secondary"><?= $stats['emprestimos_devolvidos'] ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                            <h5 class="card-title">Total Geral</h5>
                            <p class="card-text display-6 text-info"><?= $stats['total_emprestimos'] ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Livros Mais Emprestados -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-trophy"></i> Livros Mais Emprestados</h5>
                            <?php if ($dependencias_instaladas): ?>
                            <div>
                                <a href="?export=livros_mais_emprestados&format=pdf" class="btn btn-sm btn-danger">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <a href="?export=livros_mais_emprestados&format=xlsx" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($livrosMaisEmprestados)): ?>
                                <p class="text-muted">Nenhum empréstimo registrado ainda.</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($livrosMaisEmprestados as $index => $livro): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($livro['titulo']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($livro['autor']) ?></small>
                                                <?php if ($livro['editora']): ?>
                                                    <br><small class="text-muted">Editora: <?= htmlspecialchars($livro['editora']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">
                                                <?= $livro['total_emprestimos'] ?> empréstimos
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Usuários com Devoluções Pendentes -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-clock"></i> Usuários com Devoluções Pendentes</h5>
                            <?php if ($dependencias_instaladas): ?>
                            <div>
                                <a href="?export=usuarios_pendentes&format=pdf" class="btn btn-sm btn-danger">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <a href="?export=usuarios_pendentes&format=xlsx" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($usuariosPendentes)): ?>
                                <p class="text-muted">Nenhuma devolução pendente.</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($usuariosPendentes as $usuario): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($usuario['nome']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($usuario['matricula']) ?></small>
                                                <?php if ($usuario['email']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($usuario['email']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge bg-warning rounded-pill">
                                                <?= $usuario['emprestimos_pendentes'] ?> pendente(s)
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Empréstimos por Mês -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Empréstimos por Mês (Últimos 6 meses)</h5>
                            <?php if ($dependencias_instaladas): ?>
                            <div>
                                <a href="?export=emprestimos_mensais&format=pdf" class="btn btn-sm btn-danger">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                                <a href="?export=emprestimos_mensais&format=xlsx" class="btn btn-sm btn-success">
                                    <i class="fas fa-file-excel"></i> Excel
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if (empty($emprestimosPorMes)): ?>
                                <p class="text-muted">Nenhum empréstimo registrado nos últimos 6 meses.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Mês</th>
                                                <th>Total de Empréstimos</th>
                                                <th>Progresso</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $maxEmprestimos = max(array_column($emprestimosPorMes, 'total'));
                                            foreach ($emprestimosPorMes as $mes): 
                                                $percentual = $maxEmprestimos > 0 ? ($mes['total'] / $maxEmprestimos) * 100 : 0;
                                            ?>
                                                <tr>
                                                    <td><?= $mes['mes_formatado'] ?></td>
                                                    <td><?= $mes['total'] ?></td>
                                                    <td>
                                                        <div class="progress" style="height: 20px;">
                                                            <div class="progress-bar" role="progressbar" style="width: <?= $percentual ?>%">
                                                                <?= $mes['total'] ?>
                                                            </div>
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
    </div>
</div>

<?php include '../includes/footer-pages.php'; ?>

<?php

// Funções de Exportação (só são executadas se as dependências estiverem instaladas)
function exportLivrosMaisEmprestados($formato) {
    global $pdo;
    
    $dados = $pdo->query("
        SELECT b.titulo, b.autor, b.editora, COUNT(l.id) as total_emprestimos 
        FROM books b 
        LEFT JOIN loans l ON b.id = l.book_id 
        GROUP BY b.id 
        ORDER BY total_emprestimos DESC 
        LIMIT 20
    ")->fetchAll();
    
    $titulo = "Livros Mais Emprestados";
    $cabecalhos = ['Título', 'Autor', 'Editora', 'Total de Empréstimos'];
    
    if ($formato === 'pdf') {
        exportPDF($titulo, $cabecalhos, $dados);
    } else {
        exportXLSX($titulo, $cabecalhos, $dados);
    }
}

function exportUsuariosPendentes($formato) {
    global $pdo;
    
    $dados = $pdo->query("
        SELECT u.nome, u.matricula, u.email, u.telefone, COUNT(l.id) as emprestimos_pendentes 
        FROM users u 
        JOIN loans l ON u.id = l.user_id 
        WHERE l.status IN ('ativo', 'vencido') 
        GROUP BY u.id 
        ORDER BY emprestimos_pendentes DESC
    ")->fetchAll();
    
    $titulo = "Usuários com Devoluções Pendentes";
    $cabecalhos = ['Nome', 'Matrícula', 'E-mail', 'Telefone', 'Empréstimos Pendentes'];
    
    if ($formato === 'pdf') {
        exportPDF($titulo, $cabecalhos, $dados);
    } else {
        exportXLSX($titulo, $cabecalhos, $dados);
    }
}

function exportEstatisticasGerais($formato) {
    global $pdo;
    
    $stats = $pdo->query("
        SELECT 
            'Total de Livros' as descricao,
            (SELECT COUNT(*) FROM books) as valor
        UNION ALL
        SELECT 
            'Total de Usuários' as descricao,
            (SELECT COUNT(*) FROM users) as valor
        UNION ALL
        SELECT 
            'Empréstimos Ativos' as descricao,
            (SELECT COUNT(*) FROM loans WHERE status = 'ativo') as valor
        UNION ALL
        SELECT 
            'Empréstimos Vencidos' as descricao,
            (SELECT COUNT(*) FROM loans WHERE status = 'vencido') as valor
        UNION ALL
        SELECT 
            'Empréstimos Devolvidos' as descricao,
            (SELECT COUNT(*) FROM loans WHERE status = 'devolvido') as valor
        UNION ALL
        SELECT 
            'Total de Empréstimos' as descricao,
            (SELECT COUNT(*) FROM loans) as valor
    ")->fetchAll();
    
    $titulo = "Estatísticas Gerais do Sistema";
    $cabecalhos = ['Descrição', 'Valor'];
    
    if ($formato === 'pdf') {
        exportPDF($titulo, $cabecalhos, $stats);
    } else {
        exportXLSX($titulo, $cabecalhos, $stats);
    }
}

function exportEmprestimosMensais($formato) {
    global $pdo;
    
    $dados = $pdo->query("
        SELECT 
            DATE_FORMAT(data_emprestimo, '%M %Y') as mes,
            COUNT(*) as total
        FROM loans 
        WHERE data_emprestimo >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY DATE_FORMAT(data_emprestimo, '%Y-%m')
        ORDER BY data_emprestimo DESC
    ")->fetchAll();
    
    $titulo = "Empréstimos por Mês (Últimos 12 meses)";
    $cabecalhos = ['Mês', 'Total de Empréstimos'];
    
    if ($formato === 'pdf') {
        exportPDF($titulo, $cabecalhos, $dados);
    } else {
        exportXLSX($titulo, $cabecalhos, $dados);
    }
}

function exportRelatorioCompleto($formato) {
    global $pdo;
    
    // Buscar todos os dados
    $livros = $pdo->query("SELECT titulo, autor, editora, num_exemplares FROM books ORDER BY titulo")->fetchAll();
    $usuarios = $pdo->query("SELECT nome, matricula, email, telefone FROM users ORDER BY nome")->fetchAll();
    $emprestimos = $pdo->query("
        SELECT l.id, b.titulo, u.nome, u.matricula, l.data_emprestimo, l.data_devolucao, l.status 
        FROM loans l 
        JOIN books b ON l.book_id = b.id 
        JOIN users u ON l.user_id = u.id 
        ORDER BY l.data_emprestimo DESC
    ")->fetchAll();
    
    if ($formato === 'pdf') {
        exportRelatorioCompletoPDF($livros, $usuarios, $emprestimos);
    } else {
        exportRelatorioCompletoXLSX($livros, $usuarios, $emprestimos);
    }
}

// Funções auxiliares de exportação
function exportPDF($titulo, $cabecalhos, $dados) {
    $mpdf = new \Mpdf\Mpdf();
    
    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .date { font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8f9fa; border: 1px solid #ddd; padding: 8px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; }
    </style>
    
    <div class="header">
        <div class="title">' . $titulo . '</div>
        <div class="date">Gerado em: ' . date('d/m/Y H:i:s') . '</div>
    </div>
    
    <table>
        <thead>
            <tr>';
    
    foreach ($cabecalhos as $cabecalho) {
        $html .= '<th>' . $cabecalho . '</th>';
    }
    
    $html .= '</tr>
        </thead>
        <tbody>';
    
    foreach ($dados as $linha) {
        $html .= '<tr>';
        foreach ($linha as $valor) {
            $html .= '<td>' . htmlspecialchars($valor ?? '') . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</tbody>
    </table>';
    
    $mpdf->WriteHTML($html);
    $mpdf->Output($titulo . '.pdf', 'D');
}

function exportXLSX($titulo, $cabecalhos, $dados) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Título
    $sheet->setCellValue('A1', $titulo);
    $sheet->mergeCells('A1:E1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    
    // Data de geração
    $sheet->setCellValue('A2', 'Gerado em: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A2:E2');
    
    // Cabeçalhos
    $coluna = 'A';
    foreach ($cabecalhos as $cabecalho) {
        $sheet->setCellValue($coluna . '4', $cabecalho);
        $sheet->getStyle($coluna . '4')->getFont()->setBold(true);
        $coluna++;
    }
    
    // Dados
    $linha = 5;
    foreach ($dados as $item) {
        $coluna = 'A';
        foreach ($item as $valor) {
            $sheet->setCellValue($coluna . $linha, $valor);
            $coluna++;
        }
        $linha++;
    }
    
    // Auto size columns
    foreach (range('A', $coluna) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $titulo . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
}

function exportRelatorioCompletoPDF($livros, $usuarios, $emprestimos) {
    $mpdf = new \Mpdf\Mpdf();
    
    $html = '
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { font-size: 20px; font-weight: bold; }
        .section { margin: 20px 0; }
        .section-title { font-size: 16px; font-weight: bold; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8f9fa; border: 1px solid #ddd; padding: 8px; text-align: left; }
        td { border: 1px solid #ddd; padding: 8px; font-size: 10px; }
    </style>
    
    <div class="header">
        <div class="title">Relatório Completo - Sistema de Biblioteca</div>
        <div class="date">Gerado em: ' . date('d/m/Y H:i:s') . '</div>
    </div>';
    
    // Seção Livros
    $html .= '<div class="section">
        <div class="section-title">Livros Cadastrados (' . count($livros) . ')</div>
        <table>
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Editora</th>
                    <th>Exemplares</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($livros as $livro) {
        $html .= '<tr>
            <td>' . htmlspecialchars($livro['titulo']) . '</td>
            <td>' . htmlspecialchars($livro['autor']) . '</td>
            <td>' . htmlspecialchars($livro['editora'] ?? '') . '</td>
            <td>' . $livro['num_exemplares'] . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table></div>';
    
    // Seção Usuários
    $html .= '<div class="section">
        <div class="section-title">Usuários Cadastrados (' . count($usuarios) . ')</div>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Matrícula</th>
                    <th>E-mail</th>
                    <th>Telefone</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($usuarios as $usuario) {
        $html .= '<tr>
            <td>' . htmlspecialchars($usuario['nome']) . '</td>
            <td>' . htmlspecialchars($usuario['matricula']) . '</td>
            <td>' . htmlspecialchars($usuario['email'] ?? '') . '</td>
            <td>' . htmlspecialchars($usuario['telefone'] ?? '') . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table></div>';
    
    // Seção Empréstimos
    $html .= '<div class="section">
        <div class="section-title">Histórico de Empréstimos (' . count($emprestimos) . ')</div>
        <table>
            <thead>
                <tr>
                    <th>Livro</th>
                    <th>Usuário</th>
                    <th>Matrícula</th>
                    <th>Data Empréstimo</th>
                    <th>Data Devolução</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($emprestimos as $emprestimo) {
        $html .= '<tr>
            <td>' . htmlspecialchars($emprestimo['titulo']) . '</td>
            <td>' . htmlspecialchars($emprestimo['nome']) . '</td>
            <td>' . htmlspecialchars($emprestimo['matricula']) . '</td>
            <td>' . date('d/m/Y', strtotime($emprestimo['data_emprestimo'])) . '</td>
            <td>' . date('d/m/Y', strtotime($emprestimo['data_devolucao'])) . '</td>
            <td>' . $emprestimo['status'] . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table></div>';
    
    $mpdf->WriteHTML($html);
    $mpdf->Output('Relatorio_Completo_Biblioteca.pdf', 'D');
}

function exportRelatorioCompletoXLSX($livros, $usuarios, $emprestimos) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    // Planilha de Livros
    $spreadsheet->setActiveSheetIndex(0);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Livros');
    
    $sheet->setCellValue('A1', 'Relatório Completo - Sistema de Biblioteca');
    $sheet->mergeCells('A1:E1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    
    $sheet->setCellValue('A2', 'Gerado em: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A2:E2');
    
    // Cabeçalhos Livros
    $cabecalhosLivros = ['Título', 'Autor', 'Editora', 'Número de Exemplares'];
    $coluna = 'A';
    foreach ($cabecalhosLivros as $cabecalho) {
        $sheet->setCellValue($coluna . '4', $cabecalho);
        $sheet->getStyle($coluna . '4')->getFont()->setBold(true);
        $coluna++;
    }
    
    // Dados Livros
    $linha = 5;
    foreach ($livros as $livro) {
        $sheet->setCellValue('A' . $linha, $livro['titulo']);
        $sheet->setCellValue('B' . $linha, $livro['autor']);
        $sheet->setCellValue('C' . $linha, $livro['editora'] ?? '');
        $sheet->setCellValue('D' . $linha, $livro['num_exemplares']);
        $linha++;
    }
    
    // Planilha de Usuários
    $sheetUsuarios = $spreadsheet->createSheet();
    $sheetUsuarios->setTitle('Usuários');
    
    $sheetUsuarios->setCellValue('A1', 'Usuários Cadastrados');
    $sheetUsuarios->mergeCells('A1:D1');
    $sheetUsuarios->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    
    $cabecalhosUsuarios = ['Nome', 'Matrícula', 'E-mail', 'Telefone'];
    $coluna = 'A';
    foreach ($cabecalhosUsuarios as $cabecalho) {
        $sheetUsuarios->setCellValue($coluna . '3', $cabecalho);
        $sheetUsuarios->getStyle($coluna . '3')->getFont()->setBold(true);
        $coluna++;
    }
    
    $linha = 4;
    foreach ($usuarios as $usuario) {
        $sheetUsuarios->setCellValue('A' . $linha, $usuario['nome']);
        $sheetUsuarios->setCellValue('B' . $linha, $usuario['matricula']);
        $sheetUsuarios->setCellValue('C' . $linha, $usuario['email'] ?? '');
        $sheetUsuarios->setCellValue('D' . $linha, $usuario['telefone'] ?? '');
        $linha++;
    }
    
    // Planilha de Empréstimos
    $sheetEmprestimos = $spreadsheet->createSheet();
    $sheetEmprestimos->setTitle('Empréstimos');
    
    $sheetEmprestimos->setCellValue('A1', 'Histórico de Empréstimos');
    $sheetEmprestimos->mergeCells('A1:F1');
    $sheetEmprestimos->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    
    $cabecalhosEmprestimos = ['Livro', 'Usuário', 'Matrícula', 'Data Empréstimo', 'Data Devolução', 'Status'];
    $coluna = 'A';
    foreach ($cabecalhosEmprestimos as $cabecalho) {
        $sheetEmprestimos->setCellValue($coluna . '3', $cabecalho);
        $sheetEmprestimos->getStyle($coluna . '3')->getFont()->setBold(true);
        $coluna++;
    }
    
    $linha = 4;
    foreach ($emprestimos as $emprestimo) {
        $sheetEmprestimos->setCellValue('A' . $linha, $emprestimo['titulo']);
        $sheetEmprestimos->setCellValue('B' . $linha, $emprestimo['nome']);
        $sheetEmprestimos->setCellValue('C' . $linha, $emprestimo['matricula']);
        $sheetEmprestimos->setCellValue('D' . $linha, date('d/m/Y', strtotime($emprestimo['data_emprestimo'])));
        $sheetEmprestimos->setCellValue('E' . $linha, date('d/m/Y', strtotime($emprestimo['data_devolucao'])));
        $sheetEmprestimos->setCellValue('F' . $linha, $emprestimo['status']);
        $linha++;
    }
    
    // Auto size para todas as colunas em todas as planilhas
    $sheets = [$sheet, $sheetUsuarios, $sheetEmprestimos];
    foreach ($sheets as $currentSheet) {
        foreach (range('A', 'F') as $col) {
            $currentSheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Relatorio_Completo_Biblioteca.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer->save('php://output');
}
?>