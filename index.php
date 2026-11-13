<?php
session_start();
require_once 'php/conexao.php';

$mensagem = '';
$tipoMensagem = 'erro';

if (isset($_GET['cadastrado'])) {
    $mensagem = "Cadastro realizado com sucesso! Faça login.";
    $tipoMensagem = 'sucesso';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!empty($email) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            header("Location: painel.php");
            exit;
        } else {
            $mensagem = "E-mail ou senha incorretos!";
        }
    } else {
        $mensagem = "Preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar | Projeto Acad</title>
    <link rel="icon" type="image/svg+xml" href="img/logo-icone.svg">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="entrada">
        <header>
            <a class="logo" href="index.php"><img src="img/logo-claro.svg" alt="Acad" width="150" height="48"></a>
        </header>

        <main class="entrada__corpo">
            <section class="entrada__texto">
                <h1>Anote o treino. Veja a carga subir.</h1>
                <p>Cada série, repetição e quilo registrados em um só lugar, organizados por data.</p>
                <img class="barra" src="img/barra.svg" alt="" width="560" height="140">
                <div class="amostra" aria-hidden="true">
                    <div><span>Supino reto</span><strong>4 × 12 &nbsp; 40 kg</strong></div>
                    <div><span>Agachamento livre</span><strong>4 × 10 &nbsp; 70 kg</strong></div>
                    <div><span>Remada curvada</span><strong>3 × 12 &nbsp; 45 kg</strong></div>
                </div>
            </section>

            <section class="ficha" aria-labelledby="titulo-login">
                <h2 id="titulo-login">Entrar</h2>
                <p class="ficha__sub">Acesse sua conta para ver seus treinos.</p>

                <?php if ($mensagem): ?>
                    <p class="alerta alerta--<?= htmlspecialchars($tipoMensagem) ?>" role="alert"><?= htmlspecialchars($mensagem) ?></p>
                <?php endif; ?>

                <form class="form" action="index.php" method="POST">
                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" autocomplete="email" required>
                    </div>

                    <div class="campo">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" autocomplete="current-password" required>
                    </div>

                    <button class="btn" type="submit">Entrar</button>
                </form>

                <p class="ficha__rodape">Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
            </section>
        </main>
    </div>
</body>
</html>
