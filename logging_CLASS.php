<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$secret_key = "SECRET KEY";

if (isset($_POST['g-recaptcha-response'])) {
    $recaptcha_response = $_POST['g-recaptcha-response'];
    $check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret_key . '&response=' . $recaptcha_response);
    $answer = json_decode($check);

    if ($answer->success) {
        // reCAPTCHA verification passed
        $email = $_POST["email"];
        $entered_password = $_POST["passwordd"];
        $user_role = isset($_POST["user_role"]) ? $_POST["user_role"] : '';

        $connection = mysqli_connect("localhost", "root", "", "afryka_blog");

        if (!$connection) {
            die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
        }

        $query = "SELECT * FROM rejestracjatesy WHERE email LIKE '$email' AND passwordd LIKE '$entered_password'";
        $result = mysqli_query($connection, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);

            $_SESSION['role_user'] = $row['user_role'];

            if ($_SESSION['role_user'] == 'admin') {
                $_SESSION['admin'] = $row['username'];
                $username = $row['username'];
                $user_email = $row['email'];
                sendLoginNotification($username, $user_email, $connection);
                header("Location: mainpage_ADMIN.php");
            } elseif ($_SESSION['role_user'] == 'user') {
                $_SESSION['user'] = $row['username'];
                $username = $row['username'];
                $user_email = $row['email'];
                sendLoginNotification($username, $user_email, $connection);
                header("Location: mainpage_user.php");
            }
        } else {
            // User not found or passwords do not match
            $_SESSION['error'] = "Niepoprawne dane logowania";
            header("Location: login_page.php");
        }
    } else {
        // reCAPTCHA verification failed
        $_SESSION['error'] = "Proszę potwierdzić, że jesteś człowiekiem.";
        header("Location: login_page.php");
    }
} else {
    // reCAPTCHA response not received
    $_SESSION['error'] = "Proszę potwierdzić, że jesteś człowiekiem.";
    header("Location: login_page.php");
}

// Funkcja do wysyłania powiadomienia e-mail po zalogowaniu
function sendLoginNotification($username, $user_email, $connection) {
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
    $mail->Subject = "Powiadomienie o zalogowaniu - $username";
    $loginTime = date('Y-m-d H:i:s');
    $mail->Body = "$username zalogowałeś się na swoje konto o godzinie $loginTime.";

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
?>
