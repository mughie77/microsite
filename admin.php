<?php
session_start();

// --- Hardcoded Password ---
$password = 'admin123';

// --- Login Logic ---
if (isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['loggedin'] = true;
        header('Location: admin.php'); // Redirect after successful login
        exit;
    } else {
        $login_error = 'Password salah!';
    }
}

// --- Logout Logic ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// --- Save Logic ---
// Process only if logged in and form is submitted with expected data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_POST['title'])) {

    // Rebuild the config array from POST data
    $new_config = [];
    $new_config['title'] = $_POST['title'] ?? '';
    $new_config['background'] = $_POST['background'] ?? '';
    $new_config['menu'] = [];

    if (isset($_POST['menu']) && is_array($_POST['menu'])) {
        foreach ($_POST['menu'] as $menu_item_data) {
            $item = [];
            $item['text'] = $menu_item_data['text'] ?? '';
            $item['icon'] = $menu_item_data['icon'] ?? '';
            $item['type'] = $menu_item_data['type'] ?? '';

            // Only include url or content based on type
            if ($item['type'] === 'link') {
                $item['url'] = $menu_item_data['url'] ?? '';
            } elseif ($item['type'] === 'modal') {
                $item['content'] = $menu_item_data['content'] ?? '';
            }
            $new_config['menu'][] = $item;
        }
    }

    // Generate the PHP code string using var_export for safe output
    $config_string = "<?php\n\nreturn " . var_export($new_config, true) . ";\n";

    // Save to config.php
    file_put_contents('config.php', $config_string);

    // Redirect to prevent form resubmission
    header('Location: admin.php?saved=true');
    exit;
}


// --- Include Config ---
// This now loads the potentially updated config
$config = require 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .success-message {
            background-color: #00ffcc;
            color: #0a0a1a;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        body {
            font-family: 'Orbitron', sans-serif;
            background-color: #0a0a1a;
            color: #00ffcc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            width: 100%;
            max-width: 800px;
            background-color: #1a1a2a;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 25px rgba(0, 255, 204, 0.5);
            border: 1px solid #00ffcc;
        }
        h1, h2 {
            text-align: center;
            text-shadow: 0 0 10px #00ffcc;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            padding: 10px;
            border: 1px solid #00ffcc;
            background-color: #0a0a1a;
            color: #00ffcc;
            border-radius: 5px;
            font-family: inherit;
        }
        input[type="submit"] {
            margin-top: 20px;
            padding: 12px;
            background-color: #00ffcc;
            color: #0a0a1a;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: inherit;
            font-weight: bold;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }
        input[type="submit"]:hover {
            box-shadow: 0 0 15px #00ffcc;
        }
        .error {
            color: #ff4d4d;
            text-align: center;
            margin-top: 10px;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout a {
            color: #00ffcc;
            text-decoration: none;
        }
        .menu-item {
            border: 1px dashed #00ffcc;
            padding: 15px;
            margin-top: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
            <h2>Login</h2>
            <form method="POST" action="admin.php">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <input type="submit" value="Login">
                <?php if (isset($login_error)): ?>
                    <p class="error"><?php echo htmlspecialchars($login_error); ?></p>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <h2>Site Configuration</h2>
            <?php if (isset($_GET['saved'])): ?>
                <div class="success-message">Configuration saved successfully!</div>
            <?php endif; ?>
            <form method="POST" action="admin.php">
                <label for="title">Site Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($config['title']); ?>">

                <label for="background">Background Image Path:</label>
                <input type="text" id="background" name="background" value="<?php echo htmlspecialchars($config['background']); ?>">

                <h2>Menu Items</h2>
                <?php foreach ($config['menu'] as $index => $item): ?>
                    <div class="menu-item">
                        <h3>Menu Item <?php echo $index + 1; ?></h3>
                        <label for="menu_text_<?php echo $index; ?>">Text:</label>
                        <input type="text" id="menu_text_<?php echo $index; ?>" name="menu[<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($item['text']); ?>">

                        <label for="menu_icon_<?php echo $index; ?>">Icon (Font Awesome Class):</label>
                        <input type="text" id="menu_icon_<?php echo $index; ?>" name="menu[<?php echo $index; ?>][icon]" value="<?php echo htmlspecialchars($item['icon'] ?? ''); ?>">

                        <label for="menu_type_<?php echo $index; ?>">Type (link/modal):</label>
                        <input type="text" id="menu_type_<?php echo $index; ?>" name="menu[<?php echo $index; ?>][type]" value="<?php echo htmlspecialchars($item['type']); ?>">

                        <label for="menu_url_<?php echo $index; ?>">URL (for type 'link'):</label>
                        <input type="text" id="menu_url_<?php echo $index; ?>" name="menu[<?php echo $index; ?>][url]" value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>">

                        <label for="menu_content_<?php echo $index; ?>">Content (for type 'modal'):</label>
                        <input type="text" id="menu_content_<?php echo $index; ?>" name="menu[<?php echo $index; ?>][content]" value="<?php echo htmlspecialchars($item['content'] ?? ''); ?>">
                    </div>
                <?php endforeach; ?>

                <input type="submit" value="Save Configuration">
            </form>
            <div class="logout">
                <a href="?logout=true">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
