<?php
//limit czasu oczekiwania na 300 sekund
set_time_limit(300);
session_start();


// Załaduj klasy PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Funkcja do wysyłania powiadomienia e-mail
function sendEmailNotification($username, $authorEmail, $nazwa, $opis, $gatunek, $waga, $obrazek_zawartosc, $obrazek_typ, $connection) {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email admin';
    $mail->Password = 'password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('email admin');

    $mail->addAddress($authorEmail); // Dodajemy e-mail autora wpisu
    $mail->isHTML(true);
    $mail->Subject = "$username: dodałeś wpis";

    // Treść e-maila z informacjami o dodanym wpisie
    $mail->Body = "<p>Dodałeś nowy wpis:</p>";
    $mail->Body .= "<p>Nazwa zwierzęcia: $nazwa</p>";
    $mail->Body .= "<p>Opis treści: $opis</p>";
    $mail->Body .= "<p>Rodzaj gatunku zwierzęcia: $gatunek</p>";
    $mail->Body .= "<p>Waga: $waga</p>";

    // Dodawanie zdjęcia do treści e-maila
    $mail->Body .= "<p>Zdjęcie:</p>";
    $mail->addStringAttachment($obrazek_zawartosc, "obrazek.$obrazek_typ");

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    // Sprawdź, czy użytkownik ma włączone powiadomienia
    $notification_query = "SELECT notification_turn FROM rejestracjatesy WHERE username = '$username'";
    $notification_result = mysqli_query($connection, $notification_query);

    if ($notification_result) {
        $row = mysqli_fetch_array($notification_result);
        $notification_turn = $row['notification_turn'];

        // Sprawdź, czy użytkownik ma włączone powiadomienia
        if ($notification_turn) {
            // Wysyłaj e-mail tylko jeśli użytkownik ma włączone powiadomienia
            $mail->send();
        }
    } else {
        // Obsługa błędu zapytania o powiadomienia
        echo "Błąd zapytania SQL: " . mysqli_error($connection);
    }
}

// Sprawdza czy użytkownik jest zalogowany
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
    header("Location: login_page.php");
    exit();
}

//będzie przypisać dane które dany użytkownik będzie wpisać swój wpis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];
    $gatunek = $_POST['gatunek'];
    $waga = $_POST['waga'];
    $username = $_SESSION['user'];
    // Dodatkowo, odczytaj autora z pola ukrytego 'author'
    $author = $_POST['author'];

    // sprawdza warunek z plikiem graficznym
    if ($_FILES['obrazek']['error'] === UPLOAD_ERR_OK) {
        $obrazek_tmp = $_FILES['obrazek']['tmp_name'];

        // sprawdza rozmiar przesyłanego pliku (limit na poziomie serwera PHP)
        $max_file_size = 2 * 1024 * 1024; // 10 megabajtów

        if ($_FILES['obrazek']['size'] <= $max_file_size) {
            // sprawdzenie rozszerzenie przesyłanego pliku że będzie sprawdzać czy jest plik png
            $allowed_extensions = ['png'];
            $file_extension = pathinfo($_FILES['obrazek']['name'], PATHINFO_EXTENSION);

            if (in_array($file_extension, $allowed_extensions)) {
                // odczytywanie zawartości pliku do zmiennej
                $obrazek_zawartosc = file_get_contents($obrazek_tmp);

                // połączeniem się z bazą danych
                $connection = mysqli_connect("localhost", "root", "", "afryka_blog");

                // sprawdzenie, czy połączenie zostało utracone, i ponownie je ustanów
                if (!$connection) {
                    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
                }

                // zapytanie SQL do dodania wpisu z obrazkiem jako BLOB
                $query = "INSERT INTO wpisy (nazwa, opis, obrazek, gatunek, waga, author, data_wyslania) 
                          VALUES (?, ?, ?, ?, ?, ?, NOW())";

                //będzie się wykonywać zapytanie SQL
                $stmt = mysqli_prepare($connection, $query);

                // przypisywanie zmienne które będzie się znajdować wpisie
                mysqli_stmt_bind_param($stmt, "ssssds", $nazwa, $opis, $obrazek_zawartosc, $gatunek, $waga, $author);

                // Sprawdza warunek z wpisem
                if (mysqli_stmt_execute($stmt)) {
                    // Pobierz e-mail autora wpisu z bazy danych
                    $queryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = ?";
                    $stmtAuthorEmail = mysqli_prepare($connection, $queryAuthorEmail);
                    mysqli_stmt_bind_param($stmtAuthorEmail, "s", $author);
                    mysqli_stmt_execute($stmtAuthorEmail);
                    mysqli_stmt_bind_result($stmtAuthorEmail, $authorEmail);
                    mysqli_stmt_fetch($stmtAuthorEmail);
                    mysqli_stmt_close($stmtAuthorEmail);

                    // Wysłanie powiadomienia e-mail po dodaniu wpisu
                    sendEmailNotification($username, $authorEmail, $nazwa, $opis, $gatunek, $waga, $obrazek_zawartosc, $file_extension, $connection);

                    $_SESSION['success'] = "Wpis został dodany pomyślnie!";
                    header("Location: mainpage_user.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Nie udało się dodać wpisu. Spróbuj ponownie później.";
                    header("Location: mainpage_user.php");
                    exit();
                }
            } else {
                // sprawdza czy dany plik które zawiera np jpg, będzie pokazać komunikat że przyjmuje tylko dane png z powodu że pojawiło się błąd z serwerem
                $_SESSION['error'] = "Akceptowane są tylko pliki PNG.";
                header("Location: mainpage_user.php");
                exit();
            }
        } else {
            // przekroczono limit rozmiaru przesyłanego pliku graficznego
            $_SESSION['error'] = "Zdjęcie jest za duże. Maksymalny rozmiar to 2 MB.";
            header("Location: mainpage_user.php");
            exit();
        }
    } else {
        // błąd przy przesyłaniu obrazka
        $_SESSION['error'] = "Błąd podczas przesyłania obrazka.";
        header("Location: mainpage_user.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Nieprawidłowe żądanie.";
    header("Location: mainpage_user.php");
    exit();
}
?>
