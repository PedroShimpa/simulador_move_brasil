<?php

$resultado = null;
$erro = null;

function moedaBrParaFloat($valor)
{
    if (empty($valor)) return 0;
    if (is_numeric($valor)) return floatval($valor);
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    return floatval($valor);
}

// Cars parsing logic
$carros_aplicativos = [];
$carros_taxistas_condutaxi = [];
$carros_taxistas_isencao = [];
$arquivo_carros = __DIR__ . '/carros_autorizados.txt';

if (file_exists($arquivo_carros)) {
    $linhas = file($arquivo_carros);
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || $linha[0] === '#') continue;
        
        $partes = explode(';', $linha);
        if (count($partes) >= 3) {
            $cat = $partes[0];
            $nome = $partes[1];
            $valor = floatval($partes[2]);
            
            $item = [
                'nome' => $nome,
                'valor' => $valor
            ];
            
            if ($cat === 'aplicativos') $carros_aplicativos[] = $item;
            if ($cat === 'taxistas_condutaxi') $carros_taxistas_condutaxi[] = $item;
            if ($cat === 'taxistas_isencao') $carros_taxistas_isencao[] = $item;
        }
    }
}

// Gather all cars for JS injection
$todos_carros = [];
foreach ($carros_aplicativos as $c) {
    $todos_carros[] = ['categoria' => 'aplicativos', 'nome' => $c['nome'], 'valor' => $c['valor']];
}
foreach ($carros_taxistas_condutaxi as $c) {
    $todos_carros[] = ['categoria' => 'taxistas_condutaxi', 'nome' => $c['nome'], 'valor' => $c['valor']];
}
foreach ($carros_taxistas_isencao as $c) {
    $todos_carros[] = ['categoria' => 'taxistas_isencao', 'nome' => $c['nome'], 'valor' => $c['valor']];
}

// Initial form variables
$valorVeiculoPost = $_POST['valor_veiculo'] ?? '';
$entradaPost = $_POST['entrada'] ?? '0,00';
$mesesPost = isset($_POST['meses']) ? intval($_POST['meses']) : 72;
$perfilPost = $_POST['perfil'] ?? 'M';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $valorVeiculo = moedaBrParaFloat($valorVeiculoPost);
    $entrada = moedaBrParaFloat($entradaPost);
    $meses = $mesesPost;
    $perfil = $perfilPost;

    // Taxa Move Brasil
    $taxa = ($perfil == 'F') ? 0.0091 : 0.0099;

    if ($entrada > $valorVeiculo) {
        $erro = 'A entrada não pode ser maior que o valor do veículo.';
    } elseif ($entrada == $valorVeiculo) {
        $erro = 'A entrada não pode ser igual ao valor do veículo.';
    }

    $valorFinanciado = $valorVeiculo - $entrada;

    if (!$erro && $valorFinanciado > 0) {
        $parcela = $valorFinanciado * ($taxa * pow(1 + $taxa, $meses)) / (pow(1 + $taxa, $meses) - 1);
        $totalPago = $parcela * $meses;
        $juros = $totalPago - $valorFinanciado;
        $totalInvestido = $entrada + $totalPago;

        $resultado = [
            'valor_veiculo' => $valorVeiculo,
            'entrada' => $entrada,
            'valor_financiado' => $valorFinanciado,
            'parcela' => $parcela,
            'total_pago' => $totalPago,
            'juros' => $juros,
            'total_investido' => $totalInvestido,
            'taxa_percent' => $taxa * 100
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador Move Brasil - Financiamento para Táxi e Aplicativos</title>
    
    <!-- SEO and Meta Tags -->
    <meta name="description" content="Simule financiamento de veículos para táxi e aplicativos com as melhores taxas do Brasil. Veja parcelas, juros e carros autorizados. Desenvolvido por Pedro Falconi.">
    <meta name="keywords" content="simulador, financiamento, táxi, aplicativos, carros, parcelas, juros, move brasil, condutaxi, isenção, veículos, Pedro Falconi">
    <meta name="author" content="Pedro Falconi">
    <meta property="og:title" content="Simulador Move Brasil - Financiamento para Táxi e Aplicativos">
    <meta property="og:description" content="Simule financiamento de veículos para táxi e aplicativos com as melhores taxas do Brasil. Veja parcelas, juros e carros autorizados.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://movebrasil.com.br/">
    <meta property="og:image" content="https://movebrasil.com.br/assets/simulador-move-brasil.png">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://movebrasil.com.br/">

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- External Separated Stylesheet -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <!-- Floating Action Button -->
    <a href="#veiculos-autorizados" class="btn-flutuante">
        <i class="bi bi-car-front-fill"></i> Ver Veículos Autorizados
    </a>

    <!-- Header Banner -->
    <header class="header-bg text-center">
        <div class="container">
            <h1 class="fw-extrabold display-4 mb-3" style="font-weight: 800;">
                🚖 Simulador Move Brasil
            </h1>
            <p class="lead opacity-90 max-width-600 mx-auto px-3" style="font-weight: 500;">
                Calcule seu financiamento para táxi ou aplicativos gratuitamente.
            </p>
        </div>
    </header>

    <!-- Side-by-Side Wrapper for Sticky Gutter Ads -->
    <div class="d-flex justify-content-center align-items-start px-2 overlap-content">
        
        <!-- LEFT STICKY AD: Skyscraper (160x600) -->
        <div class="d-none d-xxl-block position-sticky" style="top: 24px; width: 182px; z-index: 5; margin-right: 20px;">
            <div class="ad-wrapper ad-wrapper-vertical" style="width: 182px;">
                <span class="ad-label">Publicidade</span>
                <div style="min-height: 600px;">
                    <script>
                      atOptions = {
                        'key' : '90b6b6c03a8928cbe98f8b331754f531',
                        'format' : 'iframe',
                        'height' : 600,
                        'width' : 160,
                        'params' : {}
                      };
                    </script>
                    <script src="https://www.highperformanceformat.com/90b6b6c03a8928cbe98f8b331754f531/invoke.js"></script>
                </div>
            </div>
        </div>

        <!-- MAIN CENTRAL CONTENT -->
        <div style="flex: 1; max-width: 1200px;">

            <!-- AD TOP 1: Desktop Leaderboard (728x90) / Mobile Banner (320x50) -->
            <div class="text-center mb-4">
                <!-- Desktop Leaderboard -->
                <div class="ad-wrapper d-none d-lg-inline-block">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 90px; width: 728px;">
                        <script>
                          atOptions = {
                            'key' : 'e2a626177246027d7c16f00a1d96bd16',
                            'format' : 'iframe',
                            'height' : 90,
                            'width' : 728,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/e2a626177246027d7c16f00a1d96bd16/invoke.js"></script>
                    </div>
                </div>

                <!-- Mobile Banner -->
                <div class="ad-wrapper d-inline-block d-lg-none">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 50px; width: 320px;">
                        <script>
                          atOptions = {
                            'key' : 'b2ae2344c933833261eff651b9f8306c',
                            'format' : 'iframe',
                            'height' : 50,
                            'width' : 320,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
                    </div>
                </div>
            </div>

            <!-- Simulator Section Grid -->
            <div class="row g-4 mb-5">
                
                <!-- Form Card (Left) -->
                <div class="col-lg-5 d-flex flex-column gap-4">
                    <div class="card card-custom p-4 flex-grow-1">
                        <h4 class="fw-bold mb-4 d-flex align-items-center gap-2">
                            <i class="bi bi-sliders text-emerald"></i>
                            Simulação
                        </h4>
                        
                        <form method="POST" id="formSimulador" action="#resultado-ancora">
                            
                            <!-- Valor do Veiculo -->
                            <div class="mb-4">
                                <label class="form-label" for="valor_veiculo">Valor do Veículo</label>
                                <div class="input-group-custom">
                                    <span class="input-addon">R$</span>
                                    <input
                                        type="text"
                                        name="valor_veiculo"
                                        id="valor_veiculo"
                                        class="form-control-custom dinheiro"
                                        required
                                        placeholder="150.000,00"
                                        value="<?= htmlspecialchars($valorVeiculoPost) ?>">
                                </div>
                            </div>

                            <!-- Entrada -->
                            <div class="mb-4">
                                <label class="form-label" for="entrada">Entrada</label>
                                <div class="input-group-custom">
                                    <span class="input-addon">R$</span>
                                    <input
                                        type="text"
                                        name="entrada"
                                        id="entrada"
                                        class="form-control-custom dinheiro"
                                        placeholder="0,00"
                                        value="<?= htmlspecialchars($entradaPost) ?>">
                                </div>
                            </div>

                            <!-- Parcelas -->
                            <div class="mb-4">
                                <label class="form-label">Prazo de Pagamento</label>
                                <div class="range-container">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted small fw-bold">Meses</span>
                                        <span id="labelMeses" class="fw-bold text-success" style="font-size: 16px;">
                                            <?= $mesesPost ?> meses
                                        </span>
                                    </div>
                                    <input
                                        type="range"
                                        name="meses"
                                        id="sliderMeses"
                                        class="form-range"
                                        min="12"
                                        max="72"
                                        step="12"
                                        value="<?= $mesesPost ?>">
                                    <div class="d-flex justify-content-between text-muted small px-1 mt-1">
                                        <span>12m</span>
                                        <span>24m</span>
                                        <span>36m</span>
                                        <span>48m</span>
                                        <span>60m</span>
                                        <span>72m</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Perfil -->
                            <div class="mb-4">
                                <label class="form-label" for="perfil">Perfil do Beneficiário</label>
                                <select
                                    name="perfil"
                                    id="perfil"
                                    class="form-control-custom form-select">
                                    <option value="M" <?= $perfilPost === 'M' ? 'selected' : '' ?>>
                                        Homem (Taxa de 0,99% a.m.)
                                    </option>
                                    <option value="F" <?= $perfilPost === 'F' ? 'selected' : '' ?>>
                                        Mulher (Taxa Especial de 0,91% a.m.)
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-3" style="font-size: 16px; background-color: var(--primary-color); border: none; transition: var(--transition-smooth);">
                                <i class="bi bi-calculator-fill me-2"></i> Calcular Parcelas
                            </button>
                        </form>
                    </div>
                    <!-- AD 4: Medium Rectangle (300x250) under form -->
                    <div class="text-center">
                        <div class="ad-wrapper d-flex flex-column align-items-center mb-0">
                            <span class="ad-label">Publicidade</span>
                            <div style="min-height: 250px; width: 300px;">
                                <script>
                                  atOptions = {
                                    'key' : '685dfb69e14e9da46e83769261d11dbf',
                                    'format' : 'iframe',
                                    'height' : 250,
                                    'width' : 300,
                                    'params' : {}
                                  };
                                </script>
                                <script src="https://www.highperformanceformat.com/685dfb69e14e9da46e83769261d11dbf/invoke.js"></script>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Card (Right) -->
                <div class="col-lg-7" id="resultado-ancora">
                    <div class="card card-custom p-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <h4 class="fw-bold mb-4 d-flex align-items-center gap-2">
                                <i class="bi bi-bar-chart-fill text-emerald"></i>
                                Resultado do Cálculo
                            </h4>

                            <!-- Client-side / Server-side Errors -->
                            <div id="js-erro" class="alert alert-danger d-none mb-4"></div>
                            <?php if ($erro): ?>
                                <div class="alert alert-danger mb-4">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($erro) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Results Content Panel -->
                            <div id="resultado-container" class="resultado-box mb-4 <?= (!$resultado && !$erro) ? 'opacity-50' : '' ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-7 mb-3 mb-md-0">
                                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Parcela Mensal Estimada</span>
                                        <div class="highlight-value mt-1" id="res-parcela">
                                            R$ <?= $resultado ? number_format($resultado['parcela'], 2, ',', '.') : '0,00' ?>
                                        </div>
                                        <small class="text-muted block mt-1">Calculado via Tabela Price</small>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="bg-white p-3 rounded-3 shadow-sm border border-light">
                                            <div class="small fw-bold text-muted">Taxa Utilizada</div>
                                            <div class="h5 fw-bold text-success mb-0" id="res-taxa">
                                                <?= $resultado ? number_format($resultado['taxa_percent'], 2, ',', '.') . '% a.m.' : '0,99% a.m.' ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Progress Bar Visualizer -->
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between small fw-bold mb-2">
                                        <span class="text-success" id="bar-principal-label">Financiado: --</span>
                                        <span class="text-danger" id="bar-juros-label">Juros: --</span>
                                    </div>
                                    <div class="modern-progress">
                                        <div class="progress-bar-financed" id="bar-principal" style="width: 50%" title="Financiado"></div>
                                        <div class="progress-bar-interest" id="bar-juros" style="width: 50%" title="Juros"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Grid Breakdown -->
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted fw-bold d-block">Valor Financiado</small>
                                        <span class="h6 fw-bold mb-0 text-dark d-inline-block mt-1" id="res-valor-financiado">
                                            R$ <?= $resultado ? number_format($resultado['valor_financiado'], 2, ',', '.') : 'R$ 0,00' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted fw-bold d-block">Total das Parcelas</small>
                                        <span class="h6 fw-bold mb-0 text-dark d-inline-block mt-1" id="res-total-pago">
                                            R$ <?= $resultado ? number_format($resultado['total_pago'], 2, ',', '.') : 'R$ 0,00' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted fw-bold d-block">Total em Juros</small>
                                        <span class="h6 fw-bold mb-0 text-danger d-inline-block mt-1" id="res-juros">
                                            R$ <?= $resultado ? number_format($resultado['juros'], 2, ',', '.') : 'R$ 0,00' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 rounded-3 border" style="background-color: #eff6ff; border-color: #bfdbfe !important;">
                                        <small class="text-primary fw-bold d-block">💰 Total Investido</small>
                                        <span class="h6 fw-bold mb-0 text-primary d-inline-block mt-1" id="res-total-investido">
                                            R$ <?= $resultado ? number_format($resultado['total_investido'], 2, ',', '.') : 'R$ 0,00' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AD 3: Medium Rectangle (300x250) placed beautifully under results -->
                        <div class="text-center my-3">
                            <div class="ad-wrapper d-flex flex-column align-items-center">
                                <span class="ad-label">Publicidade</span>
                                <div style="min-height: 250px; width: 300px;">
                                    <script>
                                      atOptions = {
                                        'key' : '685dfb69e14e9da46e83769261d11dbf',
                                        'format' : 'iframe',
                                        'height' : 250,
                                        'width' : 300,
                                        'params' : {}
                                      };
                                    </script>
                                    <script src="https://www.highperformanceformat.com/685dfb69e14e9da46e83769261d11dbf/invoke.js"></script>
                                </div>
                            </div>
                        </div>

                        <!-- Accordion Formula -->
                        <div class="accordion border-0" id="accordionFormula">
                            <div class="accordion-item border rounded-3 overflow-hidden">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-muted small py-2 px-3 bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false">
                                        <i class="bi bi-info-circle-fill me-2 text-info"></i> Como a parcela é calculada?
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionFormula">
                                    <div class="accordion-body bg-light text-muted small">
                                        <p class="mb-2">Utilizamos o <b>Sistema Francês de Amortização (Tabela Price)</b> com juros compostos capitalizados mensalmente:</p>
                                        <div class="p-2 bg-white rounded border font-monospace text-center mb-2" style="font-size: 11px;">
                                            Parcela = F × [ i × (1 + i)<sup>n</sup> ] / [ (1 + i)<sup>n</sup> - 1 ]
                                        </div>
                                        <ul class="mb-0 ps-3">
                                            <li><b>F:</b> Valor financiado (Preço do Veículo - Entrada)</li>
                                            <li><b>i:</b> Taxa de juros mensal (0,91% para mulheres e 0,99% para homens)</li>
                                            <li><b>n:</b> Prazo (número de parcelas em meses)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- AD TOP 3: Desktop Leaderboard (728x90) / Mobile Banner (320x50) -->
            <div class="text-center mb-4">
                <!-- Desktop Leaderboard -->
                <div class="ad-wrapper d-none d-lg-inline-block">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 90px; width: 728px;">
                        <script>
                          atOptions = {
                            'key' : 'e2a626177246027d7c16f00a1d96bd16',
                            'format' : 'iframe',
                            'height' : 90,
                            'width' : 728,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/e2a626177246027d7c16f00a1d96bd16/invoke.js"></script>
                    </div>
                </div>

                <!-- Mobile Banner -->
                <div class="ad-wrapper d-inline-block d-lg-none">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 50px; width: 320px;">
                        <script>
                          atOptions = {
                            'key' : 'b2ae2344c933833261eff651b9f8306c',
                            'format' : 'iframe',
                            'height' : 50,
                            'width' : 320,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
                    </div>
                </div>
            </div>

            <!-- Benefits and Information Section -->
            <section class="my-5">
                <div class="card card-custom border-0 overflow-hidden">
                    <div class="p-4 p-md-5 text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                        <h3 class="fw-bold mb-2">
                            <i class="bi bi-info-circle-fill me-2"></i> Move Brasil – Táxi e Aplicativos
                        </h3>
                        <p class="mb-0 lead opacity-90">
                            O programa do Governo Federal para incentivo e financiamento de veículos novos com taxas diferenciadas.
                        </p>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <!-- Highlight Benefits grid -->
                        <div class="alert alert-success border-0 p-4 rounded-4 mb-5">
                            <h5 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill me-2"></i> Principais Benefícios do Programa</h5>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-4">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-percent text-success fs-5"></i>
                                        <span>Financiamento com juros reduzidos (abaixo do mercado)</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-calendar-event text-success fs-5"></i>
                                        <span>Prazo estendido em até 72 meses para pagar</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-hourglass-split text-success fs-5"></i>
                                        <span>Até 6 meses de carência opcional</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-flower1 text-success fs-5"></i>
                                        <span>Foco em veículos ecológicos e sustentáveis</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4">
                                    <div class="d-flex gap-2">
                                        <i class="bi bi-cash-stack text-success fs-5"></i>
                                        <span>Entrada reduzida ou zerada (conforme análise)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Rules Split -->
                        <div class="row g-4 mb-5">
                            <div class="col-lg-6">
                                <div class="card h-100 border-0 bg-light p-4 rounded-4">
                                    <h5 class="fw-bold text-success mb-3">
                                        <i class="bi bi-phone-fill me-2"></i> Motoristas de Aplicativo
                                    </h5>
                                    <ul class="d-flex flex-column gap-2 text-muted ps-3 mb-0">
                                        <li>Cadastro ativo e validado na plataforma há pelo menos <b>12 meses</b>.</li>
                                        <li>Realização de no mínimo <b>100 corridas</b> no último ano.</li>
                                        <li>Operar no mesmo município/região metropolitana do cadastro.</li>
                                        <li>Realizar a adesão no portal oficial <b>GOV.BR</b>.</li>
                                        <li>Sujeito às condições padrão de análise de crédito.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card h-100 border-0 bg-light p-4 rounded-4">
                                    <h5 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-taxi-front-fill me-2"></i> Taxistas Profissionais
                                    </h5>
                                    <ul class="d-flex flex-column gap-2 text-muted ps-3 mb-0">
                                        <li>Licença, autorização ou alvará ativo municipal em dia.</li>
                                        <li>Certidão de regularidade fiscal e cadastral.</li>
                                        <li>Homologação junto à prefeitura ou órgão de transporte.</li>
                                        <li>Possibilidade de financiamento para cooperativas de táxi.</li>
                                        <li>Sujeito à análise de crédito do banco financiador.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Vehicle Rules -->
                        <div class="card border-0 bg-dark text-white p-4 rounded-4 mb-5">
                            <h5 class="fw-bold mb-3 text-warning">
                                <i class="bi bi-car-front-fill me-2"></i> Regras e Elegibilidade dos Veículos
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 bg-secondary bg-opacity-25 rounded-3">
                                        <small class="text-warning fw-bold d-block mb-1">Estado</small>
                                        <span>Veículo 100% Zero Km</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 bg-secondary bg-opacity-25 rounded-3">
                                        <small class="text-warning fw-bold d-block mb-1">Valor Limite</small>
                                        <span>Até R$ 150.000,00 na NF</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 bg-secondary bg-opacity-25 rounded-3">
                                        <small class="text-warning fw-bold d-block mb-1">Fabricante</small>
                                        <span>Montadora no Programa Mover</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <div class="p-3 bg-secondary bg-opacity-25 rounded-3">
                                        <small class="text-warning fw-bold d-block mb-1">Combustível</small>
                                        <span>Flex, Etanol, Híbrido, Elétrico</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step by Step -->
                        <h5 class="fw-bold text-center mb-4">Passo a Passo para Obter o Financiamento</h5>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="mx-auto rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 24px; font-weight: 800;">1</div>
                                    <h6 class="fw-bold">Cadastro Oficial</h6>
                                    <p class="small text-muted mb-0">Registre seu interesse e comprove suas atividades pelo portal oficial do GOV.BR.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="mx-auto rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 24px; font-weight: 800;">2</div>
                                    <h6 class="fw-bold">Análise e Validação</h6>
                                    <p class="small text-muted mb-0">O sistema analisa e emite a autorização de elegibilidade do motorista em até 5 dias úteis.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="mx-auto rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 24px; font-weight: 800;">3</div>
                                    <h6 class="fw-bold">Conclusão</h6>
                                    <p class="small text-muted mb-0">Dirija-se a uma concessionária credenciada para emitir a nota fiscal e contratar o financiamento.</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4 mb-0 small">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            O valor limite de R$ 150 mil na nota fiscal é para os veículos de entrada. A aprovação de crédito depende exclusivamente de critérios definidos pelo banco parceiro escolhido.
                        </div>
                    </div>
                </div>
            </section>

            <!-- AD TOP 2: Desktop Leaderboard (728x90) / Mobile Banner (320x50) above vehicles -->
            <div class="text-center mb-4">
                <!-- Desktop Leaderboard -->
                <div class="ad-wrapper d-none d-lg-inline-block">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 90px; width: 728px;">
                        <script>
                          atOptions = {
                            'key' : 'e2a626177246027d7c16f00a1d96bd16',
                            'format' : 'iframe',
                            'height' : 90,
                            'width' : 728,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/e2a626177246027d7c16f00a1d96bd16/invoke.js"></script>
                    </div>
                </div>

                <!-- Mobile Banner -->
                <div class="ad-wrapper d-inline-block d-lg-none">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 50px; width: 320px;">
                        <script>
                          atOptions = {
                            'key' : 'b2ae2344c933833261eff651b9f8306c',
                            'format' : 'iframe',
                            'height' : 50,
                            'width' : 320,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
                    </div>
                </div>
            </div>

            <!-- Vehicles Database Section -->
            <section class="my-5" id="veiculos-autorizados">
                <div class="card card-custom p-4 p-md-5">
                    
                    <div class="row align-items-center g-4 mb-4">
                        <div class="col-md-7">
                            <h3 class="fw-bold mb-2">
                                🚘 Carros Autorizados no Move Brasil
                            </h3>
                            <p class="text-muted mb-0">
                                Clique em <b class="text-success">Simular 🚘</b> ao lado de qualquer veículo para carregar o preço automaticamente no painel de simulação.
                            </p>
                        </div>
                        <div class="col-md-5">
                            <!-- Dynamic Search Box -->
                            <div class="car-search-box">
                                <i class="bi bi-search search-icon"></i>
                                <input 
                                    type="text" 
                                    id="searchCarro" 
                                    class="form-control-custom" 
                                    placeholder="Buscar carro por modelo (Ex: Polo, Spin)...">
                            </div>
                        </div>
                    </div>

                    <!-- Caution Message -->
                    <div class="alert alert-warning border-0 shadow-sm d-flex gap-3 mb-4 rounded-4">
                        <div class="fs-3"><i class="bi bi-exclamation-triangle-fill text-warning"></i></div>
                        <div>
                            <h6 class="fw-bold mb-1">Sobre a listagem de valores</h6>
                            <p class="small text-muted mb-0">
                                Esta lista é preenchida com valores base coletados de concessionárias. Caso um veículo não conste, 
                                pode ser devido a atrasos na divulgação de preços oficiais pela montadora. A validação final 
                                do veículo é feita mediante a Nota Fiscal emitida.
                            </p>
                        </div>
                    </div>

                    <!-- Tab Selectors -->
                    <div class="mb-4">
                        <div class="nav-pills-custom" id="car-tabs">
                            <button class="nav-link-custom active" data-tab="todos">Todos os Veículos</button>
                            <button class="nav-link-custom" data-tab="aplicativos">Aplicativos (Sem Isenção)</button>
                            <button class="nav-link-custom" data-tab="taxistas_condutaxi">Taxistas (Condutaxi)</button>
                            <button class="nav-link-custom" data-tab="taxistas_isencao">Taxistas (Isenção Total)</button>
                        </div>
                    </div>

                    <!-- Cars Table -->
                    <div class="table-responsive bg-light rounded-4 border overflow-hidden">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>Modelo do Veículo</th>
                                    <th>Categoria</th>
                                    <th>Valor do Programa</th>
                                    <th class="text-end">Ação</th>
                                </tr>
                            </thead>
                            <tbody id="car-table-body">
                                <!-- Car rows loaded dynamically by JavaScript -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </section>

            <!-- AD TOP 4: Desktop Leaderboard (728x90) / Mobile Banner (320x50) below vehicles -->
            <div class="text-center mt-4 mb-2">
                <!-- Desktop Leaderboard -->
                <div class="ad-wrapper d-none d-lg-inline-block">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 90px; width: 728px;">
                        <script>
                          atOptions = {
                            'key' : 'e2a626177246027d7c16f00a1d96bd16',
                            'format' : 'iframe',
                            'height' : 90,
                            'width' : 728,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/e2a626177246027d7c16f00a1d96bd16/invoke.js"></script>
                    </div>
                </div>

                <!-- Mobile Banner -->
                <div class="ad-wrapper d-inline-block d-lg-none">
                    <span class="ad-label">Publicidade</span>
                    <div style="min-height: 50px; width: 320px;">
                        <script>
                          atOptions = {
                            'key' : 'b2ae2344c933833261eff651b9f8306c',
                            'format' : 'iframe',
                            'height' : 50,
                            'width' : 320,
                            'params' : {}
                          };
                        </script>
                        <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT STICKY AD: Skyscraper (160x300) -->
        <div class="d-none d-xxl-block position-sticky" style="top: 24px; width: 182px; z-index: 5; margin-left: 20px;">
            <div class="ad-wrapper ad-wrapper-vertical" style="width: 182px; margin-bottom: 20px;">
                <span class="ad-label">Publicidade</span>
                <div style="min-height: 300px;">
                    <script>
                      atOptions = {
                        'key' : '4be69db97329b0f4a05323d01543c1a0',
                        'format' : 'iframe',
                        'height' : 300,
                        'width' : 160,
                        'params' : {}
                      };
                    </script>
                    <script src="https://www.highperformanceformat.com/4be69db97329b0f4a05323d01543c1a0/invoke.js"></script>
                </div>
            </div>
            <div class="ad-wrapper ad-wrapper-vertical" style="width: 182px;">
                <span class="ad-label">Publicidade</span>
                <div style="min-height: 300px;">
                    <script>
                      atOptions = {
                        'key' : '4be69db97329b0f4a05323d01543c1a0',
                        'format' : 'iframe',
                        'height' : 300,
                        'width' : 160,
                        'params' : {}
                      };
                    </script>
                    <script src="https://www.highperformanceformat.com/4be69db97329b0f4a05323d01543c1a0/invoke.js"></script>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-white border-top py-5 text-center mt-5">
        <div class="container">
            <hr class="opacity-10 mb-4">
            <p class="fw-bold mb-1">🚖 Simulador Move Brasil</p>
            <p class="text-muted small mb-3">Desenvolvido por <strong>Pedro Falconi</strong></p>
            
            <a href="https://wa.me/5511922058537" target="_blank" rel="noopener" class="btn btn-outline-success rounded-pill px-4 py-2 small d-inline-flex align-items-center gap-2" aria-label="WhatsApp Pedro Falconi">
                <i class="bi bi-whatsapp fs-5"></i> Fale comigo no WhatsApp
            </a>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Injected Car Database -->
    <script>
        const listagemCarros = <?= json_encode($todos_carros) ?>;
    </script>

    <!-- External Separated Script -->
    <script src="../js/app.js"></script>

    <!-- Google Analytics Tag -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LYNB7KTE3B"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-LYNB7KTE3B');
    </script>
</body>
</html>