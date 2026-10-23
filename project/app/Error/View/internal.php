<?php declare(strict_types=1);
/** @var STDW\View\Spec\TemplateContext $this */
/** @var string $id */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro interno</title>
    </head>
    <body>
        <h1><?= $this->echo('Erro interno') ?></h1>
        <p><?= $this->echo('Ocorreu um erro inesperado.') ?></p>
        <p><?= $this->echo('Código: ' . $id) ?></p>
    </body>
</html>
