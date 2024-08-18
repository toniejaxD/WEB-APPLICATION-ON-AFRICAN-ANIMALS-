<?php
session_start();

//będzie wczytać klasy phpmailer które zostało wzięte kod, ktore jest odpowiedzialny za wysyłanie email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//te klasy PHPMAILER są odpowiedzialne za załadowanie klas
//exception odpowiedzialny jest za wysyłanie wyjątków/błędów które są obsługiwane
require 'PHPMailer/src/Exception.php';
//PHPMailer odpowiedzialny jest za konfihurowanie oraz wysyłanie wiadomości email
require 'PHPMailer/src/PHPMailer.php';
//SMPT odpowiedzialny jest za obsługi protokołu SMPT które jest używany do wysyłanie emaila przez serwery pocztowe
require 'PHPMailer/src/SMTP.php';

//funkcja które będzie generować ramdomowo nowe hasło
function generateRandomCode($length = 6) {
    $characters = '0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
//będzie sprawdzać do tego formularza czy zostało wysłane za pomocą metody POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    // połączenie z bazą
    $connection = mysqli_connect("localhost", "root", "", "afryka_blog");

    // będzie sprawdzać czy dany użytkownik o podanym email istnieje
    $query = "SELECT * FROM rejestracjatesy WHERE email LIKE '$email'";
    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) > 0) {
        //będzie sprawdzać czy dany użytkownik istnieje i będzie pobierać od niego informacje
        $row = mysqli_fetch_array($result);
        $username = $row['username'];

        // będzie generować nowy kod resetujący hasła
        $resetCode = generateRandomCode();

        // będzie aktualizować hasło w bazie
        $updateQuery = "UPDATE rejestracjatesy SET passwordd='$resetCode' WHERE email='$email'";
        mysqli_query($connection, $updateQuery);

        //będzie konfiguracjać i wysłać emaila z nowym kodem resetującym hasłem
        //będzie stworzyć nowy obiekt z klasy PHPMailer, parametr true będzie oznaczać że będzie zgłaszać wyjątki w jakiegoś błędu
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        //HOST adres serwera SMTP które zostało podane/generowane od googla.
        $mail->Host = 'smtp.gmail.com';
        //uwierzytelnianie SMTP
        $mail->SMTPAuth = true;
        //username oraz password: dane do uwierzytelniania na serwerze SMTP.
        $mail->Username = 'email admin';
        $mail->Password = 'password';
        //będzie się łączyć z serwerem poprzez protokołu ssl
        $mail->SMTPSecure = 'ssl';
        //numer portu, na którym działa serwer SMTP
        $mail->Port = 465;
        //adres nadawcy czyli mój email
        $mail->setFrom('email admin');
        //dodaje adres e-mail odbiorcy, który jest przekazywany z formularza
        $mail->addAddress($email);
        //treść które zostanie wysłane będzie w formacie HTML
        $mail->isHTML(true);
        //temat wiadomości
        $mail->Subject = "Afryka blog nowe hasło dla:  $username";
        //treść wiadomości
        $mail->Body = "Twoje hasło to:<strong> $resetCode</strong>";
        //kodowania znaku na UTF-8
        $mail->CharSet = 'UTF-8';
        //będzie ustawiać kodowanie treści z wiadomością
        $mail->Encoding = 'base64';
        //będzie ustawiać języka na polski
        $mail->setLanguage('pl');
        
        try {
            //wysyłanie emaila do danego użytkownika
            $mail->send();
            $_SESSION['success'] = "Nowe hasło zostało wysłane na twój adres e-mail!";
            header("Location: login_page.php");
        } catch (Exception $e) {
            //klasa Exception będzie wyłapać wyjątek czy błąd
            $_SESSION['error'] = "Błąd podczas wysyłania e-maila. Spróbuj ponownie później.";
            header("Location: login_page.php");
        }
    } else {
        //jeśli w bazie o takim emailu nie będzie w bazie to będzie pokazać komuniakt o braku użytkownika o podanym adresie email
        $_SESSION['error'] = "Brak użytkownika o podanym adresie e-mail.";
        header("Location: forgot_password_page.php");
    }
}
?>