// dashboard_barbeiro.js - Cadastro e listagem de serviços e horários do barbeiro

// Usa AJAX para buscar o usuário logado
fetch('../backend/get_usuario_logado.php')
    .then(r => r.json())
    .then(user => {
        if (!user.logado || user.tipo !== 'barbeiro') {
            alert('Faça login como barbeiro!');
            window.location.href = 'index.html';
        } else {
            window.barbeiro_id = user.id;
            listarServicos();
            listarHorarios();
        }
    });

// MODAIS DE SERVIÇO E HORÁRIO
const modalServico = document.getElementById('modalServico');
const modalHorario = document.getElementById('modalHorario');
const btnAbrirModalServico = document.getElementById('btnAbrirModalServico');
const btnAbrirModalHorario = document.getElementById('btnAbrirModalHorario');
const fecharModalServico = document.getElementById('fecharModalServico');
const fecharModalHorario = document.getElementById('fecharModalHorario');

btnAbrirModalServico.onclick = () => {
    document.getElementById('formServico').reset();
    document.getElementById('tituloModalServico').textContent = 'Cadastrar Serviço';
    document.querySelector('#formServico button[type=submit]').textContent = 'Salvar';
    modalServico.style.display = 'flex';
};
fecharModalServico.onclick = () => modalServico.style.display = 'none';

btnAbrirModalHorario.onclick = () => {
    document.getElementById('formHorario').reset();
    modalHorario.style.display = 'flex';
};
fecharModalHorario.onclick = () => modalHorario.style.display = 'none';

// Fecha modal ao clicar fora do conteúdo
window.onclick = function(event) {
    if (event.target === modalServico) modalServico.style.display = 'none';
    if (event.target === modalHorario) modalHorario.style.display = 'none';
};

// --- Serviços ---
const formServico = document.getElementById('formServico');
formServico.onsubmit = function(e) {
    e.preventDefault();
    const nome = document.getElementById('nomeServico').value;
    const duracao = document.getElementById('duracaoServico').value;
    const preco = document.getElementById('precoServico').value;
    const editId = formServico.getAttribute('data-edit-id');
    let url = '../backend/cadastrar_servico.php';
    let body = { nome, duracao_minutos: duracao, preco, barbeiro_id: window.barbeiro_id };
    if (editId) {
        url = '../backend/editar_servico.php';
        body.id = editId;
    }
    fetch(url, {
        method: 'POST',
        body: new URLSearchParams(body)
    })
    .then(r => r.json())
    .then(d => {
        document.getElementById('msgServico').textContent = d.mensagem;
        if (d.sucesso) {
            formServico.reset();
            formServico.removeAttribute('data-edit-id');
            document.querySelector('#formServico button[type=submit]').textContent = 'Cadastrar Serviço';
            listarServicos();
        }
        modalServico.style.display = 'none';
    });
};

function listarServicos() {
    if (!window.barbeiro_id) return;
    fetch('../backend/listar_servicos.php?barbeiro_id=' + window.barbeiro_id)
        .then(r => r.json())
        .then(servicos => {
            const ul = document.getElementById('listaServicos');
            ul.innerHTML = '';
            if (servicos.length === 0) {
                ul.innerHTML = '<li><i>Nenhum serviço cadastrado</i></li>';
                return;
            }
            servicos.forEach(s => {
                const li = document.createElement('li');
                li.innerHTML = `${s.nome} (${s.duracao_minutos}min) - R$ ${s.preco} ` +
                    `<button onclick="editarServico(${s.id}, '${s.nome}', ${s.duracao_minutos}, ${s.preco})">Editar</button> ` +
                    `<button onclick="removerServico(${s.id})">Remover</button>`;
                ul.appendChild(li);
            });
        });
}

window.editarServico = function(id, nome, duracao, preco) {
    document.getElementById('nomeServico').value = nome;
    document.getElementById('duracaoServico').value = duracao;
    document.getElementById('precoServico').value = preco;
    document.getElementById('tituloModalServico').textContent = 'Editar Serviço';
    document.querySelector('#formServico button[type=submit]').textContent = 'Salvar Alterações';
    document.getElementById('formServico').setAttribute('data-edit-id', id);
    modalServico.style.display = 'flex';
};

window.removerServico = function(id) {
    if (!confirm('Remover este serviço?')) return;
    fetch('../backend/remover_servico.php', {
        method: 'POST',
        body: new URLSearchParams({ id })
    })
    .then(r => r.json())
    .then(() => listarServicos());
};

// --- Horários ---
const formHorario = document.getElementById('formHorario');
formHorario.onsubmit = function(e) {
    e.preventDefault();
    
    // Array de dias da semana
    const dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
    let promises = [];
    let mensagens = [];
    
    // Para cada dia, fazer uma requisição separada
    dias.forEach(dia => {
        const inicio = formHorario[`inicio_${dia}`].value;
        const fim = formHorario[`fim_${dia}`].value;
        const fechado = formHorario[`fechado_${dia}`].checked ? 1 : 0;
        
        // Se o usuário preencheu os horários ou marcou como fechado
        if ((inicio && fim) || fechado) {
            // Criamos um FormData para enviar via POST
            const formData = new FormData();
            formData.append('barbeiro_id', window.barbeiro_id);
            formData.append('dia_semana', dia);
            formData.append('hora_inicio', inicio);
            formData.append('hora_fim', fim);
            
            if (fechado) {
                formData.append('fechado', 1);
            }
            
            // Enviamos a requisição e armazenamos a promise para tratar depois
            const promise = fetch('../backend/cadastrar_horario.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    mensagens.push(`${dia}: ${data.mensagem}`);
                    return true;
                } else {
                    mensagens.push(`${dia}: Erro - ${data.mensagem}`);
                    return false;
                }
            });
            
            promises.push(promise);
        }
    });
    
    // Se nenhum horário foi configurado
    if (promises.length === 0) {
        document.getElementById('msgHorario').textContent = 'Configure pelo menos um dia da semana!';
        return;
    }
    
    // Esperamos todas as promises terminarem
    Promise.all(promises).then(results => {
        // Se todos os resultados foram bem-sucedidos
        if (results.every(r => r === true)) {
            document.getElementById('msgHorario').textContent = 'Horários salvos com sucesso!';
            modalHorario.style.display = 'none';
            listarHorarios();
        } else {
            document.getElementById('msgHorario').textContent = 'Ocorreram erros: ' + mensagens.join('; ');
        }
    });
};

function listarHorarios() {
    if (!window.barbeiro_id) return;
    
    fetch('../backend/listar_horarios_funcionamento.php?barbeiro_id=' + window.barbeiro_id)
        .then(r => r.json())
        .then(horarios => {
            const ul = document.getElementById('listaHorarios');
            ul.innerHTML = '';
            
            // Mapeia nomes dos dias para exibição
            const nomes = {
                'segunda': 'Segunda-feira',
                'terca': 'Terça-feira',
                'quarta': 'Quarta-feira',
                'quinta': 'Quinta-feira',
                'sexta': 'Sexta-feira',
                'sabado': 'Sábado',
                'domingo': 'Domingo'
            };
            
            // Garante exibição de todos os dias
            const dias = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
            
            // Para cada dia da semana
            dias.forEach(dia => {
                // Procura se há configuração para este dia
                const horario = horarios.find(h => h.dia_semana === dia);
                
                // Cria elemento li
                const li = document.createElement('li');
                
                // Define o conteúdo conforme existência de horário
                if (horario) {
                    if (parseInt(horario.fechado) === 1) {
                        li.innerHTML = `<strong>${nomes[dia]}:</strong> <span style="color: red;">Fechado</span>`;
                    } else {
                        li.innerHTML = `<strong>${nomes[dia]}:</strong> ${horario.hora_inicio} às ${horario.hora_fim}`;
                    }
                } else {
                    li.innerHTML = `<strong>${nomes[dia]}:</strong> <i>Não configurado</i>`;
                }
                
                ul.appendChild(li);
            });
        })
        .catch(error => {
            console.error('Erro ao buscar horários:', error);
            document.getElementById('listaHorarios').innerHTML = 
                '<li style="color: red;">Erro ao carregar horários. Tente novamente mais tarde.</li>';
        });
}

window.editarHorario = function(id, dia, inicio, fim) {
    document.getElementById('diaSemana').value = dia;
    document.getElementById('horaInicio').value = inicio;
    document.getElementById('horaFim').value = fim;
    document.getElementById('tituloModalHorario').textContent = 'Editar Horário';
    document.querySelector('#formHorario button[type=submit]').textContent = 'Salvar Alterações';
    document.getElementById('formHorario').setAttribute('data-edit-id', id);
    modalHorario.style.display = 'flex';
};

window.removerHorario = function(id) {
    if (!confirm('Remover este horário?')) return;
    fetch('../backend/remover_horario.php', {
        method: 'POST',
        body: new URLSearchParams({ id })
    })
    .then(r => r.json())
    .then(() => listarHorarios());
};

// Gera campos de horários para todos os dias da semana
function renderDiasSemanaHorarios(horarios = {}) {
    const dias = [
        'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'
    ];
    const nomes = {
        segunda: 'Segunda', terca: 'Terça', quarta: 'Quarta', quinta: 'Quinta',
        sexta: 'Sexta', sabado: 'Sábado', domingo: 'Domingo'
    };
    let html = '';
    dias.forEach(dia => {
        const h = horarios[dia] || {};
        html += `<div class="dia-horario">
            <label><b>${nomes[dia]}</b></label>
            <label><input type="checkbox" class="fechado-dia" data-dia="${dia}" ${h.fechado ? 'checked' : ''}> Fechado</label>
            <div class="horario-dia-campos" data-dia="${dia}" ${h.fechado ? 'style="display:none"' : ''}>
                <label>Início <input type="time" class="inicio-dia" data-dia="${dia}" value="${h.inicio || ''}"></label>
                <label>Fim <input type="time" class="fim-dia" data-dia="${dia}" value="${h.fim || ''}"></label>
                <div class="pausas-dia" data-dia="${dia}">
                    ${(h.pausas||[]).map((p,i) => `
                        <div class="pausa-item">
                            <label>Pausa início <input type="time" class="pausa-inicio" data-dia="${dia}" data-idx="${i}" value="${p.inicio}"></label>
                            <label>Pausa fim <input type="time" class="pausa-fim" data-dia="${dia}" data-idx="${i}" value="${p.fim}"></label>
                            <button type="button" class="btn-ios btn-remover-pausa" data-dia="${dia}" data-idx="${i}">Remover pausa</button>
                        </div>`).join('')}
                </div>
                <button type="button" class="btn-ios btn-add-pausa" data-dia="${dia}">+ Adicionar pausa</button>
            </div>
        </div><hr>`;
    });
    document.getElementById('diasSemanaHorarios').innerHTML = html;
}

// Lógica para mostrar/ocultar campos ao marcar "Fechado"
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('fechado-dia')) {
        const dia = e.target.getAttribute('data-dia');
        const campos = document.querySelector(`.horario-dia-campos[data-dia='${dia}']`);
        if (campos) campos.style.display = e.target.checked ? 'none' : '';
    }
});

// Adicionar pausa
let pausasData = {};
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-add-pausa')) {
        const dia = e.target.getAttribute('data-dia');
        pausasData[dia] = pausasData[dia] || [];
        pausasData[dia].push({inicio:'',fim:''});
        renderDiasSemanaHorarios(pausasData);
    }
    if (e.target.classList.contains('btn-remover-pausa')) {
        const dia = e.target.getAttribute('data-dia');
        const idx = +e.target.getAttribute('data-idx');
        pausasData[dia].splice(idx,1);
        renderDiasSemanaHorarios(pausasData);
    }
});
