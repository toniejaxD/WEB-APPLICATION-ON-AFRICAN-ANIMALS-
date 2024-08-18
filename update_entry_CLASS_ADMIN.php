<?php
session_start();

if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    header("Location: login_page.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendEmailNotification($authorEmail, $nazwa, $opis, $gatunek, $waga, $reason, $isImageChanged) {
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
    $mail->Subject = "Zmiana wpisu";

    $mail->Body = "<p><strong>Zmiana wpisu</strong></p>".
                  "<p>Powód zmiany wpisu: <strong>$reason</strong></p>".
                  "<p><strong>Szczegóły wpisu:</strong></p>".
                  "<ul>".
                  "<li>Nazwa: $nazwa</li>".
                  "<li>Opis: $opis</li>".
                  "<li>Gatunek: $gatunek</li>".
                  "<li>Waga: $waga kg</li>";

    if ($isImageChanged) {
        $mail->Body .= "<li>Zdjęcie zostało zmienione.</li>";
    } else {
        $mail->Body .= "<li>Nie zmieniono zdjęcia.</li>";
    }

    $mail->Body .= "</ul>";
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    if ($mail->send()) {
        return true; // Email sent successfully
    } else {
        return false;
    }
}

if (isset($_POST['submit'])) {
    $entry_id = $_POST['entry_id'];
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];
    $gatunek = $_POST['gatunek'];
    $waga = $_POST['waga'];
    $reason = $_POST['reason']; // Dodane uzyskiwanie powodu zmiany wpisu

    $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
    if (!$connection) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }

    $isImageChanged = false;

    if (isset($_FILES['obrazek']) && $_FILES['obrazek']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['obrazek']['name'];
        $file_tmp_name = $_FILES['obrazek']['tmp_name'];
        $file_size = $_FILES['obrazek']['size'];
        $file_type = $_FILES['obrazek']['type'];

        if ($file_type === 'image/png') {
            if ($file_size <= 2097152) {
                $obrazek_data = file_get_contents($file_tmp_name);
                $obrazek_data = mysqli_real_escape_string($connection, $obrazek_data);

                $query = "UPDATE wpisy SET nazwa='$nazwa', opis='$opis', gatunek='$gatunek', waga='$waga', obrazek='$obrazek_data' WHERE id=$entry_id";
                $result = mysqli_query($connection, $query);

                if ($result) {
                    $isImageChanged = true;
                    $_SESSION['success'] = "Wpis został zaktualizowany.";
                } else {
                    $_SESSION['error'] = "Błąd podczas aktualizacji wpisu: " . mysqli_error($connection);
                }
            } else {
                $_SESSION['error'] = "Zdjęcie jest za duże. Maksymalny rozmiar to 2 MB.";
            }
        } else {
            $_SESSION['error'] = "Zdjęcia są przyjmowane tylko w formacie PNG.";
        }
    } else {
        $query = "UPDATE wpisy SET nazwa='$nazwa', opis='$opis', gatunek='$gatunek', waga='$waga' WHERE id=$entry_id";
        $result = mysqli_query($connection, $query);

        if ($result) {
            $_SESSION['success'] = "Wpis został zaktualizowany.";
        } else {
            $_SESSION['error'] = "Błąd podczas aktualizacji wpisu: " . mysqli_error($connection);
        }
    }

    $queryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username IN (SELECT author FROM wpisy WHERE id = $entry_id)";
    $resultAuthorEmail = mysqli_query($connection, $queryAuthorEmail);

    if ($resultAuthorEmail && mysqli_num_rows($resultAuthorEmail) > 0) {
        $row = mysqli_fetch_assoc($resultAuthorEmail);
        $authorEmail = $row['email'];

        sendEmailNotification($authorEmail, $nazwa, $opis, $gatunek, $waga, $reason, $isImageChanged);
    }

    mysqli_close($connection);
    header("Location: mainpage_ADMIN.php");
    exit();
} else {
    $_SESSION['error'] = "Nieprawidłowe żądanie aktualizacji.";
    header("Location: mainpage_ADMIN.php");
    exit();
}
?>
