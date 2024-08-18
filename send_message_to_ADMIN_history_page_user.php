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

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendEmailNotification($username, $authorEmail, $adminEmail, $nazwa, $opis, $gatunek, $waga, $obrazek_zawartosc, $obrazek_typ, $message_ADMIN) {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email admin';
    $mail->Password = 'password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('email admin');
    $mail->isHTML(true);
    $mail->Subject = "$username: prośba o zmianę wpisu";

    // Sprawdzenie poprawności adresu e-mail odbiorcy
    if (filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
        $mail->addAddress($authorEmail); // Dodaj prawidłowy adres e-mail odbiorcy
    } else {
        // Jeśli adres e-mail jest nieprawidłowy, rzuć wyjątek
        throw new Exception('Nieprawidłowy adres e-mail odbiorcy');
    }

    // Treść e-maila
    $mail->Body = "<p><strong>Użytkownik $username prosi o zmianę wpisu</strong></p>".
                  "<p>Wiadomość: $message_ADMIN</p>".
                  "<p>Szczegóły wpisu:</p>".
                  "<ul>".
                  "<li>Nazwa: $nazwa</li>".
                  "<li>Opis: $opis</li>".
                  "<li>Gatunek: $gatunek</li>".
                  "<li>Waga: $waga kg</li>".
                  "</ul>".
                  "<p><strong>Zdjęcie wpisu:</strong></p>".
                  "<img src='cid:obrazek_wpisu' alt='Zdjęcie wpisu' style='max-width:100%;'>";

    // Załącznik - obrazek
    $mail->addStringAttachment($obrazek_zawartosc, "obrazek_wpisu.$obrazek_typ", 'base64', 'image/png');

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    try {
        $mail->send(); // Spróbuj wysłać e-mail
    } catch (Exception $e) {
        throw new Exception('Błąd podczas wysyłania e-maila: ' . $mail->ErrorInfo);
    }
}
// Sprawdzenie, czy formularz został wysłany
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_ADMIN']) && isset($_POST['entry_id'])) {
    // Obsługa formularza
    $entryId = $_POST['entry_id'];
    $message_ADMIN = $_POST['message_ADMIN'];

    // Fetching additional information before sending the email
    $queryEntryInfo = "SELECT * FROM wpisy WHERE id = $entryId";
    $resultEntryInfo = mysqli_query($connection, $queryEntryInfo);

    if ($resultEntryInfo && mysqli_num_rows($resultEntryInfo) > 0) {
        $row = mysqli_fetch_assoc($resultEntryInfo);

        $nazwa = $row['nazwa'];
        $opis = $row['opis'];
        $gatunek = $row['gatunek'];
        $waga = $row['waga'];
        $obrazek_zawartosc = $row['obrazek'];
        $file_extension = 'png';  // assuming it's a constant value in this context

        // Fetching author email from the database
        $queryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = ?";
        $stmtAuthorEmail = mysqli_prepare($connection, $queryAuthorEmail);
        mysqli_stmt_bind_param($stmtAuthorEmail, "s", $row['author']);
        mysqli_stmt_execute($stmtAuthorEmail);
        mysqli_stmt_bind_result($stmtAuthorEmail, $authorEmail);
        mysqli_stmt_fetch($stmtAuthorEmail);
        mysqli_stmt_close($stmtAuthorEmail);
        sendEmailNotification($username, 'email admin', $authorEmail, $nazwa, $opis, $gatunek, $waga, $obrazek_zawartosc, $file_extension, $message_ADMIN);
        $_SESSION['success'] = "Wiadomość została wysłana!";
        header("Location: history_in_entry_page_user.php");
        exit();
    }
}

?>