
<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="CSS/responsive_create_account.css">
    <title>Przypomnienie hasła</title>
</head>
<body>
    <div class="container">
        <h2>Przypomnienie hasła</h2>
        <form action="forgot_password.php" method="post">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="text" id="email" name="email" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Przypomnij hasło">
            </div>
        </form>
        <?php
        session_start();
        // Wyświetl komunikaty o sukcesie lub błędzie
        if (isset($_SESSION['success'])) {
            echo "<p>{$_SESSION['success']}</p>";
            unset($_SESSION['success']);
        } elseif (isset($_SESSION['error'])) {
            echo "<p>{$_SESSION['error']}</p>";
            unset($_SESSION['error']);
        }
        ?>
    </div>
</body>
</html>