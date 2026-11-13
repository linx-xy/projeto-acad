<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');
require_once 'php/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_treino'])) {
    $grupo_muscular = trim($_POST['grupo_muscular'] ?? '');
    $exercicio      = trim($_POST['exercicio'] ?? '');
    $series         = (int)($_POST['series'] ?? 0);
    $repeticoes     = (int)($_POST['repeticoes'] ?? 0);
    $carga_kg       = (float)($_POST['carga_kg'] ?? 0);
    $data_treino    = $_POST['data_treino'] ?? date('Y-m-d');

    if (!empty($grupo_muscular) && !empty($exercicio) && $series > 0) {
        $stmt = $pdo->prepare("INSERT INTO treinos (usuario_id, grupo_muscular, exercicio, series, repeticoes, carga_kg, data_treino)
                               VALUES (:usuario_id, :grupo_muscular, :exercicio, :series, :repeticoes, :carga_kg, :data_treino)");
        $stmt->execute([
            ':usuario_id'     => $usuario_id,
            ':grupo_muscular' => $grupo_muscular,
            ':exercicio'      => $exercicio,
            ':series'         => $series,
            ':repeticoes'     => $repeticoes,
            ':carga_kg'       => $carga_kg,
            ':data_treino'    => $data_treino
        ]);
        header("Location: painel.php?salvo=1");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_treino'])) {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("DELETE FROM treinos WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->execute([':id' => $id, ':usuario_id' => $usuario_id]);
    header("Location: painel.php?excluido=1");
    exit;
}

if (isset($_GET['salvo'])) {
    $mensagem = "Treino registrado com sucesso!";
} elseif (isset($_GET['atualizado'])) {
    $mensagem = "Treino atualizado com sucesso!";
} elseif (isset($_GET['excluido'])) {
    $mensagem = "Treino excluído.";
}

$filtroGrupo = trim($_GET['grupo'] ?? '');
$filtroDe    = $_GET['de'] ?? '';
$filtroAte   = $_GET['ate'] ?? '';

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtroDe) !== 1) {
    $filtroDe = '';
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtroAte) !== 1) {
    $filtroAte = '';
}

$filtrando = ($filtroGrupo !== '' || $filtroDe !== '' || $filtroAte !== '');

$sql = "SELECT t.*, u.nome AS nome_usuario
        FROM treinos t
        INNER JOIN usuarios u ON t.usuario_id = u.id
        WHERE t.usuario_id = :usuario_id";
$params = [':usuario_id' => $usuario_id];

if ($filtroGrupo !== '') {
    $sql .= " AND t.grupo_muscular = :grupo";
    $params[':grupo'] = $filtroGrupo;
}
if ($filtroDe !== '') {
    $sql .= " AND t.data_treino >= :de";
    $params[':de'] = $filtroDe;
}
if ($filtroAte !== '') {
    $sql .= " AND t.data_treino <= :ate";
    $params[':ate'] = $filtroAte;
}
$sql .= " ORDER BY t.data_treino DESC, t.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$treinos = $stmt->fetchAll();
$totalTreinos = count($treinos);

$stmt = $pdo->prepare("SELECT DISTINCT grupo_muscular FROM treinos WHERE usuario_id = :usuario_id ORDER BY grupo_muscular");
$stmt->execute([':usuario_id' => $usuario_id]);
$grupos = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT COUNT(*) AS total, MAX(carga_kg) AS maior, MAX(data_treino) AS ultimo FROM treinos WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$resumo = $stmt->fetch();

$totalGeral   = (int)$resumo['total'];
$maiorCarga   = $resumo['maior'] ?? 0;
$ultimoTreino = $resumo['ultimo'] ? date('d/m/Y', strtotime($resumo['ultimo'])) : '—';

$hoje   = new DateTime('today');
$inicio = (clone $hoje)->modify('monday this week')->modify('-3 weeks');

$stmt = $pdo->prepare("SELECT DISTINCT data_treino FROM treinos WHERE usuario_id = :usuario_id AND data_treino >= :inicio");
$stmt->execute([':usuario_id' => $usuario_id, ':inicio' => $inicio->format('Y-m-d')]);
$diasComTreino = array_flip($stmt->fetchAll(PDO::FETCH_COLUMN));

$dias = [];
$diasTreinados = 0;
for ($i = 0; $i < 28; $i++) {
    $d = (clone $inicio)->modify("+$i days");
    $chave  = $d->format('Y-m-d');
    $futuro = $d > $hoje;
    $feito  = !$futuro && isset($diasComTreino[$chave]);
    if ($feito) {
        $diasTreinados++;
    }
    $dias[] = [
        'numero' => $d->format('j'),
        'rotulo' => $d->format('d/m/Y'),
        'feito'  => $feito,
        'hoje'   => $d == $hoje,
        'futuro' => $futuro,
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Meus treinos | Projeto Acad</title>
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
        <div class="pagina__topo">
        <div class="pagina__titulo">
            <h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>.</h1>
            <?php if ($totalGeral === 0): ?>
                <p>Você ainda não registrou nenhum treino. Preencha a ficha abaixo para começar.</p>
            <?php else: ?>
                <p>
                    <strong><?= $totalGeral ?></strong> <?= $totalGeral === 1 ? 'treino registrado' : 'treinos registrados' ?>.
                    Sua maior carga é <strong><?= htmlspecialchars((string)$maiorCarga) ?> kg</strong>
                    e o último treino foi em <strong><?= htmlspecialchars($ultimoTreino) ?></strong>.
                </p>
            <?php endif; ?>
        </div>

        <aside class="presenca" aria-labelledby="titulo-presenca">
            <h2 id="titulo-presenca">Presença</h2>
            <p class="presenca__sub">Últimas 4 semanas: <strong><?= $diasTreinados ?></strong> <?= $diasTreinados === 1 ? 'dia treinado' : 'dias treinados' ?></p>
            <div class="presenca__semana" aria-hidden="true">
                <span>S</span><span>T</span><span>Q</span><span>Q</span><span>S</span><span>S</span><span>D</span>
            </div>
            <ol class="presenca__grade">
                <?php foreach ($dias as $dia): ?>
                <li class="dia<?= $dia['feito'] ? ' dia--feito' : '' ?><?= $dia['hoje'] ? ' dia--hoje' : '' ?><?= $dia['futuro'] ? ' dia--futuro' : '' ?>"
                    title="<?= htmlspecialchars($dia['rotulo']) ?>"<?= $dia['feito'] ? ' aria-label="' . htmlspecialchars($dia['rotulo']) . ': treinou"' : '' ?>><?= $dia['numero'] ?></li>
                <?php endforeach; ?>
            </ol>
        </aside>
        </div>

        <?php if ($mensagem): ?>
            <p class="alerta alerta--sucesso" role="status"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <section class="secao" aria-labelledby="titulo-novo">
            <div class="secao__cabecalho">
                <h2 id="titulo-novo">Registrar treino</h2>
            </div>

            <form class="form form--treino" action="painel.php" method="POST">
                <input type="hidden" name="cadastrar_treino" value="1">

                <div class="campo">
                    <label for="grupo_muscular">Grupo muscular</label>
                    <input type="text" id="grupo_muscular" name="grupo_muscular" placeholder="Peito" maxlength="50" required>
                </div>

                <div class="campo campo--largo">
                    <label for="exercicio">Exercício</label>
                    <input type="text" id="exercicio" name="exercicio" placeholder="Supino reto" maxlength="100" required>
                </div>

                <div class="campo">
                    <label for="series">Séries</label>
                    <input type="number" id="series" name="series" min="1" required>
                </div>

                <div class="campo">
                    <label for="repeticoes">Repetições</label>
                    <input type="number" id="repeticoes" name="repeticoes" min="1" required>
                </div>

                <div class="campo">
                    <label for="carga_kg">Carga (kg)</label>
                    <input type="number" id="carga_kg" name="carga_kg" step="0.5" min="0">
                </div>

                <div class="campo">
                    <label for="data_treino">Data</label>
                    <input type="date" id="data_treino" name="data_treino" value="<?= date('Y-m-d') ?>" required>
                </div>

                <button class="btn" type="submit">Salvar treino</button>
            </form>
        </section>

        <section class="secao" aria-labelledby="titulo-lista">
            <div class="secao__cabecalho">
                <h2 id="titulo-lista">Meus treinos</h2>
                <span><?= $totalTreinos ?> <?= $totalTreinos === 1 ? 'registro' : 'registros' ?></span>
            </div>

            <form class="filtros" action="painel.php" method="GET">
                <div class="campo">
                    <label for="filtro_grupo">Grupo muscular</label>
                    <select id="filtro_grupo" name="grupo">
                        <option value="">Todos</option>
                        <?php foreach ($grupos as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>"<?= $g === $filtroGrupo ? ' selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo">
                    <label for="filtro_de">De</label>
                    <input type="date" id="filtro_de" name="de" value="<?= htmlspecialchars($filtroDe) ?>">
                </div>

                <div class="campo">
                    <label for="filtro_ate">Até</label>
                    <input type="date" id="filtro_ate" name="ate" value="<?= htmlspecialchars($filtroAte) ?>">
                </div>

                <button class="btn" type="submit">Filtrar</button>

                <?php if ($filtrando): ?>
                    <a class="btn btn--contorno" href="painel.php">Limpar filtros</a>
                <?php endif; ?>
            </form>

            <?php if ($totalTreinos === 0): ?>
                <p class="vazio"><?= $filtrando ? 'Nenhum treino encontrado com esses filtros.' : 'Nenhum treino por aqui ainda.' ?></p>
            <?php else: ?>
                <div class="tabela-wrap">
                    <table class="tabela">
                        <thead>
                            <tr>
                                <th scope="col">Usuário</th>
                                <th scope="col">Data</th>
                                <th scope="col">Grupo</th>
                                <th scope="col">Exercício</th>
                                <th scope="col">Séries × reps</th>
                                <th scope="col" class="num">Carga</th>
                                <th scope="col">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($treinos as $t): ?>
                            <?php $corGrupo = abs(crc32(mb_strtolower($t['grupo_muscular']))) % 4; ?>
                            <tr>
                                <td data-label="Usuário"><?= htmlspecialchars($t['nome_usuario']) ?></td>
                                <td data-label="Data"><?= date('d/m/Y', strtotime($t['data_treino'])) ?></td>
                                <td data-label="Grupo"><span class="selo selo--<?= $corGrupo ?>"><?= htmlspecialchars($t['grupo_muscular']) ?></span></td>
                                <td data-label="Exercício"><?= htmlspecialchars($t['exercicio']) ?></td>
                                <td data-label="Séries × reps"><?= (int)$t['series'] ?> × <?= (int)$t['repeticoes'] ?></td>
                                <td data-label="Carga" class="num carga"><?= htmlspecialchars((string)$t['carga_kg']) ?> <small>kg</small></td>
                                <td data-label="Ações">
                                    <div class="acoes">
                                        <a class="btn btn--contorno btn--pequeno" href="editar.php?id=<?= (int)$t['id'] ?>">Editar</a>
                                        <form action="painel.php" method="POST" onsubmit="return confirm('Excluir este treino?');">
                                            <input type="hidden" name="excluir_treino" value="1">
                                            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                            <button class="btn btn--perigo btn--pequeno" type="submit">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
