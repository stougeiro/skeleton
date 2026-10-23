<?php declare(strict_types=1);
/** @var STDW\View\Spec\TemplateContext $this */
/** @var string $id */
/** @var string $title */
/** @var string $message */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $this->echo($title) ?></title>
    </head>
    <body>
        <h1><?= $this->echo($title) ?></h1>
        <p><?= $this->echo($message) ?></p>
        <p><?= $this->echo('Código: ' . $id) ?></p>
    </body>
</html>
