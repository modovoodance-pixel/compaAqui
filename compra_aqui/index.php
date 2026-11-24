
<?php

session_start();

require_once "config/conexao.php";

// Pesquisar categorias
$stmtCategorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
$categorias = $stmtCategorias->fetchAll();

// Receber filtros
$pesquisa = trim($_GET['q'] ?? '');
$categoriaId = filter_input(INPUT_GET, 'categoria', FILTER_VALIDATE_INT);

// Preparar consulta dos produtos
$sql = "SELECT p.*, c.nome AS categoria, 
               u.nome AS vendedor, u.telefone
        FROM produtos p
        INNER JOIN categorias c ON p.categoria_id = c.id
        INNER JOIN utilizadores u ON p.utilizador_id = u.id
        WHERE p.estado = 'ativo'";

$parametros = [];

if ($pesquisa !== '') {
    $sql .= " AND (p.titulo LIKE :pesquisa OR p.descricao LIKE :pesquisa)";
    $parametros[':pesquisa'] = "%$pesquisa%";
}

if ($categoriaId) {
    $sql .= " AND p.categoria_id = :categoria";
    $parametros[':categoria'] = $categoriaId;
}

$sql .= " ORDER BY p.data_publicacao DESC LIMIT 12";

$stmtProdutos = $pdo->prepare($sql);
$stmtProdutos->execute($parametros);
$produtos = $stmtProdutos->fetchAll();

// Proteger textos apresentados na página
function limpar($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CompraAqui | Compre e venda em Angola</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #222;
        }

        header {
            background: #142b4a;
            color: white;
            padding: 20px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            font-size: 27px;
            font-weight: bold;
        }

        .logo span {
            color: #ffbd35;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        nav a:hover {
            color: #ffbd35;
        }

        .hero {
            background: #1e416b;
            color: white;
            text-align: center;
            padding: 65px 20px;
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 15px;
        }

        .hero p {
            margin-bottom: 25px;
            color: #e0e8f2;
        }

        .pesquisa {
            max-width: 650px;
            margin: auto;
            display: flex;
            gap: 10px;
        }

        .pesquisa input {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            min-width: 0;
        }

        .pesquisa button {
            background: #ffbd35;
            border: none;
            padding: 15px 22px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .container {
            width: 86%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .titulo {
            font-size: 24px;
            margin-bottom: 22px;
            color: #142b4a;
        }

        .categorias {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 15px;
        }

        .categoria {
            background: white;
            padding: 20px 10px;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            color: #142b4a;
            border: 1px solid #eee;
            transition: 0.2s;
        }

        .categoria:hover {
            transform: translateY(-4px);
            border-color: #ffbd35;
        }

        .produtos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 22px;
        }

       
.produto {
    background: white;
    border-radius: 9px;
    overflow: hidden;
    box-shadow: 0 2px 10px #00000010;
}


       


.produto img {
    display: block;
    width: auto;
    height: 300px;
    max-width: 100%;
    object-fit: contain;
    margin: 0 auto;
    transition: transform 0.4s ease;
}

.produto img:hover {
    transform: scale(1.11);
}
        .sem-imagem {
            height: 190px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9edf2;
            color: #778;
            font-size: 40px;
        }

        .produto-info {
            padding: 16px;
        }

        .produto-info h3 {
            font-size: 17px;
            margin-bottom: 10px;
        }

        .preco {
            color: #16834b;
            font-weight: bold;
            font-size: 19px;
            margin-bottom: 10px;
        }

        .detalhe {
            color: #777;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .whatsapp {
            display: block;
            background: #16834b;
            color: white;
            text-align: center;
            padding: 11px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 14px;
            font-weight: bold;
        }

        .whatsapp:hover {
            background: #10683b;
        }

        .vazio {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            color: #666;
        }

        footer {
            background: #142b4a;
            color: white;
            text-align: center;
            padding: 25px;
            margin-top: 60px;
        }

        @media (max-width: 600px) {
            header {
                flex-direction: column;
            }

            nav a {
                margin: 0 7px;
                font-size: 14px;
            }

            .hero h1 {
                font-size: 27px;
            }

            .pesquisa {
                flex-direction: column;
            }

            .container {
                width: 92%;
            }
        }
        /* Corrigir o fundo desfocado do produto */
article.produto .produto-imagem {
    position: relative !important;
    width: 100% !important;
    height: 360px !important;
    overflow: hidden !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #e9edf5;
}

/* Imagem de fundo desfocada */
article.produto .produto-imagem > img.imagem-fundo {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
        max-width: none !important;
        object-fit: cover !important;
    filter: blur(20px) brightness(0.65) !important;
    transform: scale(1.15) !important;
    z-index: 1 !important;
}

/* Imagem principal nítida */
article.produto .produto-imagem > img.imagem-principal {
    position: relative !important;
    z-index: 2 !important;
    width: auto !important;
    height: 100% !important;
    max-width: 100% !important;
    object-fit: contain !important;
}

/* Grelha de produtos: 4 por linha */
.produtos {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 22px;
    align-items: start;
}

/* Cartão do produto */
.produto {
    width: 100%;
    min-width: 0;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px #00000010;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Efeito ao passar o cursor */
.produto:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 22px #00000018;
}

/* Área da imagem */
.produto-imagem {
    position: relative;
    width: 100%;
    height: 260px;
    overflow: hidden;
    background: #f1f2f5;
    border-radius: 12px 12px 0 0;
}

/* Fundo desfocado */
.produto-imagem .imagem-fundo {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: blur(18px);
    transform: scale(1.15);
    opacity: 0.65;
}

/* Imagem principal sem cortes */
.produto-imagem .imagem-principal {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    z-index: 1;
    transition: transform 0.4s ease;
}

/* Zoom suave ao passar o cursor */
.produto:hover .imagem-principal {
    transform: scale(1.06);
}

/* Informações */
.produto-info {
    padding: 14px;
}

/* Sem imagem */
.sem-imagem {
    height: 260px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f2f5;
    font-size: 40px;
    border-radius: 12px 12px 0 0;
}

/* Tablets */
@media (max-width: 1000px) {
    .produtos {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Telemóveis */
@media (max-width: 550px) {
    .produtos {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .produto-imagem,
    .sem-imagem {
        height: 190px;
    }

    .produto-info {
        padding: 10px;
    }
}

/* Botão de detalhes dos produtos */
.ver-detalhes {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    width: 100%;
    padding: 13px 15px;
    margin: 12px 0;

    background: #142b4a;
    color: #ffffff;

    border: 2px solid #142b4a;
    border-radius: 9px;

    text-decoration: none;
    font-size: 15px;
    font-weight: bold;

    transition: all 0.3s ease;
}

.ver-detalhes:hover {
    background: #ffbd35;
    border-color: #ffbd35;
    color: #142b4a;
    transform: translateY(-3px);
    box-shadow: 0 5px 12px #00000018;
}

    </style>
</head>

<body>


<header>
    <div class="logo">Compra<span>Aqui</span></div>

    <nav>
        <a href="index.php">Início</a>

        <?php if (isset($_SESSION["utilizador_id"])): ?>

            <?php if ($_SESSION["tipo"] === "vendedor"): ?>
                <a href="produtos/publicar.php">Publicar produto</a>
            <?php endif; ?>

            <a href="logout.php">Sair</a>

        <?php else: ?>

            <a href="login.php">Entrar</a>
            <a href="registo.php">Registar</a>

        <?php endif; ?>
    </nav>
</header>


<section class="hero">
    <h1>Encontre o que procura!</h1>

    <p>Compre e venda produtos de forma simples e rápida em Angola.</p>

    <form class="pesquisa" method="GET" action="index.php">
        <input
            type="text"
            name="q"
            placeholder="O que deseja encontrar?"
            value="<?= limpar($pesquisa) ?>"
        >

        <button type="submit">Pesquisar</button>
    </form>
</section>

<main class="container">

    <h2 class="titulo">Categorias</h2>

    <div class="categorias">

        <?php foreach ($categorias as $categoria): ?>

            <a class="categoria"
               href="index.php?categoria=<?= (int)$categoria['id'] ?>">

                🛍️
                <p><?= limpar($categoria['nome']) ?></p>

            </a>

        <?php endforeach; ?>

    </div>

    <br><br>

    <h2 class="titulo">Produtos recentes</h2>

    <?php if (count($produtos) > 0): ?>

        <div class="produtos">

            <?php foreach ($produtos as $produto): ?>

                <article class="produto">

                    <?php if (!empty($produto['imagem'])): ?>

                       <div class="produto-imagem">

    <img
        class="imagem-fundo"
        src="<?= limpar($produto['imagem']) ?>"
        alt=""
    >

    <img
        class="imagem-principal"
        src="<?= limpar($produto['imagem']) ?>"
        alt="<?= limpar($produto['titulo']) ?>"
    >

</div>
                    <?php else: ?>

                        <div class="sem-imagem">📦</div>

                    <?php endif; ?>

                    <div class="produto-info">

                        <h3><?= limpar($produto['titulo']) ?></h3>
                        <a
    href="produtos/detalhes.php?id=<?= (int)$produto['id'] ?>"
    class="ver-detalhes"
>
    Ver detalhes do produto
</a>

                        <p class="preco">
                            <?= number_format((float)$produto['preco'], 2, ',', '.') ?> Kz
                        </p>

                        <p class="detalhe">
                            Categoria: <?= limpar($produto['categoria']) ?>
                        </p>

                        <p class="detalhe">
                            Localização: <?= limpar($produto['municipio'] ?: $produto['provincia']) ?>
                        </p>

                        <p class="detalhe">
                            Vendedor: <?= limpar($produto['vendedor']) ?>
                        </p>

                        <?php
                        $telefone = preg_replace('/\D/', '', $produto['telefone']);

                        if (substr($telefone, 0, 1) === '0') {
                            $telefone = '244' . substr($telefone, 1);
                        } elseif (substr($telefone, 0, 3) !== '244') {
                            $telefone = '244' . $telefone;
                        }
                        ?>

                        <a class="whatsapp"
                           href="https://wa.me/<?= limpar($telefone) ?>"
                           target="_blank"
                           rel="noopener">
                            Contactar vendedor
                        </a>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="vazio">
            <h3>Nenhum produto encontrado.</h3>
            <p>Quando os vendedores publicarem produtos, eles aparecerão aqui.</p>
        </div>

    <?php endif; ?>

</main>

<footer>
    <p>© <?= date('Y') ?> CompraAqui - Compre e venda em Angola.</p>
    <p>O teu mercado online.</p>
</footer>

</body>
</html>
