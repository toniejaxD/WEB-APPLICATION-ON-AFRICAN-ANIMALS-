<?php
session_start();

// sprawdza czy użytkownik jest zalogowany na swoim koncie
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: login_page.php");
    // przekierowuje użytkownika na stronę logowania jeśli się nie zalogował
    exit();
}
//przechowuje nazwę zalogowanego użytkownika
$username = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Blog o zwierzętach afrykańskich</title>
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
            <li><a href="entries_from_users_page.php">Zobacz wpisy</a></li>
        </ul>
        <ul class="menu">
            <li><a href="history_in_entry_page_user.php">Historia wpisu</a></li>
        </ul>
        <ul class="menu">
            <li><a href="animal_search_page.php">Mapa Afryki</a></li>
        </ul>
        <ul class="menu">
            <li><a href="change_data_user_page.php">Edytuj konto</a></li>
        </ul>
    </nav>
    <!--białe tło które zawiera pole do uzupełnienia-->
    <div class="container">
        <!--będzie się odnosić się z dane które dany użytkownik się zalogował, będzie pokazać się jego nazwy username-->
        <h2>Witaj, <?php echo $username; ?></h2>
        
        <form action="adding_a_user_entry_CLASS_USER.php" method="post" enctype="multipart/form-data">
            <!--/*białe które dany użytkownik będzie wpisać swoje dane*/-->
            <div class="form-group">
                <label for="nazwa">Nazwa zwierzęcia</label>
                <input type="text" id="nazwa" name="nazwa" required>
            </div>
            <div class="form-group">
                <label for="opis">Opis treści</label>
                <textarea id="opis" name="opis" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="obrazek">Dodaj obrazek</label>
                <input type="file" id="obrazek" name="obrazek" accept="image/png, image/jpeg" required>
            </div>
            <div class="form-group">
                    <label for="gatunek">Rodzaj gatunku zwierzęcia</label>
                    <select id="gatunek" name="gatunek" required>
                       <option value="Ssak">Ssak</option>
                       <option value="Ptak">Ptak</option>
                       <option value="Gad">Gad</option>
                       <option value="Płaz">Płaz</option>
                       <option value="Ryba">Ryba</option>
                       <option value="Stawonoga">Stawonoga</option>
                       <option value="Inny">Inny</option>
    
                     </select>
                
            </div>
            <div class="form-group">
                <label for="waga">Waga (kg)</label>
                <input type="text" id="waga" name="waga" pattern="[0-9]+(\.[0-9]+)?" title="Wprowadź liczbę" required>
            </div>
            <input type="hidden" name="author" value="<?php echo $username; ?>"> <!-- Dodane pole ukryte -->
            <!--author, które przechowuje nazwę zalogowanego użytkownika jako autora wpisu oraz jest ukryty które odpowiada za hidden-->
            <div class="form-group">
                <input type="submit" value="Dodaj">
            </div>
            <div class="form-group">
                <?php
                // wyświetla się  komunikat o sukcesie lub błędzie
                if (isset($_SESSION['success'])) {
                    echo "<p>{$_SESSION['success']}</p>";
                    unset($_SESSION['success']); // Usunięcie komunikatu o sukcesie z sesji
                } elseif (isset($_SESSION['error'])) {
                    echo "<p>{$_SESSION['error']}</p>";//pokazuje komunikat że nie zostało dodane, komunikat o formacie zdjęcia lub że plik graficzny jest duży
                    unset($_SESSION['error']); // Usunięcie komunikatu o błędzie z sesji
                }
                ?>
            </div>
        </form>
    </div>
</body>
</html>
