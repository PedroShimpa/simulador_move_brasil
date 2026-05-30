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

    <title>Simulador Move Brasil</title>

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

    <div class="container py-5">

        <div class="row justify-content-center">

            <div class="col-lg-8">

                <div class="card card-simulador">
                    <script>
                        atOptions = {
                            'key': 'e2a626177246027d7c16f00a1d96bd16',
                            'format': 'iframe',
                            'height': 90,
                            'width': 728,
                            'params': {}
                        };
                    </script>
                    <script src="https://www.highperformanceformat.com/e2a626177246027d7c16f00a1d96bd16/invoke.js"></script>
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

                                <select
                                    name="meses"
                                    class="form-select">

                                    <?php

                                    $mesSelecionado =
                                        $_POST['meses'] ?? 72;

                                    for ($i = 12; $i <= 72; $i += 12) {

                                        $selected =
                                            ($mesSelecionado == $i)
                                            ? 'selected'
                                            : '';

                                        echo "
                                    <option
                                        value='{$i}'
                                        {$selected}>
                                        {$i} meses
                                    </option>";
                                    }

                                    ?>

                                </select>

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
                                        <div class="alert alert-info mb-4">
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

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>
        <script>
            atOptions = {
                'key': 'e2a626177246027d7c16f00a1d96bd16',
                'format': 'iframe',
                'height': 90,
                'width': 728,
                'params': {}
            };
        </script>
        <script src="https://www.highperformanceformat.com/e2a626177246027d7c16f00a1d96bd16/invoke.js"></script>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-mask/1.14.16/jquery.mask.min.js"></script>

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
    <div class="text-center mt-4 mb-3">
        <hr>
        <small class="text-muted">
            🚖 Simulador Move Brasil<br>
            Desenvolvido por <strong>Pedro Falconi</strong>
        </small>
    </div>
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

</body>

</html>