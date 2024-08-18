<!--będzie rozpoczynać sesję php-->
<?php
	session_start();
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Rejestracja użytkownika</title>
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="CSS/responsive_create_account.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<div class="container">
    <h2>Rejestracja użytkownika</h2>
    <form action="add_user_to_database_CLASS_USER.php" method="post">
        <div class="form-group">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label >E-mail:</label>
            <input type="email" id="uzytkownik" name="email" required>
        </div>

        <div class="form-group">
            <label >Hasło:</label>
            <input type="password" id="passwordd" name="passwordd" required>
        </div>

        <div class="form-group">
        <div class="g-recaptcha" data-sitekey="data-sitekey"></div>
        </div>

        <div class="form-group">
            <input type="submit" value="Zarejestruj się">
        </div>
        <div class="form-group">
  <?php
 
  if (isset($_SESSION['error'])) {
                    echo "<p>{$_SESSION['error']}</p>";
                    unset($_SESSION['error']);
  }
  ?>
  </div>
    </form>
</div>
</body>
</html>