<?php
require_once '../db/conn.php';
require_once '../moders/medico.php';

$db = (new Database())->conectar();
$medico = new Medico($db);

$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $medico->setNome($_POST['nome'] ?? '');
        $medico->setCrm($_POST['crm'] ?? '');
        $medico->setEspecialidade($_POST['especialidade'] ?? '');

        if ($medico->inserir()) {
            $msg = "Médico cadastrado com sucesso!";
        } else {
            $erro = "Erro ao cadastrar médico.";
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
    <title>Cadastro de Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <?php include '../moders/top.php'; ?>

    <div class="container my-4">
        <h2>Cadastro de Médico</h2>

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
            <fieldset class="border p-3 rounded mb-3">
                <legend class="w-auto px-2">Dados do Médico</legend>

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome completo *</label>
                    <input type="text" class="form-control" id="nome" name="nome" required minlength="10" />
                </div>

                <div class="mb-3">
                    <label for="crm" class="form-label">CRM *</label>
                    <input type="text" class="form-control" id="crm" name="crm" required pattern="\d{6}" title="Apenas números, 6 dígitos" />
                </div>

                <div class="mb-3">
                    <label for="especialidade" class="form-label">Especialidade</label>
                    <input type="text" class="form-control" id="especialidade" name="especialidade" />
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">Cadastrar</button>
        </form>
    </div>

    <?php include '../moders/bot.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
