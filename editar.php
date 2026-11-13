<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once 'php/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM treinos WHERE id = :id AND usuario_id = :usuario_id");
$stmt->execute([':id' => $id, ':usuario_id' => $usuario_id]);
$treino = $stmt->fetch();

if (!$treino) {
    header("Location: painel.php");
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_treino'])) {
    $grupo_muscular = trim($_POST['grupo_muscular'] ?? '');
    $exercicio      = trim($_POST['exercicio'] ?? '');
    $series         = (int)($_POST['series'] ?? 0);
    $repeticoes     = (int)($_POST['repeticoes'] ?? 0);
    $carga_kg       = (float)($_POST['carga_kg'] ?? 0);
    $data_treino    = $_POST['data_treino'] ?? date('Y-m-d');

    if (!empty($grupo_muscular) && !empty($exercicio) && $series > 0) {
        $stmt = $pdo->prepare("UPDATE treinos
                               SET grupo_muscular = :grupo_muscular, exercicio = :exercicio, series = :series,
                                   repeticoes = :repeticoes, carga_kg = :carga_kg, data_treino = :data_treino
                               WHERE id = :id AND usuario_id = :usuario_id");
        $stmt->execute([
            ':grupo_muscular' => $grupo_muscular,
            ':exercicio'      => $exercicio,
            ':series'         => $series,
            ':repeticoes'     => $repeticoes,
            ':carga_kg'       => $carga_kg,
            ':data_treino'    => $data_treino,
            ':id'             => $id,
            ':usuario_id'     => $usuario_id
        ]);
        header("Location: painel.php?atualizado=1");
        exit;
    }

    $mensagem = "Preencha o grupo muscular, o exercício e as séries.";
    $treino['grupo_muscular'] = $grupo_muscular;
    $treino['exercicio']      = $exercicio;
    $treino['series']         = $series;
    $treino['repeticoes']     = $repeticoes;
    $treino['carga_kg']       = $carga_kg;
    $treino['data_treino']    = $data_treino;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar treino | Projeto Acad</title>
    <link rel="icon" type="image/svg+xml" href="img/logo-icone.svg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topo">
        <div class="topo__conteudo">
            <a class="logo" href="painel.php"><img src="img/logo.svg" alt="Acad" width="150" height="48"></a>
            <a class="btn btn--secundario btn--pequeno" href="logout.php">Sair</a>
        </div>
    </header>

    <main class="pagina">
        <div class="pagina__titulo">
            <h1>Editar treino</h1>
            <p>Corrija os dados e salve. Nada muda até você confirmar.</p>
        </div>

        <?php if ($mensagem): ?>
            <p class="alerta alerta--erro" role="alert"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <section class="secao" aria-labelledby="titulo-editar">
            <div class="secao__cabecalho">
                <h2 id="titulo-editar">Dados do treino</h2>
            </div>

            <form class="form form--treino" action="editar.php?id=<?= (int)$treino['id'] ?>" method="POST">
                <input type="hidden" name="atualizar_treino" value="1">
                <input type="hidden" name="id" value="<?= (int)$treino['id'] ?>">

                <div class="campo">
                    <label for="grupo_muscular">Grupo muscular</label>
                    <input type="text" id="grupo_muscular" name="grupo_muscular" value="<?= htmlspecialchars($treino['grupo_muscular']) ?>" maxlength="50" required>
                </div>

                <div class="campo campo--largo">
                    <label for="exercicio">Exercício</label>
                    <input type="text" id="exercicio" name="exercicio" value="<?= htmlspecialchars($treino['exercicio']) ?>" maxlength="100" required>
                </div>

                <div class="campo">
                    <label for="series">Séries</label>
                    <input type="number" id="series" name="series" value="<?= (int)$treino['series'] ?>" min="1" required>
                </div>

                <div class="campo">
                    <label for="repeticoes">Repetições</label>
                    <input type="number" id="repeticoes" name="repeticoes" value="<?= (int)$treino['repeticoes'] ?>" min="1" required>
                </div>

                <div class="campo">
                    <label for="carga_kg">Carga (kg)</label>
                    <input type="number" id="carga_kg" name="carga_kg" value="<?= htmlspecialchars((string)$treino['carga_kg']) ?>" step="0.5" min="0">
                </div>

                <div class="campo">
                    <label for="data_treino">Data</label>
                    <input type="date" id="data_treino" name="data_treino" value="<?= htmlspecialchars($treino['data_treino']) ?>" required>
                </div>

                <button class="btn" type="submit">Salvar alterações</button>
                <a class="btn btn--contorno" href="painel.php">Cancelar</a>
            </form>
        </section>
    </main>
</body>
</html>
