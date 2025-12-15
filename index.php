<?php
// --- Load Config from JSON ---
$configFile = 'config.json';
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
} else {
    // Fallback if config is missing
    $config = [
        'title' => 'Microsite Not Configured',
        'background' => '',
        'menu' => []
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($config['title']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body style="background-image: url('<?php echo htmlspecialchars($config['background']); ?>');">

    <header>
        <h1><?php echo htmlspecialchars($config['title']); ?></h1>
        <nav>
            <ul>
                <?php foreach ($config['menu'] as $item): ?>
                    <li>
                        <?php
                        $icon_html = isset($item['icon']) && $item['icon'] ? '<i class="' . htmlspecialchars($item['icon']) . '"></i> ' : '';
                        ?>
                        <?php if ($item['type'] === 'link'): ?>
                            <a href="<?php echo htmlspecialchars($item['url']); ?>"><?php echo $icon_html; ?><?php echo htmlspecialchars($item['text']); ?></a>
                        <?php elseif ($item['type'] === 'modal'): ?>
                            <a href="#" class="modal-trigger" data-modal-content="<?php echo htmlspecialchars($item['content']); ?>"><?php echo $icon_html; ?><?php echo htmlspecialchars($item['text']); ?></a>
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
