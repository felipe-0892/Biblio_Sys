# 📚 Sistema de Gerenciamento de Biblioteca Escolar

Sistema web desenvolvido em **PHP** para automação completa do gerenciamento de bibliotecas escolares, incluindo controle de acervo, cadastro de usuários, empréstimos, devoluções e geração de relatórios detalhados.



## 📋 Sumário

- [Sobre o Projeto](#sobre-o-projeto)
- [Contexto e Problema](#contexto-e-problema)
- [Objetivos do Sistema](#objetivos-do-sistema)
- [Funcionalidades](#funcionalidades)
- [Requisitos do Sistema](#requisitos-do-sistema)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Como Usar](#como-usar)
- [Arquitetura e Design](#arquitetura-e-design)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Diagrama de Entidades](#diagrama-de-entidades)
- [Desenvolvimento](#desenvolvimento)
- [Testes](#testes)
- [Implantação](#implantação)
- [Documentação Adicional](#documentação-adicional)
- [Suporte](#suporte)

---

## 🎯 Sobre o Projeto

Este sistema foi desenvolvido para modernizar e automatizar os processos de uma biblioteca escolar, substituindo os métodos manuais (planilhas e cadernos) por uma solução informatizada, intuitiva e eficiente.

### Contexto e Problema

A biblioteca operava com os seguintes problemas:

- ❌ Cadastro de livros e usuários feito em **planilhas** desatualizadas
- ❌ Empréstimos e devoluções registrados **manualmente em cadernos**
- ❌ **Sem controle automático** de disponibilidade de livros
- ❌ Geração de relatórios **consumia muito tempo e esforço**
- ❌ Alta probabilidade de erros humanos
- ❌ Dificuldade em atender demanda crescente

### Objetivos do Sistema

Desenvolver uma solução web que atenda às necessidades de:

1. ✅ **Cadastro de Livros**: Registrar informações como título, autor, editora, ano de publicação, número de exemplares, ISBN
2. ✅ **Cadastro de Usuários**: Registrar alunos e funcionários com nome, matrícula, e-mail e telefone
3. ✅ **Empréstimos e Devoluções**: Controle automático do status (disponível/emprestado) e registro de todas as operações
4. ✅ **Relatórios**: Geração automática de relatórios sobre livros mais emprestados, usuários com devoluções pendentes, estatísticas, etc.

---

## ✨ Funcionalidades

### 📖 Gerenciamento de Livros
- Cadastro, edição e exclusão de livros
- Controle de quantidade de exemplares
- Busca por título, autor ou editora
- Consulta de disponibilidade em tempo real
- Validação automática de exemplares disponíveis

### 👥 Gerenciamento de Usuários
- Cadastro completo de alunos e funcionários
- Atualização de dados
- Consulta por nome ou matrícula
- Validação de dados únicos (matrícula)

### 🔄 Sistema de Empréstimos
- Realização de empréstimos com validação automática
- Controle de data de empréstimo e devolução prevista
- Registro automático de devoluções
- Alertas de empréstimos vencidos
- Controle de status (ativo, devolvido, vencido)

### 📊 Relatórios e Estatísticas
- Relatório de livros mais emprestados
- Relatório de usuários com devoluções pendentes
- Estatísticas gerais do acervo
- Relatório de empréstimos ativos
- Relatório de empréstimos vencidos
- Exportação de relatórios em PDF e Excel

### 🔍 Consulta e Busca
- Busca avançada de livros
- Filtros por título, autor, editora
- Visualização de disponibilidade instantânea
- Histórico de empréstimos por livro

---

## 🔧 Requisitos do Sistema

### Requisitos Funcionais

| RF | Descrição | Status |
|----|-----------|--------|
| RF-01 | Cadastro, consulta, atualização e exclusão de livros | ✅ Implementado |
| RF-02 | Cadastro, consulta, atualização e exclusão de usuários | ✅ Implementado |
| RF-03 | Registro automático de empréstimos | ✅ Implementado |
| RF-04 | Registro automático de devoluções | ✅ Implementado |
| RF-05 | Consulta de disponibilidade por título ou autor | ✅ Implementado |
| RF-06 | Notificações de empréstimos vencidos | 🚧 Pendente |
| RF-07 | Geração automática de relatórios | ✅ Implementado |

### Requisitos Não Funcionais

- ✅ Sistema acessível via navegador web
- ✅ Desenvolvido em **PHP** com arquitetura em camadas
- ✅ Interface amigável e responsiva com **Bootstrap 5**
- ✅ Suporta até **1.000 usuários** e **5.000 livros**
- ✅ Banco de dados relacional **MySQL** implementado

### Pré-requisitos Técnicos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior / MariaDB 10.3+
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, MySQLi, GD (para exportação)
- XAMPP, WAMP, LAMP ou similar (para desenvolvimento local)

---

## 📁 Estrutura do Projeto

```
sistema-biblioteca/
├── config/                      # Configurações do sistema
│   ├── db.php                  # Configuração da conexão com banco de dados
│   └── config.php              # Configurações gerais (constant, timezone, etc.)
│
├── includes/                    # Arquivos reutilizáveis
│   ├── nav.php                 # Barra de navegação
│   ├── nav-pages.php           # Navegação para páginas internas
│   ├── footer.php              # Rodapé principal
│   └── footer-pages.php        # Rodapé para páginas internas
│
├── pages/                       # Páginas principais do sistema
│   ├── books.php               # Gerenciamento de livros
│   ├── users.php               # Gerenciamento de usuários
│   ├── loans.php               # Sistema de empréstimos/devoluções
│   ├── reports.php             # Relatórios e estatísticas
│   └── search.php              # Consulta de livros
│
├── assets/                      # Recursos estáticos
│   ├── css/
│   │   └── style.css          # Estilos personalizados
│   └── js/
│       └── main.js            # JavaScript personalizado (formulários, modais, AJAX)
│
├── sql/                         # Scripts de banco de dados
│   └── database.sql            # Script completo de criação do banco
│
├── vendor/                      # Dependências do Composer
│   └── [bibliotecas PHP]      # phpspreadsheet, mpdf
│
├── reuse/                       # Código reutilizável (referência)
│
├── index.php                    # Página inicial (Dashboard com login)
├── composer.json               # Dependências do projeto
├── composer.lock              # Lock de versões
└── README.md                   # Este arquivo
```

---

## 🚀 Instalação

### Passo 1: Baixar o Projeto

```bash
# Clone o repositório
git clone [url-do-repositorio]
cd sistema-biblioteca

# Ou baixe o arquivo ZIP e extraia no diretório do servidor web
```

### Passo 2: Instalar Dependências

```bash
# Se tiver Composer instalado, execute:
composer install

# Caso contrário, certifique-se de que a pasta vendor/ existe
```

### Passo 3: Configurar o Banco de Dados

1. Abra o phpMyAdmin ou acesse o MySQL via terminal
2. Execute o script `sql/database.sql`:

```bash
mysql -u root -p < sql/database.sql
```

Ou via phpMyAdmin:
- Importe o arquivo `sql/database.sql`

### Passo 4: Configurar a Conexão

Edite o arquivo `config/db.php` com suas credenciais:

```php
$host = 'localhost';        // Servidor do banco
$db = 'library_db';         // Nome do banco
$user = 'seu_usuario';      // Usuário do MySQL
$pass = 'sua_senha';        // Senha do MySQL
```

### Passo 5: Colocar no Servidor Web

**Para XAMPP/WAMP (Windows):**
```
C:\xampp\htdocs\sistema-biblioteca\
```

**Para Linux (LAMP):**
```bash
sudo cp -r sistema-biblioteca /var/www/html/
sudo chown -R www-data:www-data /var/www/html/sistema-biblioteca
```

### Passo 6: Verificar Permissões

```bash
# Dê permissões adequadas (Linux)
chmod -R 755 sistema-biblioteca/
chmod -R 777 sistema-biblioteca/vendor/
```

### Passo 7: Acessar o Sistema

Abra o navegador e acesse:
```
http://localhost/sistema-biblioteca
```

---

## ⚙️ Configuração

### Configurações de Sistema

Edite `config/config.php` para personalizar:

```php
define('SITE_NAME', 'Sistema de Biblioteca');
define('MAX_LOAN_DAYS', 30);      // Dias para empréstimo
define('DAILY_FINE', 2.00);       // Multa diária (R$)
date_default_timezone_set('America/Sao_Paulo');
```

### Configurações de Exportação

O sistema utiliza as bibliotecas:
- **PHPSpreadsheet**: Para exportação em Excel (.xlsx)
- **mPDF**: Para geração de PDFs

Essas já estão configuradas via Composer.

---

## 📖 Como Usar

### 1. Login no Sistema

- Usuário padrão: `admin`
- Senha padrão: `admin123`
- *(Altere após primeiro acesso)*

### 2. Cadastro de Livros

1. Acesse o menu **"Cadastrar Livros"**
2. Preencha os campos:
   - Título (obrigatório)
   - Autor (obrigatório)
   - Editora
   - Ano de publicação
   - Número de exemplares
3. Clique em **"Adicionar Livro"**

### 3. Cadastro de Usuários

1. Acesse o menu **"Cadastrar Usuários"**
2. Preencha:
   - Nome completo
   - Matrícula (única)
   - E-mail (opcional)
   - Telefone (opcional)
3. Clique em **"Adicionar Usuário"**

### 4. Realizar Empréstimo

1. Acesse **"Gerenciar Empréstimos"**
2. Clique em **"Novo Empréstimo"**
3. Selecione o livro (mostra disponibilidade)
4. Selecione o usuário
5. Defina a data de devolução
6. Clique em **"Realizar Empréstimo"**

### 5. Registrar Devolução

1. Em **"Empréstimos Ativos"**
2. Clique em **"Devolver"** no empréstimo desejado
3. Confirme a ação

### 6. Consultar Livros

1. Acesse **"Consulta de Livros"**
2. Digite título, autor ou editora
3. Visualize disponibilidade e histórico

### 7. Visualizar Relatórios

1. Acesse **"Visualizar Relatórios"**
2. Escolha o tipo de relatório:
   - Livros mais emprestados
   - Empréstimos ativos
   - Empréstimos vencidos
   - Estatísticas gerais
3. Exporte em PDF ou Excel

---

## 🏗️ Arquitetura e Design

### Arquitetura do Sistema

O sistema utiliza **Arquitetura em Camadas (Layered Architecture)**:

```
┌─────────────────────────────────┐
│    Camada de Apresentação        │
│  (HTML + CSS + JavaScript)      │
├─────────────────────────────────┤
│    Camada de Lógica/Business    │
│  (Regras de Negócio - PHP)      │
├─────────────────────────────────┤
│    Camada de Dados              │
│  (MySQL via PDO)                │
└─────────────────────────────────┘
```

### Modelo de Dados

**Diagrama de Entidades:**

```
┌─────────────────┐
│     USERS       │
├─────────────────┤
│ id (PK)         │
│ nome            │
│ matricula (UK)  │
│ email           │
│ telefone        │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
         │
         ▼
┌─────────────────┐
│     LOANS       │
├─────────────────┤
│ id (PK)         │
│ book_id (FK)    │──┐
│ user_id (FK)    │  │
│ data_emprestimo │  │
│ data_devolucao  │  │
│ status          │  │
│ created_at      │  │
│ updated_at      │  │
└─────────────────┘  │
                     │
                     │ N:1
                     │
                     ▼
         ┌─────────────────┐
         │      BOOKS       │
         ├─────────────────┤
         │ id (PK)         │
         │ titulo          │
         │ autor           │
         │ editora         │
         │ ano_publicacao  │
         │ num_exemplares  │
         │ isbn            │
         │ created_at      │
         │ updated_at      │
         └─────────────────┘
```

### Casos de Uso Principais

1. **Usuário (Bibliotecário)**: 
   - Cadastrar livro
   - Cadastrar usuário
   - Realizar empréstimo
   - Registrar devolução
   - Gerar relatórios

2. **Sistema**:
   - Validar disponibilidade
   - Controlar status de empréstimos
   - Gerar notificações (futuro)
   - Calcular multas (futuro)

---

## 💻 Tecnologias Utilizadas

### Backend
- **PHP 7.4+** - Linguagem principal
- **PDO** - Extensão para acesso ao banco de dados
- **MySQL 5.7+** - Banco de dados relacional

### Frontend
- **Bootstrap 5** - Framework CSS
- **JavaScript (ES6+)** - Interatividade e AJAX
- **Font Awesome / Bootstrap Icons** - Ícones

### Bibliotecas PHP (via Composer)
- **PHPSpreadsheet** - Exportação para Excel
- **mPDF** - Geração de PDFs

### Servidor
- **Apache/Nginx** - Servidor web
- **XAMPP/WAMP/LAMP** - Ambiente de desenvolvimento

---

## 📐 Diagrama de Entidades

### Tabela: users
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT AUTO_INCREMENT | Chave primária |
| nome | VARCHAR(100) | Nome completo |
| matricula | VARCHAR(20) UNIQUE | Matrícula (única) |
| email | VARCHAR(100) | E-mail |
| telefone | VARCHAR(20) | Telefone |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Última atualização |

### Tabela: books
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT AUTO_INCREMENT | Chave primária |
| titulo | VARCHAR(200) | Título do livro |
| autor | VARCHAR(100) | Autor |
| editora | VARCHAR(100) | Editora |
| ano_publicacao | YEAR | Ano de publicação |
| num_exemplares | INT | Quantidade de exemplares |
| isbn | VARCHAR(20) | ISBN |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Última atualização |

### Tabela: loans
| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT AUTO_INCREMENT | Chave primária |
| book_id | INT (FK) | Referência a books |
| user_id | INT (FK) | Referência a users |
| data_emprestimo | DATE | Data do empréstimo |
| data_devolucao | DATE | Data prevista de devolução |
| data_devolucao_real | DATE | Data real de devolução |
| status | ENUM | ativo, devolvido, vencido |
| observacoes | TEXT | Observações |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Última atualização |

**Relacionamentos:**
- `loans.book_id` → `books.id` (CASCADE)
- `loans.user_id` → `users.id` (CASCADE)

---

## 🛠️ Desenvolvimento

### Etapas do Desenvolvimento

#### 1. Levantamento de Requisitos
- ✅ Análise das operações atuais da biblioteca
- ✅ Identificação de problemas e necessidades
- ✅ Definição de requisitos funcionais e não funcionais

#### 2. Análise e Design
- ✅ Modelagem do banco de dados (Diagrama ER)
- ✅ Design da interface (wireframes)
- ✅ Definição da arquitetura (camadas)

#### 3. Implementação
- ✅ Configuração do ambiente (PHP + MySQL)
- ✅ Desenvolvimento das funcionalidades principais:
  - Sistema de autenticação
  - CRUD de livros
  - CRUD de usuários
  - Sistema de empréstimos/devoluções
  - Geração de relatórios
  - Consulta e busca
- ✅ Desenvolvimento da interface com Bootstrap 5
- ✅ Integração com bibliotecas de exportação

#### 4. Testes
- 🧪 Testes unitários (pendente)
- 🧪 Testes de integração (pendente)
- ✅ Testes manuais de usabilidade
- 🧪 Testes de carga (pendente)

#### 5. Implantação
- ✅ Documentação do sistema
- 🚧 Treinamento de usuários (pendente)
- 🚧 Migração de dados (pendente)

---

## 🧪 Testes

### Testes Realizados

- ✅ Teste de cadastro de livros
- ✅ Teste de cadastro de usuários
- ✅ Teste de empréstimo (validação de disponibilidade)
- ✅ Teste de devolução
- ✅ Teste de busca e consulta
- ✅ Teste de relatórios
- ✅ Teste de interface responsiva

### Próximos Testes Recomendados

- [ ] Testes automatizados com PHPUnit
- [ ] Testes de integração
- [ ] Testes de desempenho (carga)
- [ ] Testes de segurança (SQL Injection, XSS)
- [ ] Testes de acessibilidade

---

## 🚀 Implantação

### Pré-requisitos para Produção

1. **Servidor Web**: Apache 2.4+ ou Nginx
2. **PHP**: 7.4+ com extensões PDO, MySQLi, GD
3. **Banco de Dados**: MySQL 5.7+ ou MariaDB 10.3+
4. **SSL/HTTPS**: Certificado SSL (recomendado)
5. **Backup**: Sistema de backup automático

### Checklist de Implantação

- [ ] Configurar servidor de produção
- [ ] Instalar dependências via Composer
- [ ] Criar banco de dados e executar scripts
- [ ] Configurar conexão com banco de dados
- [ ] Configurar HTTPS e certificado SSL
- [ ] Configurar permissões de arquivos
- [ ] Implementar sistema de backup
- [ ] Configurar logs de erro
- [ ] Desabilitar exibição de erros em produção
- [ ] Implementar autenticação de usuários
- [ ] Treinar equipe de bibliotecários
- [ ] Migrar dados históricos (se houver)

### Backup e Segurança

```bash
# Backup do banco de dados
mysqldump -u root -p library_db > backup_$(date +%Y%m%d).sql

# Backup dos arquivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz sistema-biblioteca/
```

---

## 📚 Documentação Adicional

### Arquivos de Referência

- `sql/database.sql` - Estrutura completa do banco
- `config/config.php` - Configurações do sistema
- `config/db.php` - Configuração do banco de dados
- `assets/js/main.js` - Lógica JavaScript principal

### Funcionalidades Futuras (Roadmap)

- 🔔 Sistema de notificações por e-mail
- 💰 Cálculo automático de multas
- 🔐 Sistema de autenticação completo
- 📱 Versão mobile (PWA)
- 🔍 Busca avançada com filtros múltiplos
- 📊 Dashboards interativos com gráficos
- 🌐 Integração com APIs externas
- 👥 Gestão de permissões e papéis

---

## 🐛 Solução de Problemas

### Erros Comuns

#### Erro de Conexão com Banco
```php
Error: Erro de conexão com o banco de dados
```

**Solução:**
- Verifique se o MySQL está rodando
- Confirme credenciais em `config/db.php`
- Certifique-se de que o banco `library_db` existe
- Verifique permissões do usuário MySQL

#### Páginas Não Carregam
```
404 Not Found ou página em branco
```

**Solução:**
- Verifique se o PHP está funcionando: `php -v`
- Confirme permissões dos arquivos
- Verifique os logs de erro do servidor
- Certifique-se de que os caminhos estão corretos

#### Problemas de CSS/JS
```
Estilos ou scripts não carregam
```

**Solução:**
- Verifique se os arquivos estão em `assets/`
- Confirme se os caminhos estão corretos nos HTML
- Limpe o cache do navegador (Ctrl+F5)
- Verifique permissões da pasta `assets/`

#### Erro: "Composer não encontrado"
```bash
composer: command not found
```

**Solução:**
- Instale o Composer: https://getcomposer.org/download/
- Ou baixe a pasta `vendor/` completa do projeto

---

## 📞 Suporte

### Contribuindo

Contribuições são bem-vindas! Para contribuir:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/NovaFuncionalidade`)
3. Commit suas mudanças (`git commit -m 'Adiciona NovaFuncionalidade'`)
4. Push para a branch (`git push origin feature/NovaFuncionalidade`)
5. Abra um Pull Request

### Reportar Bugs

Para reportar bugs:
1. Abra uma issue no repositório
2. Descreva o problema detalhadamente
3. Inclua passos para reproduzir
4. Adicione screenshots se possível

### Solicitar Features

Para solicitar novas funcionalidades:
1. Abra uma issue com a tag `feature-request`
2. Descreva a funcionalidade desejada
3. Explique o caso de uso

---

## 📝 Licença

Este projeto é de código aberto e está disponível sob a licença **MIT**.

Você é livre para:
- ✅ Usar comercialmente
- ✅ Modificar
- ✅ Distribuir
- ✅ Usar privadamente

Sob as condições:
- ⚠️ Incluir a licença MIT
- ⚠️ Incluir copyright original

---

## 👨‍💻 Autor e Contato

**Sistema de Gerenciamento de Biblioteca Escolar**

Desenvolvido com ❤️ para facilitar o gerenciamento de bibliotecas

Para dúvidas, sugestões ou suporte:
- 📧 E-mail: [seu-email@exemplo.com]
- 🐛 Issues: [GitHub Issues]
- 📚 Documentação: Ver este arquivo README.md

---

## 📊 Estatísticas do Projeto

- **Linguagem Principal**: PHP
- **Banco de Dados**: MySQL
- **Interface**: Bootstrap 5 + JavaScript
- **Linhas de Código**: ~3.000+
- **Funcionalidades**: 7 principais
- **Tempo de Desenvolvimento**: Conforme cronograma

---

## 🎓 Aprendizados

Este projeto demonstra:
- Desenvolvimento web com PHP e MySQL
- Arquitetura em camadas
- CRUD completo (Create, Read, Update, Delete)
- Integração com bibliotecas externas (Composer)
- Interface responsiva com Bootstrap
- Geração de relatórios em PDF e Excel
- Gestão de estado e validações
- Boas práticas de programação

---

**Última atualização:** 10/2025  
**Versão:** 1.0.1  
**Status do Projeto:** ✅ Em Produção
