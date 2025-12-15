<?php
session_start();

// --- Load Admin Password Securely ---
if (file_exists('admin_config.php')) {
    $password = require 'admin_config.php';
} else {
    $password = 'default_password_change_me';
}

// --- Login Logic ---
if (isset($_POST['password'])) {
    if ($_POST['password'] === $password) {
        $_SESSION['loggedin'] = true;
        header('Location: admin.php');
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

// --- Config File Path ---
$configFile = 'config.json';

// --- Save Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_POST['title'])) {
    $new_config = [];
    $new_config['title'] = $_POST['title'] ?? '';
    $new_config['background'] = $_POST['background'] ?? '';
    $new_config['menu'] = [];

    if (isset($_POST['menu']) && is_array($_POST['menu'])) {
        foreach ($_POST['menu'] as $menu_item_data) {
            if (empty($menu_item_data['text'])) {
                continue;
            }
            $item = [];
            $item['text'] = $menu_item_data['text'] ?? '';
            $item['icon'] = $menu_item_data['icon'] ?? '';
            $item['type'] = $menu_item_data['type'] ?? 'link';

            if ($item['type'] === 'link') {
                $item['url'] = $menu_item_data['url'] ?? '#';
            } elseif ($item['type'] === 'modal') {
                $item['content'] = $menu_item_data['content'] ?? '';
            }
            $new_config['menu'][] = $item;
        }
    }

    file_put_contents($configFile, json_encode($new_config, JSON_PRETTY_PRINT));
    header('Location: admin.php?saved=true');
    exit;
}

// --- Load Config ---
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
} else {
    // Default config if file doesn't exist
    $config = [
        'title' => 'My Microsite',
        'background' => 'images/background.jpg',
        'menu' => []
    ];
}

$icons = require 'icons.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
            <div class="login-form">
                <h2>Login</h2>
                <form method="POST" action="admin.php">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <?php if (isset($login_error)): ?>
                        <p class="error"><?php echo htmlspecialchars($login_error); ?></p>
                    <?php endif; ?>
                </form>
            </div>
        <?php else: ?>
            <form method="POST" action="admin.php">
                <?php if (isset($_GET['saved'])): ?>
                    <div class="success-message">Configuration saved successfully!</div>
                <?php endif; ?>

                <div class="site-settings">
                    <h2>Site Settings</h2>
                    <div class="form-group">
                        <label for="title">Site Title:</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($config['title']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="background">Background Image Path:</label>
                        <input type="text" id="background" name="background" value="<?php echo htmlspecialchars($config['background']); ?>">
                    </div>
                </div>

                <h2>Menu Items</h2>
                <div id="menu-items-container">
                    <?php foreach ($config['menu'] as $index => $item): ?>
                        <div class="menu-item" data-index="<?php echo $index; ?>">
                            <div class="menu-item-header">
                                <h3>Menu Item <?php echo $index + 1; ?></h3>
                                <button type="button" class="btn btn-danger remove-menu-item">Remove</button>
                            </div>
                            <div class="menu-item-fields">
                                <div class="form-group">
                                    <label>Text:</label>
                                    <input type="text" name="menu[<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($item['text']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>Icon:</label>
                                    <div class="icon-picker-container">
                                        <input type="text" name="menu[<?php echo $index; ?>][icon]" value="<?php echo htmlspecialchars($item['icon'] ?? ''); ?>" readonly class="icon-input">
                                        <button type="button" class="btn btn-secondary select-icon-btn">Select Icon</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Type (link/modal):</label>
                                    <input type="text" name="menu[<?php echo $index; ?>][type]" value="<?php echo htmlspecialchars($item['type']); ?>">
                                </div>
                                <div class="form-group">
                                    <label>URL (for type 'link'):</label>
                                    <input type="text" name="menu[<?php echo $index; ?>][url]" value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>">
                                </div>
                                <div class="form-group" style="grid-column: span 2;">
                                    <label>Content (for type 'modal'):</label>
                                    <input type="text" name="menu[<?php echo $index; ?>][content]" value="<?php echo htmlspecialchars($item['content'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="add-menu-item" class="btn btn-secondary" style="margin-top: 20px;">Add New Menu Item</button>
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </form>
            <div class="logout">
                <a href="?logout=true">Logout</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Icon Picker Modal -->
    <div id="icon-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Select an Icon</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <input type="text" id="icon-search" placeholder="Search for icons...">
                <div id="icon-grid">
                    <?php foreach($icons as $icon): ?>
                        <div class="icon-preview" data-icon-class="<?php echo $icon; ?>">
                            <i class="<?php echo $icon; ?>"></i>
                            <span><?php echo $icon; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuContainer = document.getElementById('menu-items-container');
        const addButton = document.getElementById('add-menu-item');

        const updateIndexes = () => {
            const items = menuContainer.querySelectorAll('.menu-item');
            items.forEach((item, index) => {
                item.dataset.index = index;
                const header = item.querySelector('h3');
                if (header) header.textContent = `Menu Item ${index + 1}`;

                const inputs = item.querySelectorAll('input');
                inputs.forEach(input => {
                    const name = input.getAttribute('name');
                    if (name) input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                });
            });
        };

        addButton.addEventListener('click', () => {
            const newIndex = menuContainer.querySelectorAll('.menu-item').length;
            const newItem = document.createElement('div');
            newItem.className = 'menu-item';
            newItem.dataset.index = newIndex;

            newItem.innerHTML = `
                <div class="menu-item-header">
                    <h3>Menu Item ${newIndex + 1}</h3>
                    <button type="button" class="btn btn-danger remove-menu-item">Remove</button>
                </div>
                <div class="menu-item-fields">
                    <div class="form-group">
                        <label>Text:</label>
                        <input type="text" name="menu[${newIndex}][text]" value="">
                    </div>
                    <div class="form-group">
                        <label>Icon:</label>
                        <div class="icon-picker-container">
                            <input type="text" name="menu[${newIndex}][icon]" value="" readonly class="icon-input">
                            <button type="button" class="btn btn-secondary select-icon-btn">Select Icon</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Type (link/modal):</label>
                        <input type="text" name="menu[${newIndex}][type]" value="link">
                    </div>
                    <div class="form-group">
                        <label>URL (for type 'link'):</label>
                        <input type="text" name="menu[${newIndex}][url]" value="#">
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Content (for type 'modal'):</label>
                        <input type="text" name="menu[${newIndex}][content]" value="">
                    </div>
                </div>
            `;
            menuContainer.appendChild(newItem);
            updateIndexes();
        });

        menuContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-menu-item')) {
                e.target.closest('.menu-item').remove();
                updateIndexes();
            }
        });

        const modal = document.getElementById('icon-modal');
        const closeModalBtn = modal.querySelector('.close-modal');
        const iconGrid = modal.querySelector('#icon-grid');
        const iconSearch = modal.querySelector('#icon-search');
        let currentTargetInput = null;

        document.querySelector('body').addEventListener('click', function(e) {
            if (e.target.classList.contains('select-icon-btn')) {
                currentTargetInput = e.target.previousElementSibling;
                modal.style.display = 'block';
                iconSearch.focus();
            }
        });

        const closeModal = () => {
            modal.style.display = 'none';
        }

        closeModalBtn.addEventListener('click', closeModal);
        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                closeModal();
            }
        });

        iconGrid.addEventListener('click', (e) => {
            const iconPreview = e.target.closest('.icon-preview');
            if (iconPreview) {
                const iconClass = iconPreview.dataset.iconClass;
                if (currentTargetInput) {
                    currentTargetInput.value = iconClass;
                }
                closeModal();
            }
        });

        iconSearch.addEventListener('keyup', () => {
            const filter = iconSearch.value.toLowerCase();
            const icons = iconGrid.querySelectorAll('.icon-preview');
            icons.forEach(iconDiv => {
                const iconClass = iconDiv.dataset.iconClass.toLowerCase();
                if (iconClass.includes(filter)) {
                    iconDiv.style.display = '';
                } else {
                    iconDiv.style.display = 'none';
                }
            });
        });

        updateIndexes();
    });
    </script>
</body>
</html>
