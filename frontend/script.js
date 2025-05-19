// script.js - Lógica de exibição e AJAX para login/cadastro

document.getElementById('btnEntrar').onclick = function() {
    document.querySelector('.welcome').classList.add('hidden');
    document.getElementById('formLogin').classList.remove('hidden');
};
document.getElementById('btnCadastrar').onclick = function() {
    document.querySelector('.welcome').classList.add('hidden');
    document.getElementById('formCadastro').classList.remove('hidden');
};
document.getElementById('voltarLogin').onclick = function() {
    document.getElementById('formLogin').classList.add('hidden');
    document.querySelector('.welcome').classList.remove('hidden');
};
document.getElementById('voltarCadastro').onclick = function() {
    document.getElementById('formCadastro').classList.add('hidden');
    document.querySelector('.welcome').classList.remove('hidden');
};

// Cadastro AJAX
const formCadastro = document.getElementById('formCadastro');
formCadastro.onsubmit = function(e) {
    e.preventDefault();
    const nome = document.getElementById('cadNome').value;
    const email = document.getElementById('cadEmail').value;
    const senha = document.getElementById('cadSenha').value;
    const tipo = document.getElementById('cadTipo').value;
    fetch('../backend/cadastro_usuario.php', {
        method: 'POST',
        body: new URLSearchParams({ nome, email, senha, tipo })
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('cadMsg').textContent = d.mensagem;
        if (d.sucesso) {
            setTimeout(() => {
                formCadastro.classList.add('hidden');
                document.querySelector('.welcome').classList.remove('hidden');
            }, 1200);
        }
    });
};

// Login AJAX
const formLogin = document.getElementById('formLogin');
formLogin.onsubmit = function(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const senha = document.getElementById('loginSenha').value;
    fetch('../backend/login.php', {
        method: 'POST',
        body: new URLSearchParams({ email, senha })
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('loginMsg').textContent = d.mensagem || '';
        if (d.sucesso) {
            // Redirecionar para dashboard conforme tipo de usuário
            if (d.tipo === 'barbeiro') {
                window.location.href = 'dashboard_barbeiro.html';
            } else if (d.tipo === 'admin') {
                window.location.href = 'admin.html';
            } else {
                window.location.href = 'agendamento.html';
            }
        }
    });
};
