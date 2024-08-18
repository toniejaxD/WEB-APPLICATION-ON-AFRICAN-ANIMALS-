<?php
session_start();

// sprawdza czy użytkownik jest zalogowany na swoim koncie
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: login_page.php");
    // przekierowuje użytkownika na stronę logowania jeśli się nie zalogował
    exit();
}

// Pobiera status notification_turn z bazy danych
$connection = mysqli_connect("localhost", "root", "", "afryka_blog");
if (!$connection) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

$username = $_SESSION['user'];
$queryNotificationTurn = "SELECT notification_turn FROM rejestracjatesy WHERE username = '$username'";
$resultNotificationTurn = mysqli_query($connection, $queryNotificationTurn);

if (!$resultNotificationTurn) {
    $_SESSION['error'] = "Błąd zapytania SQL: " . mysqli_error($connection);
    header("Location: change_data_user_page.php");
    exit();
}

$rowNotificationTurn = mysqli_fetch_array($resultNotificationTurn);
$current_notification_turn = $rowNotificationTurn['notification_turn'];

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Blog o zwierzętach afrykańskich</title>
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="StyleSheet" href="CSS/responsive_change_data_user.css">
    <!-- Dodajmy obsługę JavaScript -->
    <script>
        // Funkcja do zmiany tekstu na przycisku w zależności od statusu powiadomień
        function toggleNotificationButton() {
            var notificationButton = document.getElementById('notificationButton');
            if (notificationButton.value == 'Wyłącz powiadomienia') {
                notificationButton.value = 'Włącz powiadomienia';
            } else {
                notificationButton.value = 'Wyłącz powiadomienia';
            }
        }
    </script>
</head>
<body>
    <nav class="navbar"> 
        <ul class="menu">
            <li><a href="mainpage.html">Wyloguj się</a></li>
        </ul>
        <ul class="menu">
            <li><a href="mainpage_user.php">Powrót na główną stronę</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Zmień swoje dane</h2>
        <form action="change_password_CLASS_USER.php" method="post">
            <?php
            if (isset($_SESSION['success'])) {
                echo "<p>{$_SESSION['success']}</p>";
                unset($_SESSION['success']);
            }

            if (isset($_SESSION['error'])) {
                echo "<p>{$_SESSION['error']}</p>";
                unset($_SESSION['error']);
            }
            ?>
            <div class="form-group">
                <label for="current_password">Aktualne hasło</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nowe hasło</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Zapisz zmiany">
            </div>
        </form>
        </div>
        <div class="container">
        <!-- Dodajmy nowy formularz dla przycisku wyłączenia/włączenia powiadomień -->
        <form action="change_notification_turn.php" method="post">
            <div class="form-group">
                <!-- Dodajmy ID do przycisku, aby móc go łatwo znaleźć w JavaScript -->
                <input type="submit" id="notificationButton" name="notification_turn" value="<?php echo $current_notification_turn ? 'Wyłącz powiadomienia' : 'Włącz powiadomienia'; ?>" onclick="toggleNotificationButton()">
            </div>
        </form>
    </div>
</body>
</html>
