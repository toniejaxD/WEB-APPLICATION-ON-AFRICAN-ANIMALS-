<!--będzie rozpoczynać sesję php-->
<?php
	session_start();

	unset($_SESSION['user']);
  unset($_SESSION['admin']);
  unset($_SESSION['role_user']);
  //czasie sesji usuwa zmienne, odpowiedzialne są za przechowywanie informacji o zalogowanym użytkowników czy admininstratora

?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="CSS/responsive_create_account.css">
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

</head>
<body>
  <!--/* białe tło które zawiera pole do uzupełnienia dane logowanie cz zarejestrowanie do klasy container-->
    <div class="container">
        <h2>Logowanie</h2>
<!--za pomocą form action... edzie się rozpocząć formularz, które będą przesyłane do pliku logging_CLASS-->
<form action="logging_CLASS.php" method="post">
  <!--białe które dany użytkownik będzie wpisać swoje dane-->
    <div class="form-group">
  <label for="email">E-mail:</label>
  <input type="text" id="uzytkownik" name="email" required>
  </div>
  <div class="form-group">
  <label for="passwordd">Hasło:</label>
  <input type="password" id="passwordd" name="passwordd" required>
  </div>
  <div class="form-group">
 
  <div class="g-recaptcha" data-sitekey="DATA SITEKEY">

  </div>

  </div>
  <div class="form-group">
  <input type="submit" value="Zaloguj się">
  </div>
  <div class="form-group">
    <a href="forgot_password_page.php">Zapomniałeś hasła?</a>
</div>
  <div class="form-group">
  <?php
  //sprawdza czy użytkownik dobrze wpisał swoje dane do logowanie, jęsli napisał źle to pojawi się komunikat o błędzie z logowaniem
  if (isset($_SESSION['success'])) {
    echo "<p>{$_SESSION['success']}</p>";
    unset($_SESSION['success']);
} elseif (isset($_SESSION['error'])) {
    echo "<p>{$_SESSION['error']}</p>";
    unset($_SESSION['error']);
}
  ?>
  </div>
</form>
</div>

</body>
</html>