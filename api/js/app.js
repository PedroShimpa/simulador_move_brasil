$(document).ready(function() {
    // Money mask initialization
    if ($.fn.mask) {
        $('.dinheiro').mask('000.000.000.000.000,00', { reverse: true });
    }

    // Helper to parse BRL money string to float
    function parseCurrencyBrl(valStr) {
        if (!valStr) return 0;
        let clean = valStr.replace(/\./g, '').replace(',', '.');
        return parseFloat(clean) || 0;
    }

    // Helper to format float to BRL currency string
    function formatCurrencyBrl(value) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
    }

    // Trigger computation on input adjustments
    $('#valor_veiculo, #entrada, #perfil').on('input change keyup', function() {
        calculateRealtime();
    });

    $('#sliderMeses').on('input', function() {
        $('#labelMeses').text($(this).val() + ' meses');
        calculateRealtime();
    });

    // Calculate loan details using Tabela Price formula
    function calculateRealtime() {
        const valorVeiculoStr = $('#valor_veiculo').val();
        const entradaStr = $('#entrada').val();
        
        const valorVeiculo = parseCurrencyBrl(valorVeiculoStr);
        const entrada = parseCurrencyBrl(entradaStr);
        const meses = parseInt($('#sliderMeses').val()) || 72;
        const perfil = $('#perfil').val();

        // Clear previous errors
        $('#js-erro').addClass('d-none').html('');
        $('#resultado-container').removeClass('opacity-50');

        if (valorVeiculo <= 0) {
            $('#resultado-container').addClass('opacity-50');
            return;
        }

        if (entrada > valorVeiculo) {
            $('#js-erro').removeClass('d-none').html('<i class="bi bi-exclamation-triangle-fill me-2"></i> A entrada não pode ser maior que o valor do veículo.');
            return;
        }

        if (entrada === valorVeiculo) {
            $('#js-erro').removeClass('d-none').html('<i class="bi bi-exclamation-triangle-fill me-2"></i> A entrada não pode ser igual ao valor do veículo.');
            return;
        }

        const valorFinanciado = valorVeiculo - entrada;
        const taxa = (perfil === 'F') ? 0.0091 : 0.0099;
        
        let parcela = 0;
        if (valorFinanciado > 0) {
            parcela = valorFinanciado * (taxa * Math.pow(1 + taxa, meses)) / (Math.pow(1 + taxa, meses) - 1);
        }

        const totalPago = parcela * meses;
        const juros = totalPago - valorFinanciado;
        const totalInvestido = entrada + totalPago;

        // Update results labels
        $('#res-parcela').text(formatCurrencyBrl(parcela));
        $('#res-taxa').text((taxa * 100).toFixed(2).replace('.', ',') + '% a.m.');
        $('#res-valor-financiado').text(formatCurrencyBrl(valorFinanciado));
        $('#res-total-pago').text(formatCurrencyBrl(totalPago));
        $('#res-juros').text(formatCurrencyBrl(juros));
        $('#res-total-investido').text(formatCurrencyBrl(totalInvestido));

        // Progress Bar Ratio calculation
        const totalPrincipal = valorFinanciado;
        const percentPrincipal = totalPago > 0 ? (totalPrincipal / totalPago) * 100 : 100;
        const percentJuros = totalPago > 0 ? (juros / totalPago) * 100 : 0;

        $('#bar-principal').css('width', percentPrincipal + '%').attr('title', 'Principal: ' + percentPrincipal.toFixed(1) + '%');
        $('#bar-juros').css('width', percentJuros + '%').attr('title', 'Juros: ' + percentJuros.toFixed(1) + '%');

        $('#bar-principal-label').text('Financiado: ' + percentPrincipal.toFixed(0) + '%');
        $('#bar-juros-label').text('Juros: ' + percentJuros.toFixed(0) + '%');
    }

    // Prevent form submit if inputs are invalid
    $('#formSimulador').on('submit', function(e) {
        const valorVeiculo = parseCurrencyBrl($('#valor_veiculo').val());
        const entrada = parseCurrencyBrl($('#entrada').val());

        if (entrada > valorVeiculo) {
            alert('A entrada não pode ser maior que o valor do veículo.');
            e.preventDefault();
        } else if (entrada === valorVeiculo) {
            alert('A entrada não pode ser igual ao valor do veículo.');
            e.preventDefault();
        }
    });

    // CARS LIST RENDERING & SEARCH ENGINE
    let activeTab = 'todos';
    let searchQuery = '';

    function renderCars() {
        const tbody = $('#car-table-body');
        if (!tbody.length || typeof listagemCarros === 'undefined') return;
        
        tbody.empty();

        // Filter cars data dynamically
        const filteredCars = listagemCarros.filter(car => {
            const matchesTab = activeTab === 'todos' || car.categoria === activeTab;
            const matchesSearch = car.nome.toLowerCase().includes(searchQuery.toLowerCase());
            return matchesTab && matchesSearch;
        });

        if (filteredCars.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">Nenhum veículo encontrado com os filtros selecionados.</td></tr>');
            return;
        }

        // Render matching rows
        filteredCars.forEach(car => {
            let catLabel = '';
            let badgeClass = '';
            
            if (car.categoria === 'aplicativos') {
                catLabel = 'Aplicativos';
                badgeClass = 'badge-app';
            } else if (car.categoria === 'taxistas_condutaxi') {
                catLabel = 'Condutaxi';
                badgeClass = 'badge-condutaxi';
            } else if (car.categoria === 'taxistas_isencao') {
                catLabel = 'Isenção Total';
                badgeClass = 'badge-isencao';
            }

            const valorFormatado = car.valor > 0 
                ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(car.valor)
                : 'Sob consulta';

            const rowHtml = `
                <tr>
                    <td data-label="Modelo do Veículo">${car.nome}</td>
                    <td data-label="Categoria">
                        <span class="badge-pill-custom ${badgeClass}">${catLabel}</span>
                    </td>
                    <td data-label="Valor do Programa">${valorFormatado}</td>
                    <td data-label="Ação" class="text-md-end">
                        <button type="button" class="btn-table-action" data-valor="${car.valor}">
                            Simular 🚘
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(rowHtml);
        });
    }

    // Search input filter triggers
    $('#searchCarro').on('input', function() {
        searchQuery = $(this).val();
        renderCars();
    });

    // Category Tab selector triggers
    $('#car-tabs .nav-link-custom').on('click', function() {
        $('#car-tabs .nav-link-custom').removeClass('active');
        $(this).addClass('active');
        activeTab = $(this).data('tab');
        renderCars();
    });

    // Action: Simular este carro click
    $(document).on('click', '.btn-table-action', function() {
        const valor = parseFloat($(this).data('valor'));
        if (valor > 0) {
            // Convert value to formatted string (BRL mask style)
            const formatted = new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(valor);
            
            // Set inputs
            $('#valor_veiculo').val(formatted).trigger('input');
            $('#entrada').val('0,00').trigger('input');
            
            // Add quick visual animation
            $('#valor_veiculo').addClass('glow-active');
            setTimeout(function() {
                $('#valor_veiculo').removeClass('glow-active');
            }, 2000);

            // Smooth scroll up to simulator
            $('html, body').animate({
                scrollTop: $("#formSimulador").offset().top - 120
            }, 600);
        }
    });

    // Run initial calculations on page load
    calculateRealtime();
    renderCars();
});
