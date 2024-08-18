<?php
session_start();
$username = $_POST["username"];
$email = $_POST["email"];
$password = $_POST['passwordd'];
$user_role = 'user';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// sprawdzanie siły danego hasła dla użytkownika
if (!isStrongPassword($password)) {
    $passwordRequirements = "Hasło musi zawierać co najmniej 8 znaków, jedną małą literę, jedną wielką literę i jedną cyfrę.";

    if (strlen($password) < 8) {
        $passwordRequirements .= " Aktualna długość hasła: " . strlen($password) . " znaków (zbyt krótkie).";
    }

    if (!preg_match('/[a-z]/', $password)) {
        $passwordRequirements .= " Brak małej litery.";
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $passwordRequirements .= " Brak wielkiej litery.";
    }

    if (!preg_match('/[0-9]/', $password)) {
        $passwordRequirements .= " Brak cyfry.";
    }

    $_SESSION['error'] = $passwordRequirements;
    header("Location: create_account_page.php");
    exit();
}

function isStrongPassword($password) {
    //funkcja isStrongPassword sprawdza teraz siły hasła na podstawie kilku kryteriów
    $length = strlen($password);

    // sprawdza czy hasło ma co najmniej 8 znaków
    if ($length < 8) {
        return false;
    }

    // sprawdza czy hasło zawiera co najmniej jedną małą literę
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }

    // sprawdza czy hasło zawiera co najmniej jedną wielką literę
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // sprawdza czy hasło zawiera co najmniej jedną cyfrę
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }

    return true;
}

// Sprawdzanie reCAPTCHA
if (isset($_POST['g-recaptcha-response'])) {
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $secret_key = "SECRET-KEY";
    $check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_response);
    $answer = json_decode($check);

    if (!$answer->success) {
        $_SESSION['error'] = "Proszę potwierdzić, że jesteś człowiekiem.";
        header("Location: create_account_page.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Proszę potwierdzić, że jesteś człowiekiem.";
    header("Location: create_account_page.php");
    exit();
}

//tutaj będzie wczytywać z rejestracji nasze dane
$connection = mysqli_connect("localhost", "root", "", "afryka_blog");

$check_query = "SELECT * FROM rejestracjatesy WHERE username='$username'";
$result = mysqli_query($connection, $check_query);

if (mysqli_num_rows($result) > 0) {
    // Użytkownik o podanej nazwie już istnieje
    $_SESSION['error'] = "Użytkownik o podanej nazwie '$username' już istnieje. Wybierz inną nazwę użytkownika.";
    header("Location: create_account_page.php");
    exit();
}

//będzie sprawdzać czy istnieje użytkownik o podanym adresie e-mail
$check_email_query = "SELECT * FROM rejestracjatesy WHERE email='$email'";
$result_email = mysqli_query($connection, $check_email_query);

if (mysqli_num_rows($result_email) > 0) {
    // jeśli użytkownik o podanym adresie e-mail już istnieje to będzie pokazać komunikat
    $_SESSION['error'] = "Użytkownik z adresem e-mail '$email' już istnieje. Wybierz inny adres e-mail.";
    header("Location: create_account_page.php");
    exit();
}



//będzie dodawać nowego użytkownik który będzie się rejestrować oraz za pomocą user_role będzie miał ustawioną automatycznie jako 'user' żeby podczas logowanie będzie sprawdzać czy to jest użytkownik czy administrator
$add_user_query = "INSERT INTO rejestracjatesy (id, username, passwordd, email, user_role) VALUES (NULL, '$username', '$password', '$email', '$user_role')";
if (mysqli_query($connection, $add_user_query)) {
    $_SESSION['user'] = $username;

    // Funkcja do wysyłania powiadomienia e-mail po zalogowaniu
    function sendLoginNotification($user_email, $username) {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'email admin';
        $mail->Password = 'password';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom('email admin');

        $mail->addAddress($user_email);
        $mail->isHTML(true);
        $mail->Subject = "Witamy nowego użytkownika - $username";
        $mail->Body = "<p><strong>Witamy na naszą stronę Afryka Blog</p></strong>";
        $mail->Body .= "<p>Twoja nazwa użytkownika: $username<p>";
        $mail->Body .= "<p>Twój email: $user_email</p>";

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->setLanguage('pl');

        $mail->send();
    }

    sendLoginNotification($email, $username);

    header("Location: mainpage_user.php");
} else {
    $_SESSION['error'] = "Błąd podczas dodawania użytkownika.";
    header("Location: create_account_page.php");
    exit();
}
?>
