<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'YDP Training' ?></title>
    <link rel="stylesheet" href="/css/main.css">
    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="/css/<?= $css ?>.css">
    <?php endforeach; endif; ?>
</head>
<body>
    <?php include_once __DIR__ . '/../components/header.php'; ?>
    
    <main>
        <?= $content ?>
    </main>
    
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
    
    <script src="/js/main.js"></script>
    <?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
        <script src="/js/<?= $js ?>.js"></script>
    <?php endforeach; endif; ?>
</body>
</html>