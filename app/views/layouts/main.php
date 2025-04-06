<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'YDP Training' ?></title>

    <link rel="apple-touch-icon" sizes="180x180" href="/Sci-Bono-ICT-AcademyLMS/public/img/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/Sci-Bono-ICT-AcademyLMS/public/img/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Sci-Bono-ICT-AcademyLMS/public/img/icons/favicon-16x16.png">
    <link rel="manifest" href="/Sci-Bono-ICT-AcademyLMS/public/img/icons/site.webmanifest">
    <link rel="stylesheet" href="/Sci-Bono-ICT-AcademyLMS/public/css/main.css">
    <?php if (isset($extraCss)): foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="/Sci-Bono-ICT-AcademyLMS/public/css/<?= $css ?>.css">
    <?php endforeach; endif; ?>
</head>
<body>
    <?php include_once __DIR__ . '/../components/header.php'; ?>
    
    <main>
        <?= $content ?>
    </main>
    
    <?php include_once __DIR__ . '/../components/footer.php'; ?>
    
    <script src="/Sci-Bono-ICT-AcademyLMS/public/js/main.js"></script>
    <?php if (isset($extraJs)): foreach ($extraJs as $js): ?>
        <script src="/Sci-Bono-ICT-AcademyLMS/public/js/<?= $js ?>.js"></script>
    <?php endforeach; endif; ?>
</body>
</html>
