// depurador_agendamentos.js - Script para acessar os endpoints de depuração

document.addEventListener('DOMContentLoaded', function() {
    // Definir a data atual no input
    const hoje = new Date();
    const dataFormatada = hoje.toISOString().split('T')[0]; // Formato YYYY-MM-DD
    
    document.getElementById('inputData').value = dataFormatada;
    
    // Evento para o botão de busca
    document.getElementById('btnBuscar').addEventListener('click', buscarAgendamentos);
    
    // Evento para o botão de buscar todos
    document.getElementById('btnBuscarTodos').addEventListener('click', function() {
        buscarAgendamentos(true);
    });
    
    // Inicializar a busca ao carregar a página
    buscarAgendamentos();
});

// Função para buscar agendamentos
function buscarAgendamentos(todos = false) {
    const data = document.getElementById('inputData').value;
    const url = todos 
        ? `../backend/debug_agendamentos.php?todos=true` 
        : `../backend/debug_agendamentos.php?data=${data}`;
    
    document.getElementById('resultado').innerHTML = '<p>Carregando dados...</p>';
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Mostrar dados de contexto
            let html = `
                <h3>Informações de Contexto</h3>
                <ul>
                    <li><strong>Data consultada:</strong> ${data.data_consulta}</li>
                    <li><strong>Data atual PHP:</strong> ${data.data_atual_php}</li>
                    <li><strong>Data e hora atual PHP:</strong> ${data.data_hora_atual_php}</li>
                    <li><strong>Timezone:</strong> ${data.timezone}</li>
                    <li><strong>Total de agendamentos:</strong> ${data.total_agendamentos}</li>
                </ul>
            `;
            
            // Mostrar agendamentos
            if (data.total_agendamentos > 0) {
                html += `
                    <h3>Agendamentos Encontrados</h3>
                    <table border="1" cellpadding="6" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Data</th>
                                <th>Data Formatada</th>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Profissional</th>
                                <th>Serviço</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.agendamentos.forEach(ag => {
                    html += `
                        <tr>
                            <td>${ag.id}</td>
                            <td>${ag.data}</td>
                            <td>${ag.data_formatada}</td>
                            <td>${ag.hora_formatada}</td>
                            <td>${ag.cliente}</td>
                            <td>${ag.profissional}</td>
                            <td>${ag.servico} (${ag.duracao_minutos} min)</td>
                            <td>${ag.status}</td>
                        </tr>
                    `;
                });
                
                html += `
                        </tbody>
                    </table>
                `;
            } else {
                html += `<p><strong>Nenhum agendamento encontrado para esta data.</strong></p>`;
            }
            
            document.getElementById('resultado').innerHTML = html;
        })
        .catch(error => {
            console.error('Erro ao buscar agendamentos:', error);
            document.getElementById('resultado').innerHTML = 
                '<p style="color: red;">Erro ao buscar agendamentos. Confira o console para mais detalhes.</p>';
        });
}
