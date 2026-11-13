<?php
session_start();
require_once 'php/conexao.php';

$mensagem = '';
$tipoMensagem = 'erro';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $mensagem = "Este e-mail já está cadastrado!";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
            if ($stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash])) {
                header("Location: index.php?cadastrado=1");
                exit;
            } else {
                $mensagem = "Erro ao cadastrar usuário.";
            }
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
    <title>Criar conta | Projeto Acad</title>
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
                <h1>Comece sua ficha de treino.</h1>
                <p>Crie sua conta e registre o primeiro treino hoje.</p>
                <img class="barra" src="img/barra.svg" alt="" width="560" height="140">
                <div class="amostra" aria-hidden="true">
                    <div><span>Supino reto</span><strong>4 × 12 &nbsp; 40 kg</strong></div>
                    <div><span>Agachamento livre</span><strong>4 × 10 &nbsp; 70 kg</strong></div>
                    <div><span>Remada curvada</span><strong>3 × 12 &nbsp; 45 kg</strong></div>
                </div>
            </section>

            <section class="ficha" aria-labelledby="titulo-cadastro">
                <h2 id="titulo-cadastro">Criar conta</h2>
                <p class="ficha__sub">Leva menos de um minuto.</p>

                <?php if ($mensagem): ?>
                    <p class="alerta alerta--<?= htmlspecialchars($tipoMensagem) ?>" role="alert"><?= htmlspecialchars($mensagem) ?></p>
                <?php endif; ?>

                <form class="form" action="cadastro.php" method="POST">
                    <div class="campo">
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" maxlength="100" autocomplete="name" required>
                    </div>

                    <div class="campo">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" maxlength="100" autocomplete="email" required>
                    </div>

                    <div class="campo">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" autocomplete="new-password" required>
                    </div>

                    <button class="btn" type="submit">Criar conta</button>
                </form>

                <p class="ficha__rodape">Já tem conta? <a href="index.php">Entrar</a></p>
            </section>
        </main>
    </div>
</body>
</html>
