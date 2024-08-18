<?php
// Rozpoczęcie sesji
session_start();

// Załaduj klasy PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Funkcja do wysyłania powiadomienia e-mail
function sendEmailNotification($username, $user_email, $new_password, $connection) {
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email admin';
    $mail->Password = 'password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('email admin');

    $mail->addAddress($user_email); // Dodajemy e-mail autora wpisu
    $mail->isHTML(true);
    $mail->Subject = "$username: Zmiana hasła";
    $mail->Body = "<p>Twoje hasło zostało pomyślnie zmienione. Zmieniłeś nowe hasło na:<strong> $new_password</strong></p>";
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function ($str, $level) {
        file_put_contents('php://stderr', "[$level] $str");
    };

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
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: login_page.php");
    exit();
}

// Połączenie się z bazą danych
$connection = mysqli_connect("localhost", "root", "", "afryka_blog");

if (!$connection) {
    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
}

// Pobiera nowe i aktualne hasło z formularza
$new_password = $_POST['new_password'];
$current_password = $_POST['current_password'];

// Pobiera nazwę użytkownika, który jest zalogowany do swojego konta
$username = $_SESSION['user'];

// Sprawdza czy nowe hasło spełnia wymagania
if (!isStrongPassword($new_password)) {
    $passwordRequirements = "Hasło musi zawierać co najmniej 8 znaków, jedną małą literę, jedną wielką literę i jedną cyfrę.";

    if (strlen($new_password) < 8) {
        $passwordRequirements .= " Aktualna długość hasła: " . strlen($new_password) . " znaków (zbyt krótkie).";
    }

    if (!preg_match('/[a-z]/', $new_password)) {
        $passwordRequirements .= " Brak małej litery.";
    }

    if (!preg_match('/[A-Z]/', $new_password)) {
        $passwordRequirements .= " Brak wielkiej litery.";
    }

    if (!preg_match('/[0-9]/', $new_password)) {
        $passwordRequirements .= " Brak cyfry.";
    }

    $_SESSION['error'] = $passwordRequirements;
    header("Location: change_data_user_page.php");
    exit();
}

// Funkcja sprawdzająca siłę hasła
function isStrongPassword($new_password) {
    $length = strlen($new_password);

    if ($length < 8) {
        return false;
    }

    if (!preg_match('/[a-z]/', $new_password)) {
        return false;
    }

    if (!preg_match('/[A-Z]/', $new_password)) {
        return false;
    }

    if (!preg_match('/[0-9]/', $new_password)) {
        return false;
    }

    return true;
}

// Zapytanie SQL, które pobiera hasło użytkownika na podstawie nazwy użytkownika
$query = "SELECT passwordd FROM rejestracjatesy WHERE username = '$username'";
$result = mysqli_query($connection, $query);

if (!$result) {
    $_SESSION['error'] = "Błąd zapytania SQL: " . mysqli_error($connection);
    header("Location: change_password_CLASS_USER.php");
    exit();
}

// Sprawdza, czy zapytanie zwróciło dokładnie jeden wynik dla danego użytkownika z hasłem
if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_array($result);
    $stored_password = $row['passwordd'];

    // Sprawdza, czy aktualne hasło jest poprawne
    if ($current_password === $stored_password) {
        // Zapytanie, które będzie aktualizować hasło użytkownika w bazie danych
        $update_query = "UPDATE rejestracjatesy SET passwordd = '$new_password' WHERE username = '$username'";

        // Sprawdzenie warunku, czy aktualizacja hasła się powiodła
        if (mysqli_query($connection, $update_query)) {
            $_SESSION['success'] = "Hasło zostało pomyślnie zmienione!";

            // Pobierz e-mail autora wpisu z bazy danych
            $queryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = ?";
            $stmtAuthorEmail = mysqli_prepare($connection, $queryAuthorEmail);
            mysqli_stmt_bind_param($stmtAuthorEmail, "s", $username);
            mysqli_stmt_execute($stmtAuthorEmail);
            mysqli_stmt_bind_result($stmtAuthorEmail, $user_email);
            mysqli_stmt_fetch($stmtAuthorEmail);
            mysqli_stmt_close($stmtAuthorEmail);

            // Wysłanie powiadomienia e-mail po zmianie hasła
            sendEmailNotification($username, $user_email, $new_password, $connection);

        } else {
            $_SESSION['error'] = "Nie udało się zmienić hasła: " . mysqli_error($connection);
        }
    } else {
        $_SESSION['error'] = "Podane aktualne hasło jest niepoprawne.";
    }
} else {
    $_SESSION['error'] = "Nie znaleziono użytkownika w bazie danych.";
}

// Zamyka połączenie z bazą danych
mysqli_close($connection);

// Przekierowanie na stronę zmiany danych użytkownika
header("Location: change_data_user_page.php");
exit();
?>
