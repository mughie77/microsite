<?php
$config = include('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['title']); ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-image: url('<?php echo htmlspecialchars($config['background']); ?>');">

    <header>
        <h1><?php echo htmlspecialchars($config['title']); ?></h1>
        <nav>
            <ul>
                <?php foreach ($config['menu'] as $item): ?>
                    <li>
                        <?php if ($item['type'] === 'link'): ?>
                            <a href="<?php echo htmlspecialchars($item['url']); ?>"><?php echo htmlspecialchars($item['text']); ?></a>
                        <?php elseif ($item['type'] === 'modal'): ?>
                            <a href="#" class="modal-trigger" data-modal-content="<?php echo htmlspecialchars($item['content']); ?>"><?php echo htmlspecialchars($item['text']); ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </header>

    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <p id="modal-text"></p>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
