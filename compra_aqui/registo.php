
<?php
require_once "config/conexao.php";

$mensagem = "";
$tipoMensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $telefone = trim($_POST["telefone"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";
    $tipo = $_POST["tipo"] ?? "comprador";

    if ($nome === "" || $email === "" || $telefone === "" || $senha === "") {
        $mensagem = "Preencha todos os campos.";
        $tipoMensagem = "erro";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Introduza um endereço de email válido.";
        $tipoMensagem = "erro";

    } elseif (strlen($senha) < 8) {
        $mensagem = "A palavra-passe deve ter pelo menos 8 caracteres.";
        $tipoMensagem = "erro";

    } elseif ($senha !== $confirmarSenha) {
        $mensagem = "As palavras-passe não coincidem.";
        $tipoMensagem = "erro";

    } elseif (!in_array($tipo, ["comprador", "vendedor"], true)) {
        $mensagem = "Selecione um tipo de conta válido.";
        $tipoMensagem = "erro";

    } else {
        try {
            $verificar = $pdo->prepare(
                "SELECT id FROM utilizadores WHERE email = ?"
            );

            $verificar->execute([$email]);

            if ($verificar->fetch()) {
                $mensagem = "Este email já está registado.";
                $tipoMensagem = "erro";

            } else {
                $senhaSegura = password_hash($senha, PASSWORD_DEFAULT);

                $inserir = $pdo->prepare(
                    "INSERT INTO utilizadores
                    (nome, email, telefone, senha, tipo)
                    VALUES (?, ?, ?, ?, ?)"
                );

                $inserir->execute([
                    $nome,
                    $email,
                    $telefone,
                    $senhaSegura,
                    $tipo
                ]);

                header("Location: login.php?registo=sucesso");
                exit;
            }

        } catch (PDOException $e) {
            $mensagem = "Não foi possível criar a conta. Tente novamente.";
            $tipoMensagem = "erro";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Criar conta | CompraAqui</title>

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
            padding: 25px;
        }

        .caixa {
            background: white;
            width: 100%;
            max-width: 460px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 5px 25px #00000012;
        }

        .logo {
            text-align: center;
            font-size: 29px;
            font-weight: bold;
            color: #142b4a;
            margin-bottom: 10px;
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
            font-size: 14px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 17px;
            font-size: 15px;
        }

        input:focus, select:focus {
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
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 6px;
            font-size: 14px;
        }

        .erro {
            background: #ffe5e5;
            color: #a40000;
        }

        .rodape {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
        }

        .rodape a {
            color: #1e416b;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="caixa">

    <div class="logo">Compra<span>Aqui</span></div>

    <h2>Criar uma conta</h2>

    <p class="subtitulo">
        Junte-se ao CompraAqui e comece a comprar ou vender.
    </p>

    <?php if ($mensagem !== ""): ?>
        <div class="mensagem <?= $tipoMensagem ?>">
            <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="registo.php">

        <label>Nome completo</label>
        <input type="text" name="nome"
               placeholder="Digite o seu nome"
               value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required>

        <label>Email</label>
        <input type="email" name="email"
               placeholder="exemplo@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required>

        <label>Telefone / WhatsApp</label>
        <input type="tel" name="telefone"
               placeholder="Ex: 923000000"
               value="<?= htmlspecialchars($_POST['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               required>

        <label>Tipo de conta</label>
        <select name="tipo" required>
            <option value="comprador">Quero comprar</option>
            <option value="vendedor"
                <?= (($_POST['tipo'] ?? '') === 'vendedor') ? 'selected' : '' ?>>
                Quero vender
            </option>
        </select>

        <label>Palavra-passe</label>
        <input type="password" name="senha"
               placeholder="Mínimo de 8 caracteres"
               minlength="8" required>

        <label>Confirmar palavra-passe</label>
        <input type="password" name="confirmar_senha"
               placeholder="Repita a palavra-passe"
               required>

        <button type="submit">Criar minha conta</button>

    </form>

    <div class="rodape">
        Já tens uma conta?
        <a href="login.php">Entrar</a>
        <br><br>
        <a href="index.php">Voltar ao início</a>
    </div>

</div>

</body>
</html>
