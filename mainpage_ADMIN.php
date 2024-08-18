<?php
session_start();

// Sprawdź, czy administrator jest zalogowany do swojego konta
if (!isset($_SESSION['admin']) || $_SESSION['admin'] == "") {
    header("Location: login_page.php");
    exit();
}
$username = $_SESSION['admin'];

$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : 'all';
$selectedOrder = isset($_GET['order']) ? $_GET['order'] : 'desc';
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
            <li><a href="list_a_users_ADMIN_page.php">Lista użytkowników</a></li>
        </ul>     
    </nav>
    <!--białe tło które zawiera dany wpisy od użytkowników-->
    <div class="container">
        <!--będzie się odnosić się z dane które dany administrator się zalogował, będzie pokazać się jego nazwy username-->
        <h2>Witaj, <?php echo $username; ?></h2>
        <?php
        //będzie się pokazać komunikat czy dany wpis zostało zmienione czy nie
        if (isset($_SESSION['error'])) {
            echo "<h2><p class='error'>{$_SESSION['error']}</p></h2>";
            unset($_SESSION['error']);
        }elseif (isset($_SESSION['success'])) {
            echo "<h2><p>{$_SESSION['success']}</p></h2>";
            unset($_SESSION['success']);
        }
        ?>
        <!-- formularz do wyboru filtrów -->
        <form>
        <div class="entry">
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
            <p>
            <div class="button-group">
                <input type="submit" value="Filtruj">
            </div>
    </p>
            </div>
        </form>
        <?php
        $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
        if (!$connection) {
            die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
        }
        //pobieranie z tabeli wpisy
        $query = "SELECT * FROM wpisy";
        //zapytanie SQL na podstawie wybranych filtrów

        if ($selectedGenre != 'all') {
            $query .= " WHERE gatunek = '$selectedGenre'";
        }
        $query .= " ORDER BY data_wyslania $selectedOrder";



        $result = mysqli_query($connection, $query);
        //sprawdza, czy zapytanie zwróciło wyniki i czy jest przynajmniej jeden wynik
        if ($result && mysqli_num_rows($result) > 0) {
            //while iteruje przez wyniki zapytania i wyświetla każdy wpis na stronie
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<div class='entry'>";
                echo "<h3>Nazwa: {$row['nazwa']}</h3>";
                echo "<p>Opis: {$row['opis']}</p>";
                echo "<p>Gatunek: {$row['gatunek']}</p>";
                echo "<p>Waga: {$row['waga']} kg</p>";
                echo "<p>Autor: {$row['author']}</p>";
                echo "<p>Wysłano: {$row['data_wyslania']}</p>"; // Data i godzina wysłania
                echo "<p><strong>Zdjęcie:</strong></p>";
                echo "<img src='data:image/jpeg;base64," . base64_encode($row['obrazek']) . "' alt='Zdjęcie'>";
                // przycisk do edytowanie wpisu
                echo "<div class='button-group'>";
                echo "<form method='POST' action='edit_entry_page_ADMIN.php'>";
                //ukryte pole, które przekazuje identyfikator wpisu do edycji czyli ten "entry_id"
                echo "<input type='hidden' name='entry_id' value='{$row['id']}'>";
                echo "<input type='submit' name='edit' value='Edytuj'>";
                echo "</form>";
                // przycisk do usuwanie wpisu
                echo "<form method='POST' action='delete_confirmation_in_entry_page.php'>";
                echo "<input type='hidden' name='entry_id' value='{$row['id']}'>";
                echo "<input type='submit' name='delete' value='Usuń'>";
                echo "</form>";
                echo "</div>";
                

                 // Wyświetlanie komentarzy pod wpisem
                 $comments_query = "SELECT * FROM komentarze_pod_wpisem WHERE wpis_id = {$row['id']} ORDER BY data_dodania DESC";
                 $comments_result = mysqli_query($connection, $comments_query);
 
                 echo "<div class='entry'>";
                 if ($comments_result && mysqli_num_rows($comments_result) > 0) {
                     echo "<h4>Komentarze:</h4>";
                     while ($comment = mysqli_fetch_assoc($comments_result)) {
                       
                         echo "<strong>{$comment['author']}</strong>: {$comment['komentarz']} ";
                         echo "<form method='POST' action='delete_comments_in_entry_page.php'>";
                         echo "<input type='hidden' name='comment_id' value='{$comment['id']}'>";
                         echo "<div class='button-group'>";
                         echo "<input type='submit' name='delete_comment' value='Usuń komentarz'>";
                         echo "</div>";
                         echo "</form>";
                        
                        
                     }
                 } else {
                     echo "<p><strong>Brak komentarzy.</p></strong>";
                 }
                 echo "</div>";
                
                echo "</div>";
        
                
            }
        } else {
            echo "<p><strong>Brak wpisów do wyświetlenia.</p></strong>";
        }
        mysqli_close($connection);
        ?>
    </div>

</body>
</html>