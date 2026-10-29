<?php
session_start();
require_once 'php/conexao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (!empty($nome) && !empty($email) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $mensagem = "Este e-mail já está registado!";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
            if ($stmt->execute([':nome' => $nome, ':email' => $email, ':senha' => $senhaHash])) {
                header("Location: index.php?cadastrado=1");
                exit;
            } else {
                $mensagem = "Erro ao registar utilizador.";
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
    <title>Projeto Acad - Registo</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Criar Conta - Projeto Acad</h2>
        <?php if ($mensagem): ?>
            <p class="alerta"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>
        <form action="cadastro.php" method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" required>
            
            <label>E-mail:</label>
            <input type="email" name="email" required>
            
            <label>Palavra-passe:</label>
            <input type="password" name="senha" required>
            
            <button type="submit">Registar</button>
        </form>
        <p>Já tem conta? <a href="index.php">Faça Login</a></p>
    </div>
</body>
</html>