<?php

$resultado = null;
$erro = null;

function moedaBrParaFloat($valor)
{
    $valor = str_replace('.', '', $valor);
    $valor = str_replace(',', '.', $valor);
    return floatval($valor);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $valorVeiculo = moedaBrParaFloat($_POST['valor_veiculo']);
    $entrada = moedaBrParaFloat($_POST['entrada']);
    $meses = intval($_POST['meses']);
    $perfil = $_POST['perfil'];

    // Taxa Move Brasil
    $taxa = ($perfil == 'F') ? 0.0091 : 0.0099;

    if ($entrada > $valorVeiculo) {
        $erro = 'A entrada não pode ser maior que o valor do veículo.';
    }
    if ($entrada == $valorVeiculo) {
        $erro = 'A entrada não pode ser igual ao valor do veículo.';
    }

    $valorFinanciado = $valorVeiculo - $entrada;

    if (!$erro && $valorFinanciado > 0) {

        /*
             * Cálculo da parcela com juros compostos (Sistema Price):
             *
             *           taxa * (1 + taxa) ^ meses
             * Parcela = -------------------------- * valorFinanciado
             *              (1 + taxa) ^ meses - 1
             *
             * Onde:
             *   - taxa: taxa de juros mensal
             *   - meses: número de parcelas
             *   - valorFinanciado: valor a ser financiado
             */
        $parcela = $valorFinanciado *
            ($taxa * pow(1 + $taxa, $meses)) /
            (pow(1 + $taxa, $meses) - 1);

        $totalPago = $parcela * $meses;
        $juros = $totalPago - $valorFinanciado;

        // Entrada + parcelas
        $totalInvestido = $entrada + $totalPago;

        $resultado = [
            'valor_financiado' => $valorFinanciado,
            'parcela' => $parcela,
            'total_pago' => $totalPago,
            'juros' => $juros,
            'total_investido' => $totalInvestido
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f3f5f7;
        }

        .card-simulador {
            border: none;
            border-radius: 25px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, .08);
        }

        .titulo {
            color: #198754;
            font-weight: 700;
        }

        .resultado {
            background: #eefaf0;
            border-radius: 15px;
        }

        .valor {
            font-size: 1.35rem;
            font-weight: bold;
        }

        .info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 12px;
        }

        .investido {
            color: #0d6efd;
        }

        .parcela {
            color: #198754;
        }
    </style>

</head>

<body>
<a href="#veiculos-autorizados" class="btn-veiculos-flutuante">
    🚘 Ver veículos aceitos
</a>

<style>
.btn-veiculos-flutuante {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;

    background: #198754;
    color: #fff;
    text-decoration: none;

    padding: 14px 20px;
    border-radius: 50px;

    font-weight: 600;
    font-size: 15px;

    box-shadow: 0 4px 15px rgba(0,0,0,.25);
    transition: all .3s ease;
}

.btn-veiculos-flutuante:hover {
    background: #157347;
    color: #fff;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .btn-veiculos-flutuante {
        left: 15px;
        right: 15px;
        bottom: 15px;
        text-align: center;
        font-size: 16px;
    }
}

html {
    scroll-behavior: smooth;
}
</style>
        <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-12">

                <div class="card card-simulador">


                    <div class="card-body p-4">

                        <h2 class="text-center titulo mb-4">
                            🚖 Simulador Move Brasil Táxi e Aplicativos
                        </h2>

                        <div class="info mb-4">
                            Financiamento de até R$ 150.000,00<br>
                            Prazo máximo: 72 meses
                        </div>

                        <form method="POST" id="formSimulador">

                            <div class="mb-3">

                                <label class="form-label">
                                    Valor do veículo
                                </label>

                                <input
                                    type="text"
                                    name="valor_veiculo"
                                    class="form-control dinheiro"
                                    required
                                    placeholder="150.000,00"
                                    value="<?= $_POST['valor_veiculo'] ?? '' ?>">

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Entrada
                                </label>

                                <input
                                    type="text"
                                    name="entrada"
                                    class="form-control dinheiro"
                                    placeholder="0,00"
                                    value="<?= $_POST['entrada'] ?? '0,00' ?>">

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Quantidade de parcelas
                                </label>

                                <div class="d-flex align-items-center gap-3">
                                    <input
                                        type="range"
                                        name="meses"
                                        class="form-range"
                                        min="12"
                                        max="72"
                                        step="12"
                                        id="sliderMeses"
                                        value="<?= $_POST['meses'] ?? 72 ?>"
                                        oninput="document.getElementById('labelMeses').innerText = this.value + ' meses'"
                                    >
                                    <span id="labelMeses" style="min-width:80px; font-weight:bold;">
                                        <?= $_POST['meses'] ?? 72 ?> meses
                                    </span>
                                </div>
                                <script>
                                    // Garante que o label está sincronizado ao carregar a página
                                    document.addEventListener('DOMContentLoaded', function() {
                                        var slider = document.getElementById('sliderMeses');
                                        var label = document.getElementById('labelMeses');
                                        if (slider && label) {
                                            label.innerText = slider.value + ' meses';
                                        }
                                    });
                                </script>

                            </div>

                            <div class="mb-4">

                                <label class="form-label">
                                    Perfil
                                </label>

                                <?php
                                $perfilSelecionado =
                                    $_POST['perfil'] ?? 'M';
                                ?>

                                <select
                                    name="perfil"
                                    class="form-select">

                                    <option
                                        value="M"
                                        <?= $perfilSelecionado == 'M'
                                            ? 'selected'
                                            : '' ?>>

                                        Homem (0,99% a.m.)

                                    </option>

                                    <option
                                        value="F"
                                        <?= $perfilSelecionado == 'F'
                                            ? 'selected'
                                            : '' ?>>

                                        Mulher (0,91% a.m.)

                                    </option>

                                </select>

                            </div>

                            <button
                                class="btn btn-success w-100 py-3">

                                Simular Financiamento

                            </button>
                        </form>

                        <script>
                            atOptions = {
                                'key': 'b2ae2344c933833261eff651b9f8306c',
                                'format': 'iframe',
                                'height': 50,
                                'width': 320,
                                'params': {}
                            };
                        </script>
                        <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>

                        <?php if ($erro): ?>

                            <div class="alert alert-danger mt-4">
                                <?= $erro ?>
                            </div>

                        <?php endif; ?>

                        <?php if ($resultado): ?>

                            <div class="resultado p-4 mt-4">



                                <h4 class="mb-4">
                                    Resultado da Simulação
                                </h4>

                                <div class="row">

                                    <div class="col-md-6 mb-4">

                                        <small>
                                            Valor Financiado
                                        </small>

                                        <div class="valor">
                                            R$
                                            <?= number_format(
                                                $resultado['valor_financiado'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </div>

                                    </div>

                                    <div class="col-md-6 mb-4">

                                        <small>
                                            Parcela Mensal
                                        </small>

                                        <div class="valor parcela">
                                            R$
                                            <?= number_format(
                                                $resultado['parcela'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </div>

                                    </div>

                                    <div class="col-md-6 mb-4">

                                        <small>
                                            Total das Parcelas
                                        </small>

                                        <div class="valor">
                                            R$
                                            <?= number_format(
                                                $resultado['total_pago'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </div>

                                    </div>

                                    <div class="col-md-6 mb-4">

                                        <small>
                                            Total de Juros
                                        </small>

                                        <div class="valor text-danger">
                                            R$
                                            <?= number_format(
                                                $resultado['juros'],
                                                2,
                                                ',',
                                                '.'
                                            ) ?>
                                        </div>

                                    </div>

                                    <div class="col-12">

                                        <div class="alert alert-primary mb-0">

                                            <h5 class="mb-2">
                                                💰 Total Investido
                                            </h5>

                                            <div class="valor investido">

                                                R$
                                                <?= number_format(
                                                    $resultado['total_investido'],
                                                    2,
                                                    ',',
                                                    '.'
                                                ) ?>

                                            </div>

                                            <small>
                                                Entrada + Total das Parcelas
                                            </small>

                                        </div>
                                        <div class="alert alert-info mt-4">
                                            <strong>Fórmula utilizada para o cálculo da parcela:</strong><br>
                                            <span style="font-family:monospace; font-size:1.1em;">
                                                Parcela = Valor Financiado × <br>
                                                &nbsp;&nbsp;&nbsp;&nbsp;[ taxa × (1 + taxa)<sup>meses</sup> ] / [ (1 + taxa)<sup>meses</sup> - 1 ]
                                            </span><br>
                                            <small>
                                                Onde:<br>
                                                &nbsp;&nbsp;- <b>taxa</b>: taxa de juros mensal<br>
                                                &nbsp;&nbsp;- <b>meses</b>: número de parcelas<br>
                                                &nbsp;&nbsp;- <b>Valor Financiado</b>: valor a ser financiado<br>
                                            </small>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <script>
                                atOptions = {
                                    'key': 'b2ae2344c933833261eff651b9f8306c',
                                    'format': 'iframe',
                                    'height': 50,
                                    'width': 320,
                                    'params': {}
                                };
                            </script>
                            <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>


    </div>
    <div class="container my-5">
   <script>
                    atOptions = {
                        'key': 'b2ae2344c933833261eff651b9f8306c',
                        'format': 'iframe',
                        'height': 50,
                        'width': 320,
                        'params': {}
                    };
                </script>
                <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="bg-primary text-white p-4">
                <h2 class="fw-bold mb-2">
                    🚖 Move Brasil – Táxi e Aplicativos
                </h2>
                <p class="mb-0">
                    Programa do Governo Federal para financiamento de veículos novos
                    com condições especiais para taxistas e motoristas de aplicativo.
                </p>
            </div>

            <div class="card-body p-4">

                <div class="alert alert-success border-0">
                    <h5 class="fw-bold mb-2">✅ Principais Benefícios</h5>
                    <ul class="mb-0">
                        <li>Financiamento com juros reduzidos.</li>
                        <li>Prazo de até <strong>72 meses</strong> para pagamento.</li>
                        <li>Possibilidade de até <strong>6 meses de carência</strong>.</li>
                        <li>Financiamento de veículos sustentáveis.</li>
                        <li>Possibilidade de entrada reduzida ou até zero, conforme análise do banco.</li>
                    </ul>
                </div>

                <div class="row g-4">

                    <div class="col-lg-6">
                        <div class="card h-100 border-success">
                            <div class="card-header bg-success text-white fw-bold">
                                🚗 Motoristas de Aplicativo
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>Cadastro ativo há pelo menos <strong>12 meses</strong>.</li>
                                    <li>Mínimo de <strong>100 corridas realizadas</strong> nos últimos 12 meses.</li>
                                    <li>As corridas devem ser na mesma plataforma cadastrada no programa.</li>
                                    <li>Necessário realizar cadastro pelo portal GOV.BR.</li>
                                    <li>Sujeito à análise de crédito da instituição financeira.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card h-100 border-warning">
                            <div class="card-header bg-warning text-dark fw-bold">
                                🚕 Taxistas
                            </div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    <li>Licença, autorização ou alvará ativo.</li>
                                    <li>Regularidade fiscal e cadastral.</li>
                                    <li>Validação realizada pelos órgãos competentes.</li>
                                    <li>Cooperativas de táxi também podem participar.</li>
                                    <li>Sujeito à análise de crédito do banco.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

                <hr class="my-4">

                <div class="card border-primary">
                    <div class="card-header bg-primary text-white fw-bold">
                        🚘 Regras dos Veículos
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Veículo obrigatoriamente <strong>0 km</strong>.</li>
                            <li>Preço da nota fiscal de até <strong>R$ 150.000,00</strong>.</li>
                            <li>Montadora participante do Programa Mover.</li>
                            <li>Modelos elegíveis:
                                <ul>
                                    <li>Flex</li>
                                    <li>Etanol</li>
                                    <li>Híbrido Flex</li>
                                    <li>Elétrico</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="row g-4 mt-1">

                    <div class="col-md-4">
                        <div class="card text-center border-info h-100">
                            <div class="card-body">
                                <h1 class="text-info fw-bold">1</h1>
                                <h6 class="fw-bold">Cadastro</h6>
                                <p class="small text-muted mb-0">
                                    Faça o cadastro no portal GOV.BR Move Brasil.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-center border-warning h-100">
                            <div class="card-body">
                                <h1 class="text-warning fw-bold">2</h1>
                                <h6 class="fw-bold">Validação</h6>
                                <p class="small text-muted mb-0">
                                    Aprovação em até 5 dias úteis.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card text-center border-success h-100">
                            <div class="card-body">
                                <h1 class="text-success fw-bold">3</h1>
                                <h6 class="fw-bold">Financiamento</h6>
                                <p class="small text-muted mb-0">
                                    Procure uma concessionária e banco participante.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="alert alert-primary mt-4 mb-0">
                    <strong>ℹ️ Observação:</strong><br>
                    O limite de R$ 150 mil é referente ao valor do veículo na nota fiscal.
                    A aprovação final depende da análise de crédito da instituição financeira.
                </div>

            </div>
        </div>
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


    <!-- jQuery (CDN) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- jQuery Mask Plugin (CDN) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <script>
        $('.dinheiro').mask(
            '000.000.000.000.000,00', {
                reverse: true
            }
        );

        $('#formSimulador').on(
            'submit',
            function(e) {

                function moedaParaNumero(valor) {

                    valor = valor.replaceAll('.', '');
                    valor = valor.replace(',', '.');

                    return parseFloat(valor) || 0;
                }

                let valorVeiculo =
                    moedaParaNumero(
                        $('[name="valor_veiculo"]').val()
                    );

                let entrada =
                    moedaParaNumero(
                        $('[name="entrada"]').val()
                    );

                if (entrada > valorVeiculo) {

                    alert(
                        'A entrada não pode ser maior que o valor do veículo.'
                    );

                    e.preventDefault();
                }
            }
        );
    </script>

    <div class="container mb-2"  id="veiculos-autorizados">
        <div class="text-center mb-4">
            <h3 class="fw-bold">
                🚘 Carros autorizados no Move Brasil
            </h3>

            <p class="text-muted mb-0">
                Modelos que já possuem valor de nota fiscal confirmado dentro das regras do programa.
            </p>
        </div>

        <div class="alert alert-warning border-0 shadow-sm">
            <div class="d-flex">
                <div class="me-3 fs-3">
                    ⚠️
                </div>

                <div>
                    <h6 class="fw-bold mb-2">
                        Atenção
                    </h6>

                    <p class="mb-2">
                        Esta lista é atualizada constantemente conforme novas informações
                        são disponibilizadas pelas concessionárias e montadoras.
                    </p>

                    <ul class="mb-0">
                        <li>
                            Alguns veículos podem ainda não aparecer na relação.
                        </li>
                        <li>
                            Isso <strong>não significa que o modelo esteja fora do programa</strong>.
                        </li>
                        <li>
                            Em muitos casos, ainda não recebemos o valor oficial da nota fiscal.
                        </li>
                        <li>
                            Veículos com campanhas promocionais ou descontos temporários também podem não constar inicialmente.
                        </li>
                        <li>
                            A elegibilidade final depende da análise da nota fiscal emitida pela concessionária.
                        </li>
                    </ul>
                </div>
            </div>
        </div>


        <?php
        $carros_aplicativos = [];
        $carros_taxistas_condutaxi = [];
        $carros_taxistas_isencao = [];
        $arquivo_carros = __DIR__ . '/carros_autorizados.txt';
        if (file_exists($arquivo_carros)) {
            $linhas = file($arquivo_carros);
            foreach ($linhas as $linha) {
                $linha = trim($linha);
                if ($linha === '' || $linha[0] === '#') continue;
                list($cat, $nome, $valor) = explode(';', $linha);
                $item = [
                    'nome' => $nome,
                    'valor' => $valor
                ];
                if ($cat === 'aplicativos') $carros_aplicativos[] = $item;
                if ($cat === 'taxistas_condutaxi') $carros_taxistas_condutaxi[] = $item;
                if ($cat === 'taxistas_isencao') $carros_taxistas_isencao[] = $item;
            }
        }
        ?>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white text-center">Carros para aplicativos<br><small>(sem isenção)</small></div>
                    <ul class="list-group list-group-flush">
                        <?php if (count($carros_aplicativos)): foreach ($carros_aplicativos as $carro): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($carro['nome']) ?>
                                    <?php if ($carro['valor']): ?>
                                        <span class="text-muted float-end">R$ <?= number_format($carro['valor'], 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach;
                        else: ?>
                            <li class="list-group-item text-muted">Nenhum veículo cadastrado</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <script>
                    atOptions = {
                        'key': 'b2ae2344c933833261eff651b9f8306c',
                        'format': 'iframe',
                        'height': 50,
                        'width': 320,
                        'params': {}
                    };
                </script>
                <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white text-center">Carros para taxistas<br><small>(somente Condutaxi)</small></div>
                    <ul class="list-group list-group-flush">
                        <?php if (count($carros_taxistas_condutaxi)): foreach ($carros_taxistas_condutaxi as $carro): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($carro['nome']) ?>
                                    <?php if ($carro['valor']): ?>
                                        <span class="text-muted float-end">R$ <?= number_format($carro['valor'], 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach;
                        else: ?>
                            <li class="list-group-item text-muted">Nenhum veículo cadastrado</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <script>
                    atOptions = {
                        'key': 'b2ae2344c933833261eff651b9f8306c',
                        'format': 'iframe',
                        'height': 50,
                        'width': 320,
                        'params': {}
                    };
                </script>
                <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark text-center">Carros para taxistas<br><small>(isenção total)</small></div>
                    <ul class="list-group list-group-flush">
                        <?php if (count($carros_taxistas_isencao)): foreach ($carros_taxistas_isencao as $carro): ?>
                                <li class="list-group-item">
                                    <?= htmlspecialchars($carro['nome']) ?>
                                    <?php if ($carro['valor']): ?>
                                        <span class="text-muted float-end">R$ <?= number_format($carro['valor'], 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach;
                        else: ?>
                            <li class="list-group-item text-muted">Nenhum veículo cadastrado</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <script>
                    atOptions = {
                        'key': 'b2ae2344c933833261eff651b9f8306c',
                        'format': 'iframe',
                        'height': 50,
                        'width': 320,
                        'params': {}
                    };
                </script>
                <script src="https://www.highperformanceformat.com/b2ae2344c933833261eff651b9f8306c/invoke.js"></script>
            </div>
        </div>
    </div>

    <script>
        atOptions = {
            'key': '4be69db97329b0f4a05323d01543c1a0',
            'format': 'iframe',
            'height': 300,
            'width': 160,
            'params': {}
        };
    </script>
    <script src="https://www.highperformanceformat.com/4be69db97329b0f4a05323d01543c1a0/invoke.js"></script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-LYNB7KTE3B"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-LYNB7KTE3B');
    </script>

        <div class="text-center mt-4 mb-5 ">
        <hr>
        <small class="text-muted">
            🚖 Simulador Move Brasil<br>
            Desenvolvido por <strong>Pedro Falconi</strong><br>
            <a href="https://wa.me/5511922058537" target="_blank" rel="noopener" class="d-inline-block mt-2" aria-label="WhatsApp Pedro Falconi">
                <img src="https://img.icons8.com/color/48/000000/whatsapp--v1.png" alt="WhatsApp" style="width:24px;height:24px;vertical-align:middle;"> Fale comigo no WhatsApp
            </a>
        </small>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</script>
</body>

</html>