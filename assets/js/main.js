// // JavaScript para o Sistema de Biblioteca

// // State
// let currentUser = '';
// let selectedBook = '';
// let selectedRental = null;

// document.addEventListener('DOMContentLoaded', function() {
//     // Elements
//     const loginPage = document.getElementById('loginPage');
//     const dashboardPage = document.getElementById('dashboardPage');
//     const loginForm = document.getElementById('loginForm');
//     const logoutBtn = document.getElementById('logoutBtn');
//     const currentUserName = document.getElementById('currentUserName');

//     // Login functionality
//     if (loginForm) {
//         loginForm.addEventListener('submit', (e) => {
//             e.preventDefault();
//             const username = document.getElementById('username').value;
//             const password = document.getElementById('password').value;
            
//             if (username && password) {
//                 currentUser = username;
//                 if (currentUserName) {
//                     currentUserName.textContent = username;
//                 }
//                 if (loginPage) loginPage.classList.add('hidden');
//                 if (dashboardPage) dashboardPage.classList.remove('hidden');
//             }
//         });
//     }

//     // Logout functionality
//     if (logoutBtn) {
//         logoutBtn.addEventListener('click', () => {
//             currentUser = '';
//             const usernameInput = document.getElementById('username');
//             const passwordInput = document.getElementById('password');
//             if (usernameInput) usernameInput.value = '';
//             if (passwordInput) passwordInput.value = '';
//             if (loginPage) loginPage.classList.remove('hidden');
//             if (dashboardPage) dashboardPage.classList.add('hidden');
//         });
//     }

//     // Book Search functionality
//     const bookSearch = document.getElementById('bookSearch');
//     const booksTableBody = document.getElementById('booksTableBody');
    
//     if (bookSearch && booksTableBody) {
//         bookSearch.addEventListener('input', (e) => {
//             const searchTerm = e.target.value.toLowerCase();
//             const rows = booksTableBody.querySelectorAll('tr');
            
//             rows.forEach(row => {
//                 const title = row.cells[0].textContent.toLowerCase();
//                 const author = row.cells[1].textContent.toLowerCase();
                
//                 if (title.includes(searchTerm) || author.includes(searchTerm)) {
//                     row.style.display = '';
//                 } else {
//                     row.style.display = 'none';
//                 }
//             });
//         });
//     }

//     // Select Book functionality
//     document.querySelectorAll('.select-book-btn').forEach(btn => {
//         btn.addEventListener('click', (e) => {
//             selectedBook = e.target.dataset.book;
//             const bookId = e.target.dataset.bookId;
//             const rentalBookInput = document.getElementById('rentalBook');
//             const rentalBookIdInput = document.getElementById('rentalBookId');
            
//             if (rentalBookInput) {
//                 rentalBookInput.value = selectedBook;
//             }
//             if (rentalBookIdInput) {
//                 rentalBookIdInput.value = bookId;
//             }
            
//             // Switch to rental tab
//             const rentalTab = document.getElementById('rental-tab');
//             if (rentalTab) {
//                 const tab = new bootstrap.Tab(rentalTab);
//                 tab.show();
//             }
//         });
//     });

//     // Update Return Date functionality
//     const rentalDays = document.getElementById('rentalDays');
//     const returnDate = document.getElementById('returnDate');
    
//     function updateReturnDate() {
//         if (rentalDays && returnDate) {
//             const days = parseInt(rentalDays.value);
//             const date = new Date();
//             date.setDate(date.getDate() + days);
//             returnDate.textContent = date.toLocaleDateString('pt-BR');
//         }
//     }
    
//     if (rentalDays) {
//         rentalDays.addEventListener('change', updateReturnDate);
//         updateReturnDate();
//     }

//     // Rental Form Submit
//     const rentalForm = document.getElementById('rentalForm');
//     if (rentalForm) {
//         rentalForm.addEventListener('submit', (e) => {
//             e.preventDefault();
            
//             const user = document.getElementById('rentalUser');
//             const book = document.getElementById('rentalBook');
            
//             if (user && book && user.value && book.value) {
//                 showToast('Aluguel registrado com sucesso!');
                
//                 // Reset form
//                 rentalForm.reset();
//                 if (book) book.value = '';
//                 selectedBook = '';
//                 updateReturnDate();
                
//                 // Switch back to search tab
//                 const searchTab = document.getElementById('search-tab');
//                 if (searchTab) {
//                     const tab = new bootstrap.Tab(searchTab);
//                     tab.show();
//                 }
                
//                 // Close modal
//                 const modal = bootstrap.Modal.getInstance(document.getElementById('rentalModal'));
//                 if (modal) modal.hide();
//             }
//         });
//     }

//     // Rental Search functionality
//     const rentalSearch = document.getElementById('rentalSearch');
//     const rentalsTableBody = document.getElementById('rentalsTableBody');
    
//     if (rentalSearch && rentalsTableBody) {
//         rentalSearch.addEventListener('input', (e) => {
//             const searchTerm = e.target.value.toLowerCase();
//             const rows = rentalsTableBody.querySelectorAll('tr');
            
//             rows.forEach(row => {
//                 const book = row.cells[0].textContent.toLowerCase();
//                 const user = row.cells[1].textContent.toLowerCase();
                
//                 if (book.includes(searchTerm) || user.includes(searchTerm)) {
//                     row.style.display = '';
//                 } else {
//                     row.style.display = 'none';
//                 }
//             });
//         });
//     }

//     // Select Rental for Return functionality
//     document.querySelectorAll('.select-rental-btn').forEach(btn => {
//         btn.addEventListener('click', (e) => {
//             selectedRental = JSON.parse(e.target.dataset.rental);
            
//             // Fill return form
//             const returnBookName = document.getElementById('returnBookName');
//             const returnUserName = document.getElementById('returnUserName');
//             const returnRentalDate = document.getElementById('returnRentalDate');
//             const returnExpectedDate = document.getElementById('returnExpectedDate');
//             const returnLoanId = document.getElementById('returnLoanId');
            
//             if (returnBookName) returnBookName.textContent = selectedRental.book;
//             if (returnUserName) returnUserName.textContent = selectedRental.user;
//             if (returnRentalDate) returnRentalDate.textContent = selectedRental.rentalDate;
//             if (returnExpectedDate) returnExpectedDate.textContent = selectedRental.returnDate;
//             if (returnLoanId) returnLoanId.value = selectedRental.id;
            
//             // Show/hide late alert
//             const lateAlert = document.getElementById('lateAlert');
//             if (lateAlert) {
//                 if (selectedRental.isLate) {
//                     const lateFee = (Math.floor(Math.random() * 5) + 1) * 2;
//                     const lateFeeElement = document.getElementById('lateFee');
//                     if (lateFeeElement) {
//                         lateFeeElement.textContent = lateFee.toFixed(2);
//                     }
//                     lateAlert.classList.remove('hidden');
//                 } else {
//                     lateAlert.classList.add('hidden');
//                 }
//             }
            
//             // Update current return date
//             const currentReturnDate = document.getElementById('currentReturnDate');
//             if (currentReturnDate) {
//                 currentReturnDate.textContent = new Date().toLocaleDateString('pt-BR');
//             }
            
//             // Show form
//             const noSelectionMessage = document.getElementById('noSelectionMessage');
//             const returnForm = document.getElementById('returnForm');
//             if (noSelectionMessage) noSelectionMessage.classList.add('hidden');
//             if (returnForm) returnForm.classList.remove('hidden');
            
//             // Switch to return tab
//             const returnTab = document.getElementById('return-tab');
//             if (returnTab) {
//                 const tab = new bootstrap.Tab(returnTab);
//                 tab.show();
//             }
//         });
//     });

//     // Back to Active Rentals functionality
//     const backToActiveBtn = document.getElementById('backToActiveBtn');
//     if (backToActiveBtn) {
//         backToActiveBtn.addEventListener('click', () => {
//             selectedRental = null;
//             const returnForm = document.getElementById('returnForm');
//             const noSelectionMessage = document.getElementById('noSelectionMessage');
//             if (returnForm) returnForm.classList.add('hidden');
//             if (noSelectionMessage) noSelectionMessage.classList.remove('hidden');
            
//             // Switch to active tab
//             const activeTab = document.getElementById('active-tab');
//             if (activeTab) {
//                 const tab = new bootstrap.Tab(activeTab);
//                 tab.show();
//             }
//         });
//     }

//     // Return Form Submit functionality
//     const returnForm = document.getElementById('returnForm');
//     if (returnForm) {
//         returnForm.addEventListener('submit', (e) => {
//             e.preventDefault();
            
//             if (selectedRental) {
//                 showToast('Devolução registrada com sucesso!');
                
//                 // Reset
//                 selectedRental = null;
//                 returnForm.reset();
//                 const returnFormElement = document.getElementById('returnForm');
//                 const noSelectionMessage = document.getElementById('noSelectionMessage');
//                 if (returnFormElement) returnFormElement.classList.add('hidden');
//                 if (noSelectionMessage) noSelectionMessage.classList.remove('hidden');
                
//                 // Switch back to active tab
//                 const activeTab = document.getElementById('active-tab');
//                 if (activeTab) {
//                     const tab = new bootstrap.Tab(activeTab);
//                     tab.show();
//                 }
                
//                 // Close modal
//                 const modal = bootstrap.Modal.getInstance(document.getElementById('returnModal'));
//                 if (modal) modal.hide();
//             }
//         });
//     }

//     // Confirmação para exclusão
//     const deleteButtons = document.querySelectorAll('button[name="delete"]');
//     deleteButtons.forEach(button => {
//         button.addEventListener('click', function(e) {
//             if (!confirm('Tem certeza que deseja excluir este item?')) {
//                 e.preventDefault();
//             }
//         });
//     });

//     // Auto-hide alerts
//     const alerts = document.querySelectorAll('.alert');
//     alerts.forEach(alert => {
//         setTimeout(() => {
//             alert.style.opacity = '0';
//             setTimeout(() => {
//                 alert.remove();
//             }, 300);
//         }, 5000);
//     });

//     // Form validation
//     const forms = document.querySelectorAll('form');
//     forms.forEach(form => {
//         form.addEventListener('submit', function(e) {
//             const requiredFields = form.querySelectorAll('[required]');
//             let isValid = true;
            
//             requiredFields.forEach(field => {
//                 if (!field.value.trim()) {
//                     field.classList.add('is-invalid');
//                     isValid = false;
//                 } else {
//                     field.classList.remove('is-invalid');
//                 }
//             });
            
//             if (!isValid) {
//                 e.preventDefault();
//                 alert('Por favor, preencha todos os campos obrigatórios.');
//             }
//         });
//     });

//     // Search functionality enhancement
//     const searchInput = document.querySelector('input[name="query"]');
//     if (searchInput) {
//         searchInput.addEventListener('input', function() {
//             const query = this.value.toLowerCase();
//             const tableRows = document.querySelectorAll('tbody tr');
            
//             tableRows.forEach(row => {
//                 const text = row.textContent.toLowerCase();
//                 if (text.includes(query)) {
//                     row.style.display = '';
//                 } else {
//                     row.style.display = 'none';
//                 }
//             });
//         });
//     }

//     // Tooltip initialization
//     const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
//     tooltipTriggerList.map(function (tooltipTriggerEl) {
//         return new bootstrap.Tooltip(tooltipTriggerEl);
//     });

//     // Reset modals when closed
//     const rentalModal = document.getElementById('rentalModal');
//     if (rentalModal) {
//         rentalModal.addEventListener('hidden.bs.modal', () => {
//             selectedBook = '';
//             if (rentalForm) rentalForm.reset();
//             const rentalBookInput = document.getElementById('rentalBook');
//             if (rentalBookInput) rentalBookInput.value = '';
//             const searchTab = document.getElementById('search-tab');
//             if (searchTab) {
//                 const tab = new bootstrap.Tab(searchTab);
//                 tab.show();
//             }
//         });
//     }

//     const returnModal = document.getElementById('returnModal');
//     if (returnModal) {
//         returnModal.addEventListener('hidden.bs.modal', () => {
//             selectedRental = null;
//             if (returnForm) returnForm.reset();
//             const returnFormElement = document.getElementById('returnForm');
//             const noSelectionMessage = document.getElementById('noSelectionMessage');
//             if (returnFormElement) returnFormElement.classList.add('hidden');
//             if (noSelectionMessage) noSelectionMessage.classList.remove('hidden');
//             const activeTab = document.getElementById('active-tab');
//             if (activeTab) {
//                 const tab = new bootstrap.Tab(activeTab);
//                 tab.show();
//             }
//         });
//     }
// });

// // Toast Notification
// function showToast(message) {
//     const toastMessage = document.getElementById('toastMessage');
//     const toastEl = document.getElementById('successToast');
//     if (toastMessage) toastMessage.textContent = message;
//     if (toastEl) {
//         const toast = new bootstrap.Toast(toastEl);
//         toast.show();
//     }
// }

// JavaScript para o Sistema de Biblioteca

// State
let currentUser = '';
let selectedBook = '';
let selectedRental = null;

// Configurações
const CONFIG = {
    MAX_LOAN_DAYS: 30,
    DAILY_FINE: 2.00,
    DEFAULT_LOAN_DAYS: 7
};

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    initializeLogin();
    initializeDashboard();
    initializeModals();
    initializeEventListeners();
    checkAuthStatus();
}

function initializeLogin() {
    const loginForm = document.getElementById('loginForm');
    const loginPage = document.getElementById('loginPage');
    const dashboardPage = document.getElementById('dashboardPage');

    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    // Enter key para login
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                handleLogin(e);
            }
        });
    }
}

function initializeDashboard() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }

    // Atualizar estatísticas em tempo real
    setInterval(updateStats, 30000); // Atualiza a cada 30 segundos
}

function initializeModals() {
    initializeRentalModal();
    initializeReturnModal();
}

function initializeRentalModal() {
    // Book Search
    const bookSearch = document.getElementById('bookSearch');
    if (bookSearch) {
        bookSearch.addEventListener('input', debounce(handleBookSearch, 300));
    }

    // Select Book buttons
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('select-book-btn')) {
            handleBookSelection(e.target);
        }
    });

    // Rental Days
    const rentalDays = document.getElementById('rentalDays');
    if (rentalDays) {
        rentalDays.addEventListener('change', updateReturnDate);
        updateReturnDate();
    }

    // Rental Form
    const rentalForm = document.getElementById('rentalForm');
    if (rentalForm) {
        rentalForm.addEventListener('submit', handleRentalSubmit);
    }
}

function initializeReturnModal() {
    // Rental Search
    const rentalSearch = document.getElementById('rentalSearch');
    if (rentalSearch) {
        rentalSearch.addEventListener('input', debounce(handleRentalSearch, 300));
    }

    // Select Rental buttons
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('select-rental-btn')) {
            handleRentalSelection(e.target);
        }
    });

    // Back to Active
    const backToActiveBtn = document.getElementById('backToActiveBtn');
    if (backToActiveBtn) {
        backToActiveBtn.addEventListener('click', handleBackToActive);
    }

    // Return Form
    const returnForm = document.getElementById('returnForm');
    if (returnForm) {
        returnForm.addEventListener('submit', handleReturnSubmit);
    }
}

function initializeEventListeners() {
    // Confirmação para exclusão
    document.addEventListener('click', (e) => {
        if (e.target.name === 'delete' || e.target.closest('button[name="delete"]')) {
            if (!confirm('Tem certeza que deseja excluir este item?')) {
                e.preventDefault();
            }
        }
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showToast('Por favor, preencha todos os campos obrigatórios.', 'error');
            }
        });
    });

    // Input validation
    document.addEventListener('input', (e) => {
        if (e.target.hasAttribute('required')) {
            validateField(e.target);
        }
    });

    // Modal reset handlers
    const rentalModal = document.getElementById('rentalModal');
    if (rentalModal) {
        rentalModal.addEventListener('hidden.bs.modal', resetRentalModal);
    }

    const returnModal = document.getElementById('returnModal');
    if (returnModal) {
        returnModal.addEventListener('hidden.bs.modal', resetReturnModal);
    }
}

// Handlers
function handleLogin(e) {
    if (e) e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    if (username && password) {
        // Simulação de login - em produção, isso seria uma chamada AJAX
        if (authenticateUser(username, password)) {
            currentUser = username;
            document.getElementById('currentUserName').textContent = username;
            document.getElementById('loginPage').classList.add('hidden');
            document.getElementById('dashboardPage').classList.remove('hidden');
            
            // Salvar sessão
            sessionStorage.setItem('currentUser', username);
            sessionStorage.setItem('isAuthenticated', 'true');
            
            showToast(`Bem-vindo, ${username}!`, 'success');
        } else {
            showToast('Credenciais inválidas. Tente novamente.', 'error');
        }
    }
}

function handleLogout() {
    currentUser = '';
    sessionStorage.removeItem('currentUser');
    sessionStorage.removeItem('isAuthenticated');
    
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('loginPage').classList.remove('hidden');
    document.getElementById('dashboardPage').classList.add('hidden');
    
    showToast('Logout realizado com sucesso.', 'info');
}

function handleBookSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#booksTableBody tr');
    
    rows.forEach(row => {
        const title = row.cells[0].textContent.toLowerCase();
        const author = row.cells[1].textContent.toLowerCase();
        const isVisible = title.includes(searchTerm) || author.includes(searchTerm);
        row.style.display = isVisible ? '' : 'none';
    });
}

function handleBookSelection(button) {
    selectedBook = button.dataset.book;
    const bookId = button.dataset.bookId;
    
    document.getElementById('rentalBook').value = selectedBook;
    document.getElementById('rentalBookId').value = bookId;
    
    // Switch to rental tab
    const rentalTab = document.getElementById('rental-tab');
    if (rentalTab) {
        const tab = new bootstrap.Tab(rentalTab);
        tab.show();
    }
}

function handleRentalSubmit(e) {
    e.preventDefault();
    
    const user = document.getElementById('rentalUser');
    const book = document.getElementById('rentalBook');
    
    if (user && book && user.value && book.value) {
        // Simular envio do formulário
        simulateFormSubmission('rentalForm')
            .then(() => {
                showToast('Aluguel registrado com sucesso!', 'success');
                resetRentalModal();
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('rentalModal'));
                if (modal) modal.hide();
            })
            .catch(error => {
                showToast('Erro ao registrar aluguel. Tente novamente.', 'error');
            });
    }
}

function handleRentalSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#rentalsTableBody tr');
    
    rows.forEach(row => {
        const book = row.cells[0].textContent.toLowerCase();
        const user = row.cells[1].textContent.toLowerCase();
        const isVisible = book.includes(searchTerm) || user.includes(searchTerm);
        row.style.display = isVisible ? '' : 'none';
    });
}

function handleRentalSelection(button) {
    selectedRental = JSON.parse(button.dataset.rental);
    
    // Preencher formulário de devolução
    document.getElementById('returnBookName').textContent = selectedRental.book;
    document.getElementById('returnUserName').textContent = selectedRental.user;
    document.getElementById('returnRentalDate').textContent = selectedRental.rentalDate;
    document.getElementById('returnExpectedDate').textContent = selectedRental.returnDate;
    document.getElementById('returnLoanId').value = selectedRental.id;
    
    // Mostrar/ocultar alerta de atraso
    const lateAlert = document.getElementById('lateAlert');
    if (lateAlert) {
        if (selectedRental.isLate) {
            const lateFee = calculateLateFee(selectedRental);
            document.getElementById('lateFee').textContent = lateFee.toFixed(2);
            lateAlert.classList.remove('hidden');
        } else {
            lateAlert.classList.add('hidden');
        }
    }
    
    // Atualizar data atual
    document.getElementById('currentReturnDate').textContent = new Date().toLocaleDateString('pt-BR');
    
    // Mostrar formulário
    document.getElementById('noSelectionMessage').classList.add('hidden');
    document.getElementById('returnForm').classList.remove('hidden');
    
    // Mudar para aba de devolução
    const returnTab = document.getElementById('return-tab');
    if (returnTab) {
        const tab = new bootstrap.Tab(returnTab);
        tab.show();
    }
}

function handleBackToActive() {
    selectedRental = null;
    document.getElementById('returnForm').classList.add('hidden');
    document.getElementById('noSelectionMessage').classList.remove('hidden');
    
    const activeTab = document.getElementById('active-tab');
    if (activeTab) {
        const tab = new bootstrap.Tab(activeTab);
        tab.show();
    }
}

function handleReturnSubmit(e) {
    e.preventDefault();
    
    if (selectedRental) {
        simulateFormSubmission('returnForm')
            .then(() => {
                showToast('Devolução registrada com sucesso!', 'success');
                resetReturnModal();
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('returnModal'));
                if (modal) modal.hide();
            })
            .catch(error => {
                showToast('Erro ao registrar devolução. Tente novamente.', 'error');
            });
    }
}

// Utility Functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    const isValid = field.value.trim() !== '';
    field.classList.toggle('is-invalid', !isValid);
    field.classList.toggle('is-valid', isValid && field.value.trim() !== '');
    return isValid;
}

function updateReturnDate() {
    const rentalDays = document.getElementById('rentalDays');
    const returnDate = document.getElementById('returnDate');
    
    if (rentalDays && returnDate) {
        const days = parseInt(rentalDays.value);
        const date = new Date();
        date.setDate(date.getDate() + days);
        returnDate.textContent = date.toLocaleDateString('pt-BR');
    }
}

function calculateLateFee(rental) {
    // Calcular dias de atraso (simulação)
    const daysLate = Math.floor(Math.random() * 10) + 1;
    return daysLate * CONFIG.DAILY_FINE;
}

function simulateFormSubmission(formId) {
    return new Promise((resolve, reject) => {
        // Simular delay de rede
        setTimeout(() => {
            // Em produção, isso seria uma chamada AJAX real
            const success = Math.random() > 0.1; // 90% de chance de sucesso
            if (success) {
                resolve();
            } else {
                reject(new Error('Simulated network error'));
            }
        }, 1000);
    });
}

function resetRentalModal() {
    selectedBook = '';
    const rentalForm = document.getElementById('rentalForm');
    if (rentalForm) rentalForm.reset();
    document.getElementById('rentalBook').value = '';
    
    const searchTab = document.getElementById('search-tab');
    if (searchTab) {
        const tab = new bootstrap.Tab(searchTab);
        tab.show();
    }
}

function resetReturnModal() {
    selectedRental = null;
    const returnForm = document.getElementById('returnForm');
    if (returnForm) returnForm.reset();
    document.getElementById('returnForm').classList.add('hidden');
    document.getElementById('noSelectionMessage').classList.remove('hidden');
    
    const activeTab = document.getElementById('active-tab');
    if (activeTab) {
        const tab = new bootstrap.Tab(activeTab);
        tab.show();
    }
}

function checkAuthStatus() {
    const isAuthenticated = sessionStorage.getItem('isAuthenticated');
    const storedUser = sessionStorage.getItem('currentUser');
    
    if (isAuthenticated === 'true' && storedUser) {
        currentUser = storedUser;
        document.getElementById('currentUserName').textContent = storedUser;
        document.getElementById('loginPage').classList.add('hidden');
        document.getElementById('dashboardPage').classList.remove('hidden');
    }
}

function authenticateUser(username, password) {
    // Em produção, isso seria uma verificação no servidor
    // Por enquanto, aceita qualquer credencial não vazia
    return username.trim() !== '' && password.trim() !== '';
}

function updateStats() {
    // Em produção, isso faria uma chamada AJAX para atualizar as estatísticas
    console.log('Atualizando estatísticas...');
}

// Toast Notification
function showToast(message, type = 'success') {
    const toastEl = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');
    
    if (!toastEl || !toastMessage) return;
    
    // Configurar cor baseada no tipo
    const typeConfig = {
        success: 'bg-success',
        error: 'bg-danger',
        warning: 'bg-warning',
        info: 'bg-info'
    };
    
    toastEl.className = `toast align-items-center text-white border-0 ${typeConfig[type] || 'bg-success'}`;
    toastMessage.textContent = message;
    
    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

// Exportar para uso global (se necessário)
window.BibliotecaApp = {
    showToast,
    updateStats,
    CONFIG
};