<?php
// adicionar.php
// Formulário para adicionar novo veículo à lista de carros autorizados

$arquivo = 'carros_autorizados.txt';
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = $_POST['categoria'] ?? '';
    $nome = trim($_POST['nome'] ?? '');
    $valor = trim($_POST['valor'] ?? '');

    if ($categoria && $nome && $valor) {
        $linha = "$categoria;$nome;$valor\n";
        file_put_contents($arquivo, $linha, FILE_APPEND);
        $mensagem = 'Veículo adicionado com sucesso!';
    } else {
        $mensagem = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Veículo Autorizado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">Adicionar Veículo Autorizado</h3>
                    <?php if ($mensagem): ?>
                        <div class="alert alert-info"><?= $mensagem ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-select" required>
                                <option value="">Selecione</option>
                                <option value="aplicativos">Carros para aplicativos (sem isenção)</option>
                                <option value="taxistas_condutaxi">Carros para taxistas (somente Condutaxi)</option>
                                <option value="taxistas_isencao">Carros para taxistas (isenção total)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome do carro</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Valor (apenas número, ex: 70000)</label>
                            <input type="number" name="valor" class="form-control" required>
                        </div>
                        <button class="btn btn-success w-100">Adicionar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
