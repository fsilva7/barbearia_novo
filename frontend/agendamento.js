// agendamento.js - Lógica de navegação e placeholders para AJAX

// Elementos dos passos
const steps = [
    'stepBoasVindas',
    'stepData',
    'stepServico',
    'stepProfissional',
    'stepHorario',
    'stepConfirmacao'
];
let agendamento = {
    data: '',
    servico: '',
    profissional: '',
    horario: ''
};

// Checa se usuário está logado e é cliente
const urlParams = new URLSearchParams(window.location.search);
const barbeiroIdUrl = urlParams.get('barbeiro_id');
let barbeiroId = barbeiroIdUrl;

// Verifica se temos um barbeiro_id válido
document.addEventListener('DOMContentLoaded', function() {
    // Configura a data mínima no input de data (hoje)
    const hoje = new Date();
    const ano = hoje.getFullYear();
    const mes = String(hoje.getMonth() + 1).padStart(2, '0');
    const dia = String(hoje.getDate()).padStart(2, '0');
    const dataMinima = `${ano}-${mes}-${dia}`;
    
    document.getElementById('dataAgendamento').min = dataMinima;
    document.getElementById('dataAgendamento').value = dataMinima;
    
    if (!barbeiroId) {
        alert('Link de agendamento inválido. Escolha um estabelecimento.');
        window.location.href = 'index.html';
    }
    
    // Adiciona o nome do estabelecimento na tela de boas-vindas
    if (barbeiroId) {
        fetch(`../backend/get_usuario_logado.php?id=${barbeiroId}`)
            .then(r => r.json())
            .then(barbeiro => {
                if (barbeiro && barbeiro.nome) {
                    document.querySelector('#stepBoasVindas h2').textContent = 
                        `Olá! Vamos agendar seu horário na ${barbeiro.nome}?`;
                }
            })
            .catch(err => console.error('Erro ao buscar dados do barbeiro:', err));
    }
});

function showStep(stepId) {
    steps.forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
    document.getElementById(stepId).classList.remove('hidden');
}

document.getElementById('btnComecar').onclick = () => showStep('stepData');
document.getElementById('voltarData').onclick = () => showStep('stepBoasVindas');
document.getElementById('voltarServico').onclick = () => showStep('stepData');
document.getElementById('voltarProfissional').onclick = () => showStep('stepServico');
document.getElementById('voltarHorario').onclick = () => showStep('stepProfissional');
document.getElementById('voltarConfirmacao').onclick = () => showStep('stepHorario');
document.getElementById('voltarParaHome').onclick = () => window.location.href = 'index.html';

document.getElementById('btnDataProximo').onclick = () => {
    agendamento.data = document.getElementById('dataAgendamento').value;
    if (!agendamento.data) return alert('Escolha uma data!');
    
    // Verifica se o estabelecimento está aberto nesta data
    fetch(`../backend/verificar_horario_funcionamento.php?barbeiro_id=${barbeiroId}&data=${agendamento.data}`)
        .then(r => r.json())
        .then(resposta => {
            if (!resposta.aberto) {
                alert('O estabelecimento está fechado nesta data. Por favor, escolha outra data.');
                return;
            }
            
            // Se estiver aberto, carrega os serviços e prossegue
            carregarServicos();
            showStep('stepServico');
        })
        .catch(error => {
            console.error('Erro ao verificar horário de funcionamento:', error);
            alert('Erro ao verificar disponibilidade. Por favor, tente novamente.');
        });
};

document.getElementById('btnServicoProximo').onclick = () => {
    agendamento.servico = document.getElementById('selectServico').value;
    if (!agendamento.servico) return alert('Escolha um serviço!');
    carregarProfissionais();
    showStep('stepProfissional');
};

document.getElementById('btnProfissionalProximo').onclick = () => {
    agendamento.profissional = document.getElementById('selectProfissional').value;
    if (!agendamento.profissional) return alert('Escolha um profissional!');
    carregarHorarios();
    showStep('stepHorario');
};

function carregarServicos() {
    const select = document.getElementById('selectServico');
    select.innerHTML = '<option value="">Carregando...</option>';
    select.disabled = true;
    
    fetch(`../backend/listar_servicos.php?barbeiro_id=${barbeiroId}`)
        .then(r => r.json())
        .then(servicos => {
            select.innerHTML = '<option value="">Selecione</option>';
            select.disabled = false;
            
            if (servicos.length === 0) {
                select.innerHTML = '<option value="">Nenhum serviço disponível</option>';
                document.getElementById('btnServicoProximo').disabled = true;
                return;
            }
            
            servicos.forEach(s => {
                select.innerHTML += `<option value="${s.id}" data-duracao="${s.duracao_minutos}">${s.nome} (${s.duracao_minutos}min) - R$ ${s.preco}</option>`;
            });
        })
        .catch(error => {
            console.error('Erro ao carregar serviços:', error);
            select.innerHTML = '<option value="">Erro ao carregar serviços</option>';
            select.disabled = false;
        });
}

function carregarProfissionais() {
    const servicoId = document.getElementById('selectServico').value;
    const select = document.getElementById('selectProfissional');
    
    select.innerHTML = '<option value="">Carregando...</option>';
    select.disabled = true;
    
    fetch(`../backend/listar_profissionais.php?servico_id=${servicoId}&barbeiro_id=${barbeiroId}`)
        .then(r => r.json())
        .then(profissionais => {
            select.innerHTML = '<option value="">Selecione</option>';
            select.disabled = false;
            
            if (profissionais.length === 0) {
                select.innerHTML = '<option value="">Nenhum profissional disponível</option>';
                document.getElementById('btnProfissionalProximo').disabled = true;
                return;
            }
            
            profissionais.forEach(p => {
                select.innerHTML += `<option value="${p.id}">${p.nome}</option>`;
            });
        })
        .catch(error => {
            console.error('Erro ao carregar profissionais:', error);
            select.innerHTML = '<option value="">Erro ao carregar profissionais</option>';
            select.disabled = false;
        });
}

function carregarHorarios() {
    const profissionalId = document.getElementById('selectProfissional').value;
    const servicoId = document.getElementById('selectServico').value;
    const data = document.getElementById('dataAgendamento').value;
    const horariosDiv = document.getElementById('horariosDisponiveis');
    
    horariosDiv.innerHTML = '<p class="loading">Carregando horários disponíveis</p>';
    
    fetch(`../backend/listar_horarios.php?profissional_id=${profissionalId}&servico_id=${servicoId}&data=${data}&barbeiro_id=${barbeiroId}`)
        .then(r => r.json())
        .then(horarios => {
            horariosDiv.innerHTML = '';
            
            if (horarios.length === 0) {
                horariosDiv.innerHTML = '<p class="alert">Não há horários disponíveis para esta data. Por favor, escolha outra data ou serviço.</p>';
                return;
            }
            
            horarios.forEach(horario => {
                let btn = document.createElement('button');
                btn.textContent = horario;
                btn.onclick = () => selecionarHorario(horario);
                horariosDiv.appendChild(btn);
            });
        })
        .catch(error => {
            console.error('Erro ao carregar horários:', error);
            horariosDiv.innerHTML = '<p class="alert">Erro ao carregar horários. Por favor, tente novamente.</p>';
        });
}

function selecionarHorario(horario) {
    agendamento.horario = horario;
    // Exibe resumo
    document.getElementById('resumoAgendamento').innerHTML =
        `<b>Data:</b> ${agendamento.data}<br>` +
        `<b>Serviço:</b> ${document.getElementById('selectServico').selectedOptions[0].text}<br>` +
        `<b>Profissional:</b> ${document.getElementById('selectProfissional').selectedOptions[0].text}<br>` +
        `<b>Horário:</b> ${horario}`;
    showStep('stepConfirmacao');
}

document.getElementById('btnConfirmar').onclick = () => {
    // Enviar agendamento via AJAX
    fetch('../backend/get_usuario_logado.php')
        .then(r => r.json())
        .then(user => {
            if (!user.logado || user.tipo !== 'cliente') {
                alert('Faça login como cliente!');
                window.location.href = 'index.html';
                return;
            }
            fetch('../backend/agendar.php', {
                method: 'POST',
                body: new URLSearchParams({
                    cliente_id: user.id,
                    profissional_id: document.getElementById('selectProfissional').value,
                    servico_id: document.getElementById('selectServico').value,
                    data: document.getElementById('dataAgendamento').value,
                    hora: agendamento.horario
                })
            })
            .then(r => r.json())
            .then(d => {
                document.getElementById('msgAgendamento').textContent = d.mensagem;
                if (d.sucesso) {
                    setTimeout(() => window.location.href = 'index.html', 2000);
                }
            });
        });
};
