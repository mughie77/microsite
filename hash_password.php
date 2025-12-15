<?php
if (isset($_GET['password'])) {
    $password = $_GET['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Password: " . htmlspecialchars($password) . "<br>";
    echo "Hash: " . $hash;
} else {
?>
    <form method="get">
        <label for="password">Enter password to hash:</label>
        <input type="text" id="password" name="password">
        <button type="submit">Generate Hash</button>
    </form>
<?php
}
?>
