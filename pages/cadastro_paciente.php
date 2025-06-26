<?php
require_once '../db/conn.php';
require_once '../moders/paciente.php';

$db = (new Database())->conectar();
$paciente = new Paciente($db);

$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $paciente->setNome($_POST['nome'] ?? '');
        $paciente->setCpf($_POST['cpf'] ?? '');
        $paciente->setDataNascimento($_POST['data_nascimento'] ?? '');
        $paciente->setSexo($_POST['sexo'] ?? '');
        $paciente->setTelefone($_POST['telefone'] ?? '');
        $paciente->setEmail($_POST['email'] ?? '');

        $paciente->setConvenio($_POST['convenio'] ?? '');
        $paciente->setNumeroCarteira($_POST['numero_carteira'] ?? '');
        $paciente->setValidadeCarteira($_POST['validade_carteira'] ?? '');

        if ($paciente->inserir()) {
            $msg = "Paciente cadastrado com sucesso!";
        } else {
            $erro = "Erro ao cadastrar paciente.";
        }

    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Cadastro de Paciente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <?php include '../moders/top.php'; ?>

    <div class="container my-4">
        <h2>Cadastro de Paciente</h2>

        <?php if ($msg): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <fieldset class="border p-3 mb-3 rounded">
                <legend class="w-auto px-2">Dados Pessoais</legend>

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome completo *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required minlength="3" />
                </div>

                <div class="mb-3">
                    <label for="cpf" class="form-label">CPF *</label>
                    <input type="text" class="form-control" id="cpf" name="cpf" required pattern="\d{11}" title="Apenas números, 11 dígitos" />
                </div>

                <div class="mb-3">
                    <label for="data_nascimento" class="form-label">Data de nascimento *</label>
                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento" required />
                </div>

                <div class="mb-3">
                    <label for="sexo" class="form-label">Sexo *</label>
                    <select class="form-select" id="sexo" name="sexo" required>
                        <option value="" selected>Selecione</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" />
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" />
                </div>
            </fieldset>

            <fieldset class="border p-3 rounded">
                <legend class="w-auto px-2">Dados do Convênio</legend>

                <div class="mb-3">
                    <label for="convenio" class="form-label">Convênio</label>
                    <input type="text" class="form-control" id="convenio" name="convenio" />
                </div>

                <div class="mb-3">
                    <label for="numero_carteira" class="form-label">Número da Carteira</label>
                    <input type="text" class="form-control" id="numero_carteira" name="numero_carteira" />
                </div>

                <div class="mb-3">
                    <label for="validade_carteira" class="form-label">Validade da Carteira</label>
                    <input type="date" class="form-control" id="validade_carteira" name="validade_carteira" />
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary mt-3">Cadastrar</button>
        </form>
    </div>

    <?php include '../moders/bot.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
