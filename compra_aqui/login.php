
<?php
session_start();
require_once "config/conexao.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($email === "" || $senha === "") {
        $mensagem = "Preencha todos os campos.";

    } else {

        $consulta = $pdo->prepare(
            "SELECT * FROM utilizadores WHERE email = ? LIMIT 1"
        );

        $consulta->execute([$email]);
        $utilizador = $consulta->fetch();

        if ($utilizador && password_verify($senha, $utilizador["senha"])) {

            session_regenerate_id(true);

            $_SESSION["utilizador_id"] = $utilizador["id"];
            $_SESSION["nome"] = $utilizador["nome"];
            $_SESSION["email"] = $utilizador["email"];
            $_SESSION["telefone"] = $utilizador["telefone"];
            $_SESSION["tipo"] = $utilizador["tipo"];

            header("Location: index.php");
            exit;

        } else {
            $mensagem = "Email ou palavra-passe incorretos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Entrar | CompraAqui</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .caixa {
            background: white;
            width: 100%;
            max-width: 430px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px #00000012;
        }

        .logo {
            text-align: center;
            font-size: 29px;
            font-weight: bold;
            color: #142b4a;
            margin-bottom: 20px;
        }

        .logo span {
            color: #ffbd35;
        }

        h2 {
            text-align: center;
            color: #142b4a;
            margin-bottom: 10px;
        }

        .subtitulo {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input {
            width: 100%;
            padding: 14px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #1e416b;
        }

        button {
            width: 100%;
            padding: 14px;
            background: #142b4a;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1e416b;
        }

        .mensagem {
            background: #ffe5e5;
            color: #a40000;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .sucesso {
            background: #e1f6e9;
            color: #176b35;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .rodape {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            line-height: 1.8;
        }

        a {
            color: #1e416b;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="caixa">

    <div class="logo">Compra<span>Aqui</span></div>

    <h2>Bem-vindo de volta!</h2>

    <p class="subtitulo">Entre na sua conta para continuar.</p>

    <?php if (isset($_GET["registo"]) && $_GET["registo"] === "sucesso"): ?>
        <div class="sucesso">
            Conta criada com sucesso! Agora pode iniciar sessão.
        </div>
    <?php endif; ?>

    <?php if ($mensagem !== ""): ?>
        <div class="mensagem">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <label>Email</label>
        <input
            type="email"
            name="email"
            placeholder="Digite o seu email"
            required
        >

        <label>Palavra-passe</label>
        <input
            type="password"
            name="senha"
            placeholder="Digite a sua palavra-passe"
            required
        >

        <button type="submit">Entrar na minha conta</button>

    </form>

    <div class="rodape">
        Ainda não tens uma conta?
        <a href="registo.php">Criar conta</a>
        <br>
        <a href="index.php">Voltar ao início</a>
    </div>

</div>

</body>
</html>
