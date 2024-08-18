<?php
session_start();

// Sprawdź, czy użytkownik jest zalogowany jako administrator
if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    header("Location: login_page.php");
    exit();
}
//będzie sprawdzać czy zostało przesłane żądanie POST z identyfikatorem wpisu (entry_id).
if (isset($_POST['entry_id'])) {
    //będzie pobierać identyfikator wpisu przesłany w żądaniu POST i przypisuje go do zmiennej $entry_id
    $entry_id = $_POST['entry_id'];

    // Pobierz dane wpisu na podstawie $entry_id i wygeneruj formularz edycji
    $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
    if (!$connection) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }
    //zapytanie SQL, które pobiera dane konkretnego wpisu z tabeli wpisy na podstawie jego identyfikatora "entry_id"
    $query = "SELECT * FROM wpisy WHERE id = $entry_id";
    $result = mysqli_query($connection, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // widok formularza do które dany administrator będzie edytować
        echo "<!DOCTYPE html>";
        echo "<html lang='pl-PL'>";
        echo "<head>";
        echo "<title>Edytuj Wpis</title>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='keywords' content='HTML, CSS, JavaScript'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<link rel='stylesheet' href='CSS/responsive_user.css'>";
        echo "</head>";
        echo "<body>";
        echo "<nav class='navbar'>";
        echo "<ul class='menu'>";
        echo "<li><a href='mainpage_ADMIN.php'>Powrót na główną stronę</a></li>";
        echo "</ul>";
        echo "</nav>";
        echo "<div class='container'>";
        echo "<h2>Edytuj Wpis</h2>";
        //multipart/form-data jest używana, gdy formularz zawiera element <input type="file">, który umożliwia użytkownikowi wybieranie plików z lokalnego systemu i przesyłanie ich na serwer.
        echo "<form method='POST' action='update_entry_CLASS_ADMIN.php' enctype='multipart/form-data'>";
        // będzie dodawać pole ukryte, które przekazuje identyfikator wpisu do strony obsługującej aktualizację
        echo "<input type='hidden' name='entry_id' value='{$row['id']}'>";
        echo "<div class='form-group'>";
        echo "<label for='nazwa'>Nazwa zwierzęcia</label>";
        echo "<input type='text' id='nazwa' name='nazwa' value='{$row['nazwa']}' required>";
        echo "</div>";
        echo "<div class='form-group'>";
        echo "<label for='opis'>Opis treści</label>";
        echo "<textarea id='opis' name='opis' rows='4' required>{$row['opis']}</textarea>";
        echo "</div>";
        echo "<div class='form-group'>";
        echo "<label for='obrazek'>Zmień obrazek</label>";
        echo "<input type='file' id='obrazek' name='obrazek' accept='image/png, image/jpeg'>";
        echo "<img src='data:image/jpeg;base64," . base64_encode($row['obrazek']) . "' alt='Zdjęcie'>";
        echo "</div>";
        echo "<div class='form-group'>";
echo "<label for='gatunek'>Rodzaj gatunku zwierzęcia</label>";
echo "<select id='gatunek' name='gatunek' required>";
// Definiowanie dostępnych opcji
$options = array("Ssak", "Ptak", "Gad", "Płaz", "Ryba", "Stawonoga", "Inny");

// Iterowanie przez opcje i tworzenie elementów option
foreach ($options as $option) {
    // Sprawdzanie, czy opcja jest wybrana, i oznaczanie jej jako selected, jeśli jest zgodna z aktualnym gatunkiem
    $selected = ($option == $row['gatunek']) ? 'selected' : '';

    // Wyświetlanie opcji
    echo "<option value='$option' $selected>$option</option>";
}
echo "</select>";
echo "</div>";
        echo "<div class='form-group'>";
        echo "<label for='waga'>Waga (kg)</label>";
        echo "<input type='text' id='waga' name='waga' value='{$row['waga']}' pattern='[0-9]+(\.[0-9]+)?' title='Wprowadź liczbę' required>";
        echo "</div>";    
        echo "<div class='form-group'>";
        echo "<label for='reason'>Powód zmiany wpisu</label>";
        echo "<textarea id='reason' name='reason' rows='4' required></textarea>";
        echo "</div>";
        echo "<div class='form-group'>";
        echo "<input type='submit' name='submit' value='Zaktualizuj'>";
        echo "</div>"; 
        echo "</form>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
    } else {
        echo "<p>Nie znaleziono wpisu o podanym identyfikatorze.</p>";
    }

    mysqli_close($connection);
} else {
    echo "<p>Nieprawidłowe żądanie edycji.</p>";
}
?>
