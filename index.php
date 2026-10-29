<?php
session_start();
require_once 'php/conexao.php';

$mensagem = '';

if (isset($_GET['cadastrado'])) {
    $mensagem = "Registo realizado com sucesso! Faça login.";
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
            $mensagem = "E-mail ou palavra-passe incorretos!";
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
    <title>Projeto Acad - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login - Projeto Acad</h2>
        <?php if ($mensagem): ?>
            <p class="alerta"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <label>E-mail:</label>
            <input type="email" name="email" required>
            
            <label>Palavra-passe:</label>
            <input type="password" name="senha" required>
            
            <button type="submit">Entrar</button>
        </form>
        <p>Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>
</body>
</html>