
<?php
require_once "../config/conexao.php";

// Receber o ID do produto
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    die("Produto não encontrado.");
}

// Buscar informações do produto
$sql = "SELECT
            p.*,
            c.nome AS categoria,
            u.nome AS vendedor,
            u.telefone
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        INNER JOIN utilizadores u ON p.utilizador_id = u.id
        WHERE p.id = ? AND p.estado = 'ativo'";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);

$produto = $stmt->fetch();

if (!$produto) {
    die("Este produto não existe ou não está disponível.");
}

// Buscar fotografias adicionais
$stmtFotos = $pdo->prepare(
    "SELECT imagem FROM produto_fotos
     WHERE produto_id = ?
     ORDER BY ordem ASC"
);

$stmtFotos->execute([$id]);
$fotosAdicionais = $stmtFotos->fetchAll();

// Organizar todas as fotografias
$imagens = [];

if (!empty($produto["imagem"])) {
    $imagens[] = $produto["imagem"];
}

foreach ($fotosAdicionais as $foto) {
    $imagens[] = $foto["imagem"];
}

// Preparar telefone para WhatsApp
$telefone = preg_replace('/\D/', '', $produto["telefone"]);

if (substr($telefone, 0, 1) === "0") {
    $telefone = "244" . substr($telefone, 1);
} elseif (substr($telefone, 0, 3) !== "244") {
    $telefone = "244" . $telefone;
}

function limpar($texto) {
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= limpar($produto["titulo"]) ?> | CompraAqui</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f5f9;
            color: #26364b;
        }

        header {
            background: #142b4a;
            padding: 20px 7%;
            color: white;
        }

        .logo {
            font-size: 27px;
            font-weight: bold;
        }

        .logo span {
            color: #ffbd35;
        }

        .container {
            max-width: 1100px;
            width: 92%;
            margin: 40px auto;
        }

        .produto-detalhes {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 35px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px #0000000c;
        }

        .imagem-principal {
            width: 100%;
            height: 430px;
            object-fit: contain;
            background: #f4f5f8;
            border-radius: 12px;
        }

        .galeria-miniaturas {
            display: flex;
            gap: 12px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .miniatura {
            width: 85px;
            height: 85px;
            object-fit: contain;
            background: #f4f5f8;
            border: 2px solid transparent;
            border-radius: 9px;
            cursor: pointer;
        }

        .miniatura.ativa {
            border-color: #ffbd35;
        }

        .informacoes h1 {
            font-size: 29px;
            color: #142b4a;
            margin-bottom: 18px;
        }

        .preco {
            color: #16834b;
            font-size: 27px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .informacoes p {
            margin-bottom: 17px;
            line-height: 1.6;
            color: #68778a;
        }

        .informacoes strong {
            color: #26364b;
        }

        .descricao {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e8ed;
        }

        .whatsapp {
            display: block;
            text-align: center;
            background: #16834b;
            color: white;
            text-decoration: none;
            padding: 15px;
            border-radius: 9px;
            font-weight: bold;
            margin-top: 30px;
        }

        .whatsapp:hover {
            background: #10683b;
        }

        .voltar {
            display: inline-block;
            margin-top: 25px;
            color: #1e416b;
            text-decoration: none;
            font-weight: bold;
        }

        .sem-foto {
            height: 430px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f5f8;
            border-radius: 12px;
            font-size: 60px;
        }

        @media (max-width: 750px) {
            .produto-detalhes {
                grid-template-columns: 1fr;
                padding: 18px;
            }

            .imagem-principal,
            .sem-foto {
                height: 300px;
            }

            .informacoes h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="logo">Compra<span>Aqui</span></div>
</header>

<main class="container">

    <section class="produto-detalhes">

        
<div class="galeria">

    <?php if (count($imagens) > 0): ?>

        <div class="imagem-com-setas">

            <img
                id="imagemPrincipal"
                class="imagem-principal"
                src="../<?= limpar($imagens[0]) ?>"
                alt="<?= limpar($produto["titulo"]) ?>"
            >

            <?php if (count($imagens) > 1): ?>
                <button type="button" class="seta-galeria seta-anterior"
                    onclick="mudarImagem(-1)" aria-label="Fotografia anterior">
                    &#10094;
                </button>

                <button type="button" class="seta-galeria seta-seguinte"
                    onclick="mudarImagem(1)" aria-label="Fotografia seguinte">
                    &#10095;
                </button>
            <?php endif; ?>

        </div>

        <div class="galeria-miniaturas">

            <?php foreach ($imagens as $indice => $imagem): ?>
                <img
                    class="miniatura <?= $indice === 0 ? 'ativa' : '' ?>"
                    src="../<?= limpar($imagem) ?>"
                    alt="Fotografia <?= $indice + 1 ?>"
                    onclick="selecionarImagem(<?= $indice ?>)"
                >
            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="sem-foto">📦</div>

    <?php endif; ?>

</div>

        <div class="informacoes">

            <h1><?= limpar($produto["titulo"]) ?></h1>

            <div class="preco">
                <?= number_format((float)$produto["preco"], 2, ',', '.') ?> Kz
            </div>

            <p>
                <strong>Categoria:</strong>
                <?= limpar($produto["categoria"]) ?>
            </p>

            <p>
                <strong>Localização:</strong>
                <?= limpar($produto["municipio"] ?: $produto["provincia"]) ?>
            </p>

            <p>
                <strong>Vendedor:</strong>
                <?= limpar($produto["vendedor"]) ?>
            </p>

            <div class="descricao">

                <h3>Descrição do produto</h3>

                <p><?= nl2br(limpar($produto["descricao"])) ?></p>

            </div>

            <a
                class="whatsapp"
                href="https://wa.me/<?= limpar($telefone) ?>"
                target="_blank"
                rel="noopener"
            >
                Contactar vendedor pelo WhatsApp
            </a>

        </div>

    </section>

    <a class="voltar" href="../index.php">
        ← Voltar aos produtos
    </a>

</main>

<script>
function mudarImagem(elemento) {

    document.getElementById("imagemPrincipal").src = elemento.src;

    document.querySelectorAll(".miniatura").forEach(function(imagem) {
        imagem.classList.remove("ativa");
    });

    elemento.classList.add("ativa");
}
</script>

</body>
</html>
