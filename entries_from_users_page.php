<?php
session_start();

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Check if the user is logged in
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: login_page.php");
    exit();
}

// Get the username from the session
$username = $_SESSION['user'];

// Get selected genre and order from the URL parameters
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : 'all';
$selectedOrder = isset($_GET['order']) ? $_GET['order'] : 'desc';

// Establish a database connection
$connection = mysqli_connect("localhost", "root", "", "afryka_blog");
if (!$connection) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

// Function to send email notification
function sendEmailNotification($username, $authorEmail, $nazwa, $opis, $gatunek, $waga, $obrazek_zawartosc, $obrazek_typ, $comment_text) {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email admin';
    $mail->Password = 'password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('email admin');
    $mail->addAddress($authorEmail);
    $mail->isHTML(true);
    $mail->Subject = "$username: skomentował Twój wpis";

    // Email body
    $mail->Body = "<p><strong>Użytkownik $username skomentował twój wpis</strong></p>".
                  "<p>Komentarz: $comment_text</p>".
                  "<p>Szczegóły wpisu:</p>".
                  "<ul>".
                  "<li>Nazwa: $nazwa</li>".
                  "<li>Opis: $opis</li>".
                  "<li>Gatunek: $gatunek</li>".
                  "<li>Waga: $waga kg</li>".
                  "</ul>".
                  "<p><strong>Zdjęcie wpisu:</strong></p>".

                  "<img src='cid:obrazek_wpisu' alt='Zdjęcie wpisu' style='max-width:100%;'>";

    // Attach image
    $mail->addStringAttachment($obrazek_zawartosc, "obrazek_wpisu.$obrazek_typ", 'base64', 'image/png');


                  
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    // Check if the user has enabled notifications
    $notification_query = "SELECT notification_turn FROM rejestracjatesy WHERE username = '$username'";
    $notification_result = mysqli_query($GLOBALS['connection'], $notification_query);

    if ($notification_result) {
        $row = mysqli_fetch_array($notification_result);
        $notification_turn = $row['notification_turn'];

        // Send email only if the user has enabled notifications
        if ($notification_turn) {
            $mail->send();
        }
    } else {
        // Handle notification query error
        echo "Błąd zapytania SQL: " . mysqli_error($GLOBALS['connection']);
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['entry_id'])) {
    $entry_id = $_POST['entry_id'];
    $comment_text = $_POST['comment'];

    // Prepare and execute SQL query to add a comment
    $insert_comment_query = "INSERT INTO komentarze_pod_wpisem (wpis_id, komentarz, author, data_dodania) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($connection, $insert_comment_query);
    mysqli_stmt_bind_param($stmt, "iss", $entry_id, $comment_text, $username);

    if (mysqli_stmt_execute($stmt)) {
        // Get author's email from the database
        $queryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = (SELECT author FROM wpisy WHERE id = ?)";
        $stmtAuthorEmail = mysqli_prepare($connection, $queryAuthorEmail);
        mysqli_stmt_bind_param($stmtAuthorEmail, "i", $entry_id);
        mysqli_stmt_execute($stmtAuthorEmail);
        mysqli_stmt_bind_result($stmtAuthorEmail, $authorEmail);
        mysqli_stmt_fetch($stmtAuthorEmail);
        mysqli_stmt_close($stmtAuthorEmail);

        // Get entry details
        $queryEntryDetails = "SELECT * FROM wpisy WHERE id = ?";
        $stmtEntryDetails = mysqli_prepare($connection, $queryEntryDetails);
        mysqli_stmt_bind_param($stmtEntryDetails, "i", $entry_id);
        mysqli_stmt_execute($stmtEntryDetails);
        $entry_details = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtEntryDetails));
        mysqli_stmt_close($stmtEntryDetails);

        // Send email notification after adding a comment
        sendEmailNotification($username, $authorEmail, $entry_details['nazwa'], $entry_details['opis'], $entry_details['gatunek'], $entry_details['waga'], $entry_details['obrazek'], $file_extension, $comment_text);

        $_SESSION['success'] = "Komentarz został dodany pomyślnie!";
    } else {
        $_SESSION['error'] = "Nie udało się dodać komentarza. Spróbuj ponownie później.";
    }

    header("Location: entries_from_users_page.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Blog o zwierzętach afrykańskich - wpisy od użytkowników</title>
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <!-- widok strony na urządzeniach mobilnych, co pomaga w dostosowaniu strony do różnych rozmiarów ekranów-->
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
    <!-- białe tło które zawiera pole do uzupełnienia-->
    <div class="container">
        <!-- będzie się odnosić się z dane które dany użytkownik się zalogował, będzie pokazać się jego nazwy username-->
        <h2>Witaj, <?php echo $username; ?></h2>
        <div class="form-group">
            <?php
            if (isset($_SESSION['success'])) {
                echo "<p><strong>{$_SESSION['success']}</strong></p>";
                unset($_SESSION['success']); // Usunięcie komunikatu o sukcesie z sesji
            } elseif (isset($_SESSION['error'])) {
                echo "<p><strong>{$_SESSION['error']}</strong></p>"; //pokazuje komunikat że nie zostało dodane, komunikat o formacie zdjęcia lub że plik graficzny jest duży
                unset($_SESSION['error']); // Usunięcie komunikatu o błędzie z sesji
            }
            ?>
        </div>
        <!-- formularz do wyboru filtrów -->
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
        // połączenie z bazą SQL
        $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
        if (!$connection) {
            die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
        }

        //zapytanie SQL na podstawie wybranych filtrów
        $query = "SELECT * FROM wpisy";
        if ($selectedGenre != 'all') {
            $query .= " WHERE gatunek = '$selectedGenre'";
        }
        $query .= " ORDER BY data_wyslania $selectedOrder";

        $result = mysqli_query($connection, $query);

        // sprawdza, czy zapytanie zwróciło wyniki i czy jest przynajmniej jeden wynik
        if ($result && mysqli_num_rows($result) > 0) {
            // while iteruje przez wyniki zapytania i wyświetla każdy wpis na stronie
            while ($row = mysqli_fetch_assoc($result)) {
                ///* białe tło które będą dodawane po dodaniu wpisu "entry"*/
                echo "<div class='entry'>";

                echo "<h3>Nazwa: {$row['nazwa']}</h3>";
                echo "<p>Opis: {$row['opis']}</p>";
                echo "<p>Gatunek: {$row['gatunek']}</p>";
                echo "<p>Waga: {$row['waga']} kg</p>";
                echo "<p>Autor: {$row['author']}</p>";
                echo "<p>Wysłano: {$row['data_wyslania']}</p>"; // Data i godzina wysłania
                echo "<p><strong>Zdjęcie:</strong></p>";
                echo "<img src='data:image/jpeg;base64," . base64_encode($row['obrazek']) . "' alt='Zdjęcie'>";

                $comments_query = "SELECT * FROM komentarze_pod_wpisem WHERE wpis_id = {$row['id']} ORDER BY data_dodania DESC";
                $comments_result = mysqli_query($connection, $comments_query);

                echo "<div class='form-group'>";

                echo "<form method='post' action='entries_from_users_page.php'>";
                echo "<input type='hidden' name='entry_id' value='{$row['id']}'>";
                echo "<textarea name='comment' id='opis'  rows='4' placeholder='Napisz komentarz' required></textarea>";
                echo "<input type='submit' value='Dodaj Komentarz'>";
                echo "</form>";
                echo "</div>";

                echo "<h4>Komentarze:</h4>";
                if ($comments_result && mysqli_num_rows($comments_result) > 0) {
                    while ($comment = mysqli_fetch_assoc($comments_result)) {
                        echo "<p><strong>{$comment['author']}</strong>: {$comment['komentarz']}</p>";
                    }
                } else {
                    echo "<p>Brak komentarzy.</p>";
                }
                echo "</div>";
                // Formularz do dodawania komentarza

                // Wyświetlenie komentarzy pod wpisem
            }
        } else {
            echo "<h2>Brak wpisów do wyświetlenia.</h2>";
        }
        ?>
    </div>
</body>
</html>
