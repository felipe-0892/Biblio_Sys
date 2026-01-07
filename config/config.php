<?php
// Configurações do sistema
define('SITE_NAME', 'Sistema de Biblioteca');
define('SITE_VERSION', '1.0.0');
define('MAX_LOAN_DAYS', 30);
define('DAILY_FINE', 2.00);

// Configurações de sessão
session_start();

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Incluir arquivo do banco de dados
require_once 'db.php';

// Função para redirecionamento
function redirect($url) {
    header("Location: $url");
    exit();
}

// Função para verificar autenticação
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        redirect('../index.php');
    }
}

// Função para debug
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
?>