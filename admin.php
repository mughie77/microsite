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

// --- Function to handle file uploads ---
function handle_upload($file_key, $current_value) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "", basename($_FILES[$file_key]['name']));
        $targetPath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $targetPath)) {
            return $targetPath;
        }
    }
    return $current_value;
}

// --- Save Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_POST['title'])) {
    $old_config = json_decode(file_get_contents($configFile), true) ?? [];

    $new_config = [];
    $new_config['title'] = $_POST['title'] ?? '';

    $new_config['profile_picture'] = handle_upload('profile_picture_upload', $old_config['profile_picture'] ?? '');
    $new_config['background'] = handle_upload('background_upload', $old_config['background'] ?? '');

    // Save theme colors
    $new_config['colors'] = [
        'background' => $_POST['colors']['background'] ?? '#000000',
        'accent' => $_POST['colors']['accent'] ?? '#00faff',
    ];

    $new_config['menu'] = [];
    if (isset($_POST['menu']) && is_array($_POST['menu'])) {
        foreach ($_POST['menu'] as $menu_item_data) {
            if (empty($menu_item_data['text'])) continue;
            $item = [
                'text' => $menu_item_data['text'] ?? '',
                'icon' => $menu_item_data['icon'] ?? '',
                'type' => $menu_item_data['type'] ?? 'link',
                'url' => $menu_item_data['url'] ?? '#',
                'content' => $menu_item_data['content'] ?? '',
            ];
            $new_config['menu'][] = $item;
        }
    }

    file_put_contents($configFile, json_encode($new_config, JSON_PRETTY_PRINT));
    header('Location: admin.php?saved=true');
    exit;
}

// --- Load Config ---
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
}
// Set defaults for missing keys to avoid errors
$config = array_merge([
    'title' => 'My Microsite',
    'profile_picture' => '',
    'background' => 'images/background.jpg',
    'colors' => [
        'background' => '#000000',
        'accent' => '#00faff',
    ],
    'menu' => []
], $config);


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
            <form method="POST" action="admin.php" enctype="multipart/form-data">
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
                        <label for="profile_picture_upload">Profile Picture:</label>
                        <input type="file" id="profile_picture_upload" name="profile_picture_upload">
                        <?php if (!empty($config['profile_picture'])): ?>
                            <p>Current: <a href="<?php echo htmlspecialchars($config['profile_picture']); ?>" target="_blank"><?php echo htmlspecialchars($config['profile_picture']); ?></a></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="background_upload">Background Image:</label>
                        <input type="file" id="background_upload" name="background_upload">
                        <?php if (!empty($config['background'])): ?>
                            <p>Current: <a href="<?php echo htmlspecialchars($config['background']); ?>" target="_blank"><?php echo htmlspecialchars($config['background']); ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <h2>Theme Colors</h2>
                <div class="site-settings">
                     <div class="form-group">
                        <label for="color_background">Background Color:</label>
                        <input type="color" id="color_background" name="colors[background]" value="<?php echo htmlspecialchars($config['colors']['background']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="color_accent">Accent/Glow Color:</label>
                        <input type="color" id="color_accent" name="colors[accent]" value="<?php echo htmlspecialchars($config['colors']['accent']); ?>">
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
                                <div class="form-group"><label>Text:</label><input type="text" name="menu[<?php echo $index; ?>][text]" value="<?php echo htmlspecialchars($item['text']); ?>"></div>
                                <div class="form-group"><label>Icon:</label><div class="icon-picker-container"><input type="text" name="menu[<?php echo $index; ?>][icon]" value="<?php echo htmlspecialchars($item['icon'] ?? ''); ?>" readonly class="icon-input"><button type="button" class="btn btn-secondary select-icon-btn">Select Icon</button></div></div>
                                <div class="form-group"><label>Type (link/modal):</label><input type="text" name="menu[<?php echo $index; ?>][type]" value="<?php echo htmlspecialchars($item['type']); ?>"></div>
                                <div class="form-group"><label>URL (for type 'link'):</label><input type="text" name="menu[<?php echo $index; ?>][url]" value="<?php echo htmlspecialchars($item['url'] ?? ''); ?>"></div>
                                <div class="form-group" style="grid-column: span 2;"><label>Content (for type 'modal'):</label><input type="text" name="menu[<?php echo $index; ?>][content]" value="<?php echo htmlspecialchars($item['content'] ?? ''); ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="add-menu-item" class="btn btn-secondary" style="margin-top: 20px;">Add New Menu Item</button>
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </form>
            <div class="logout"><a href="?logout=true">Logout</a></div>
        <?php endif; ?>
    </div>

    <!-- Icon Picker Modal -->
    <div id="icon-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header"><h2>Select an Icon</h2><span class="close-modal">&times;</span></div>
            <div class="modal-body">
                <input type="text" id="icon-search" placeholder="Search for icons...">
                <div id="icon-grid">
                    <?php foreach($icons as $icon): ?>
                        <div class="icon-preview" data-icon-class="<?php echo $icon; ?>"><i class="<?php echo $icon; ?>"></i><span><?php echo $icon; ?></span></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    // JS remains the same
    document.addEventListener('DOMContentLoaded', function () {
        const menuContainer = document.getElementById('menu-items-container');
        const addButton = document.getElementById('add-menu-item');

        const updateIndexes = () => {
            const items = menuContainer.querySelectorAll('.menu-item');
            items.forEach((item, index) => {
                item.dataset.index = index;
                const header = item.querySelector('h3');
                if (header) header.textContent = `Menu Item ${index + 1}`;
                item.querySelectorAll('input, select').forEach(input => {
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
                <div class="menu-item-header"><h3>Menu Item ${newIndex + 1}</h3><button type="button" class="btn btn-danger remove-menu-item">Remove</button></div>
                <div class="menu-item-fields">
                    <div class="form-group"><label>Text:</label><input type="text" name="menu[${newIndex}][text]" value=""></div>
                    <div class="form-group"><label>Icon:</label><div class="icon-picker-container"><input type="text" name="menu[${newIndex}][icon]" value="" readonly class="icon-input"><button type="button" class="btn btn-secondary select-icon-btn">Select Icon</button></div></div>
                    <div class="form-group"><label>Type (link/modal):</label><input type="text" name="menu[${newIndex}][type]" value="link"></div>
                    <div class="form-group"><label>URL (for type 'link'):</label><input type="text" name="menu[${newIndex}][url]" value="#"></div>
                    <div class="form-group" style="grid-column: span 2;"><label>Content (for type 'modal'):</label><input type="text" name="menu[${newIndex}][content]" value=""></div>
                </div>
            `;
            menuContainer.appendChild(newItem);
            updateIndexes();
        });

        menuContainer.addEventListener('click', e => {
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

        document.body.addEventListener('click', e => {
            if (e.target.classList.contains('select-icon-btn')) {
                currentTargetInput = e.target.previousElementSibling;
                modal.style.display = 'block';
                iconSearch.focus();
            }
        });

        const closeModal = () => modal.style.display = 'none';
        closeModalBtn.addEventListener('click', closeModal);
        window.addEventListener('click', e => { if (e.target == modal) closeModal(); });

        iconGrid.addEventListener('click', e => {
            const iconPreview = e.target.closest('.icon-preview');
            if (iconPreview) {
                if (currentTargetInput) currentTargetInput.value = iconPreview.dataset.iconClass;
                closeModal();
            }
        });

        iconSearch.addEventListener('keyup', () => {
            const filter = iconSearch.value.toLowerCase();
            iconGrid.querySelectorAll('.icon-preview').forEach(div => {
                div.style.display = div.dataset.iconClass.toLowerCase().includes(filter) ? '' : 'none';
            });
        });
        updateIndexes();
    });
    </script>
</body>
</html>
