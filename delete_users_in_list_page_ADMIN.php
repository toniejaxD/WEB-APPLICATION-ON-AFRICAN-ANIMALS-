<?php
session_start();

// Sprawdź, czy administrator jest zalogowany do swojego konta
if (!isset($_SESSION['admin']) || $_SESSION['admin'] == "") {
    header("Location: login_page.php");
    exit();
}
$username = $_SESSION['admin'];

// Pobierz identyfikator wpisu do usunięcia
$entry_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';

?>
<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Strona Administratora</title>
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/responsive_ADMIN.css">
</head>
<body>
    <!--zawiera punkt nawigacyjny do innej strony-->
    <nav class="navbar">
        <ul class="menu">
            <li><a href="mainpage.html">Wyloguj się</a></li>
        </ul>
        <ul class="menu">
            <li><a href="mainpage_ADMIN.php">Powrót na główną stronę</a></li>
        </ul>     
    </nav>
    <!--białe tło które zawiera dany wpisy od użytkowników-->
    <div class="entry">
        <h2>Czy na pewno chcesz usunąć użytkownika?</h2>
        <form method="POST" action="delete_entry_CLASS_ADMIN.php">
    <input type="hidden" name="user_id" value="<?php echo $_POST['user_id']; ?>">
    <input type="hidden" name="delete_user" value="<?php echo $_POST['delete_user']; ?>">
    <label for="reason">Podaj powód usunięcia:</label>
    <textarea id="reason" name="reason" rows="4" required></textarea>
    
    <div class='button-group'>
        <input type="submit" name="confirm_delete" value="Potwierdź Usunięcie">
    </div>
</form>
    </div>
</body>
</html>