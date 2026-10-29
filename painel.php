<?php
session_start();
require_once 'php/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensagem = '';

// Inserir Treino
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
            ':exercicio'       => $exercicio,
            ':series'          => $series,
            ':repeticoes'      => $repeticoes,
            ':carga_kg'        => $carga_kg,
            ':data_treino'     => $data_treino
        ]);
        $mensagem = "Treino registado com sucesso!";
    }
}

// Listagem com INNER JOIN
$stmt = $pdo->prepare("SELECT t.*, u.nome AS nome_usuario 
                       FROM treinos t 
                       INNER JOIN usuarios u ON t.usuario_id = u.id 
                       WHERE t.usuario_id = :usuario_id 
                       ORDER BY t.data_treino DESC");
$stmt->execute([':usuario_id' => $usuario_id]);
$treinos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Projeto Acad - Painel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>! 🏋️‍♂️</h1>
        <p><a href="logout.php">Sair (Logout)</a></p>

        <?php if ($mensagem): ?>
            <p class="alerta"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <h2>Registar Treino</h2>
        <form action="painel.php" method="POST">
            <input type="hidden" name="cadastrar_treino" value="1">
            <label>Grupo Muscular:</label>
            <input type="text" name="grupo_muscular" required>
            <label>Exercício:</label>
            <input type="text" name="exercicio" required>
            <label>Séries:</label>
            <input type="number" name="series" required>
            <label>Repetições:</label>
            <input type="number" name="repeticoes" required>
            <label>Carga (kg):</label>
            <input type="number" step="0.5" name="carga_kg">
            <label>Data:</label>
            <input type="date" name="data_treino" value="<?= date('Y-m-d') ?>" required>
            <button type="submit">Salvar Treino</button>
        </form>

        <hr>

        <h2>Meus Treinos (INNER JOIN)</h2>
        <table border="1" cellpadding="6">
            <tr>
                <th>Utilizador</th>
                <th>Data</th>
                <th>Grupo</th>
                <th>Exercício</th>
                <th>SériesxReps</th>
                <th>Carga</th>
            </tr>
            <?php foreach ($treinos as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['nome_usuario']) ?></td>
                <td><?= date('d/m/Y', strtotime($t['data_treino'])) ?></td>
                <td><?= htmlspecialchars($t['grupo_muscular']) ?></td>
                <td><?= htmlspecialchars($t['exercicio']) ?></td>
                <td><?= $t['series'] ?>x<?= $t['repeticoes'] ?></td>
                <td><?= $t['carga_kg'] ?> kg</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>