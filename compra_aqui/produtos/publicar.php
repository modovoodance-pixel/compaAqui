<?php
session_start();

require_once "../config/conexao.php";

// Verificar se o utilizador está autenticado
if (!isset($_SESSION["utilizador_id"])) {
    header("Location: ../login.php");
    exit;
}

// Verificar se é vendedor
if ($_SESSION["tipo"] !== "vendedor") {
    die("Apenas vendedores podem publicar produtos.");
}

// Buscar categorias
$stmt = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
$categorias = $stmt->fetchAll();

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $titulo = trim($_POST["titulo"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco = $_POST["preco"] ?? "";
    $categoria_id = $_POST["categoria_id"] ?? "";
    $municipio = trim($_POST["municipio"] ?? "");

    // Validar os campos
    if (
        $titulo === "" ||
        $descricao === "" ||
        !is_numeric($preco) ||
        (float)$preco <= 0 ||
        !ctype_digit((string)$categoria_id) ||
        $municipio === ""
    ) {
        $erro = "Preencha todos os campos corretamente.";
    }

    // Preparar as fotografias
    $fotografias = [];

    if ($erro === "" && isset($_FILES["imagens"])) {

        $ficheiros = $_FILES["imagens"];
        $total = count($ficheiros["name"]);

        if ($total > 5) {
            $erro = "Pode enviar no máximo 5 fotografias.";
        } else {

            $tiposPermitidos = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            for ($i = 0; $i < $total; $i++) {

                if ($ficheiros["error"][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                if ($ficheiros["error"][$i] !== UPLOAD_ERR_OK) {
                    $erro = "Ocorreu um erro ao enviar uma fotografia.";
                    break;
                }

                if ($ficheiros["size"][$i] > 5 * 1024 * 1024) {
                    $erro = "Cada fotografia deve ter no máximo 5 MB.";
                    break;
                }

                $tipoImagem = $finfo->file($ficheiros["tmp_name"][$i]);

                if (!isset($tiposPermitidos[$tipoImagem])) {
                    $erro = "Use apenas fotografias JPG, PNG ou WEBP.";
                    break;
                }

                $fotografias[] = [
                    "temporario" => $ficheiros["tmp_name"][$i],
                    "extensao" => $tiposPermitidos[$tipoImagem]
                ];
            }
        }
    }

    // Guardar fotografias e produto
    if ($erro === "") {

        $pasta = "../assets/imagens/";

        if (!is_dir($pasta)) {
            mkdir($pasta, 0755, true);
        }

        $caminhos = [];

        try {

            // Mover as fotografias para a pasta
            foreach ($fotografias as $foto) {

                $nomeImagem = bin2hex(random_bytes(16)) . "." . $foto["extensao"];
                $destino = $pasta . $nomeImagem;

                if (!move_uploaded_file($foto["temporario"], $destino)) {
                    throw new Exception("Não foi possível guardar uma fotografia.");
                }

                $caminhos[] = "assets/imagens/" . $nomeImagem;
            }

            // Primeira fotografia será a principal
            $imagemPrincipal = $caminhos[0] ?? null;

            // Iniciar transação
            $pdo->beginTransaction();

            // Inserir produto
            $sql = "INSERT INTO produtos
                    (utilizador_id, categoria_id, titulo, descricao, preco, municipio, imagem)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $_SESSION["utilizador_id"],
                $categoria_id,
                $titulo,
                $descricao,
                $preco,
                $municipio,
                $imagemPrincipal
            ]);

            $produtoId = $pdo->lastInsertId();

            // Guardar fotografias adicionais
            $sqlFoto = "INSERT INTO produto_fotos
                        (produto_id, imagem, ordem)
                        VALUES (?, ?, ?)";

            $stmtFoto = $pdo->prepare($sqlFoto);

            // Começar na segunda fotografia
            for ($i = 1; $i < count($caminhos); $i++) {

                $stmtFoto->execute([
                    $produtoId,
                    $caminhos[$i],
                    $i + 1
                ]);
            }

            // Confirmar operação
            $pdo->commit();

            header("Location: ../index.php");
            exit;

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            // Apagar fotografias caso ocorra um erro
            foreach ($caminhos as $caminho) {
                $ficheiro = "../" . $caminho;

                if (file_exists($ficheiro)) {
                    unlink($ficheiro);
                }
            }

            $erro = "Erro ao publicar o produto. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Publicar produto | CompraAqui</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="pagina-publicar">

    <h1>Publicar um produto</h1>

    <p>Coloque o seu produto à venda no CompraAqui.</p>

    <?php if ($erro !== ""): ?>
        <p style="color:red;">
            <?= htmlspecialchars($erro) ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Nome do produto</label>
        <input type="text" name="titulo" required>

        <br><br>

        <label>Descrição</label>
        <textarea name="descricao" required></textarea>

        <br><br>

        <label>Preço (Kz)</label>
        <input type="number" name="preco" min="1" step="0.01" required>

        <br><br>

        <label>Categoria</label>

        <select name="categoria_id" required>

            <option value="">Escolha uma categoria</option>

            <?php foreach ($categorias as $categoria): ?>

                <option value="<?= $categoria["id"] ?>">
                    <?= htmlspecialchars($categoria["nome"]) ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>

        <label>Município</label>
        <input type="text" name="municipio" required>

        <br><br>

        <label>Fotografias do produto (máximo 5)</label>

        <input
            type="file"
            name="imagens[]"
            accept="image/jpeg,image/png,image/webp"
            multiple
        >

        <p>Pode selecionar até 5 fotografias. Cada uma deve ter no máximo 5 MB.</p>

        <br>

        <button type="submit">Publicar produto</button>

    </form>

    <br>

    <a href="../index.php">Voltar à página inicial</a>

</body>
</html>
