<?php
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: login_page.php");
    exit();
}

// Pobranie nazwy użytkownika z sesji
$username = $_SESSION['user'];

// Pobranie wybranego gatunku i sortowania z parametrów URL
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : 'all';
$selectedOrder = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Połączenie z bazą danych
$connection = mysqli_connect("localhost", "root", "", "afryka_blog");
if (!$connection) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}


?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Blog o zwierzętach afrykańskich - wpisy od użytkowników</title>
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/responsive_user.css">
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
        <h2>Witaj, <?php echo $username; ?></h2>
        <div class="form-group">
            <?php
            if (isset($_SESSION['success'])) {
                echo "<p><strong>{$_SESSION['success']}</strong></p>";
                unset($_SESSION['success']);
            } elseif (isset($_SESSION['error'])) {
                echo "<p><strong>{$_SESSION['error']}</strong></p>";
                unset($_SESSION['error']);
            }
            ?>
        </div>

        <form>
            <div class="form-group">
                <label for="genre">Wybierz gatunek:</label>
                <select name="genre" id="genre">
                    <option value="all" <?php echo ($selectedGenre == 'all') ? 'selected' : ''; ?>>Wszystkie</option>
                    <option value="Ssak" <?php echo ($selectedGenre == 'Ssak') ? 'selected' : ''; ?>>Ssak</option>
                    <option value="Ptak" <?php echo ($selectedGenre == 'Ptak') ? 'selected' : ''; ?>>Ptak</option>
                    <option value="Gad" <?php echo ($selectedGenre == 'Gad') ? 'selected' : ''; ?>>Gad</option>
                    <option value="Płaz" <?php echo ($selectedGenre == 'Płaz') ? 'selected' : ''; ?>>Płaz</option>
                    <option value="Ryba" <?php echo ($selectedGenre == 'Ryba') ? 'selected' : ''; ?>>Ryba</option>
                    <option value="Stawonoga" <?php echo ($selectedGenre == 'Stawonoga') ? 'selected' : ''; ?>>Stawonoga</option>
                    <option value="Inny" <?php echo ($selectedGenre == 'Inny') ? 'selected' : ''; ?>>Inny</option>
                    <!-- Dodaj więcej opcji dla innych gatunków -->
                </select>

                <label for="order">Sortuj według daty:</label>
                <select name="order" id="order">
                    <option value="desc" <?php echo ($selectedOrder == 'desc') ? 'selected' : ''; ?>>Najnowsze</option>
                    <option value="asc" <?php echo ($selectedOrder == 'asc') ? 'selected' : ''; ?>>Najstarsze</option>
                </select>
            </div>

            <div class="form-group">
                <input type="submit" value="Filtruj">
            </div>
        </form>

        <?php
        // Połączenie z bazą danych SQL
        $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
        if (!$connection) {
            die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
        }

        // Zapytanie SQL na podstawie wybranych filtrów
        // Zapytanie SQL na podstawie wybranych filtrów i autora
        $username = mysqli_real_escape_string($connection, $username);

        $query = "SELECT * FROM wpisy WHERE author = '$username'";
if ($selectedGenre != 'all') {
    $query .= " AND gatunek = '$selectedGenre'";
}
$query .= " ORDER BY data_wyslania $selectedOrder";

        $result = mysqli_query($connection, $query);

        // Sprawdzenie, czy zapytanie zwróciło wyniki i czy jest przynajmniej jeden wynik
        if ($result && mysqli_num_rows($result) > 0) {
            // while iteruje przez wyniki zapytania i wyświetla każdy wpis na stronie
            while ($row = mysqli_fetch_assoc($result)) {
                // Białe tło, które będzie dodawane po dodaniu wpisu "entry"
                echo "<div class='entry'>";
                echo "<h3>Nazwa: {$row['nazwa']}</h3>";
                echo "<p>Opis: {$row['opis']}</p>";
                echo "<p>Gatunek: {$row['gatunek']}</p>";
                echo "<p>Waga: {$row['waga']} kg</p>";
                echo "<p>Autor: {$row['author']}</p>";
                echo "<p>Wysłano: {$row['data_wyslania']}</p>"; // Data i godzina wysłania
                echo "<p><strong>Zdjęcie:</strong></p>";
                echo "<img src='data:image/jpeg;base64," . base64_encode($row['obrazek']) . "' alt='Zdjęcie'>";
                
                

                echo "<h4>Komentarze:</h4>";
                $comments_query = "SELECT * FROM komentarze_pod_wpisem WHERE wpis_id = {$row['id']} ORDER BY data_dodania DESC";
                $comments_result = mysqli_query($connection, $comments_query);

                echo "<input type='hidden' name='entry_id' value='{$row['id']}'>";

                if ($comments_result && mysqli_num_rows($comments_result) > 0) {
                    while ($comment = mysqli_fetch_assoc($comments_result)) {
                        echo "<p><strong>{$comment['author']}</strong>: {$comment['komentarz']}</p>";
                    }
                } else {
                    echo "<p>Brak komentarzy.</p>";
                }

                // Wewnątrz pętli while, w której są wyświetlane wpisy
echo "<form method='post' action='send_message_to_ADMIN_history_page_user.php'>";
echo "<input type='hidden' name='entry_id' value='{$row['id']}'>";
echo "<textarea name='message_ADMIN' id='opis'  rows='4' placeholder='Napisz wiadomość o zmiany wpisu do administratora' required></textarea>";
echo "<input type='submit' value='Wyślij wiadomość do administratora'>";
echo "</form>"; // Formularz do dodawania komentarza
                echo "</div>";
            }
        } else {
            echo "<h2>Brak wpisów do wyświetlenia.</h2>";
        }
        ?>
    </div>
</body>
</html>
