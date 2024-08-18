<?php
session_start();

// Sprawdza, czy dany administrator jest zalogowany
if (!isset($_SESSION['admin']) || $_SESSION['admin'] == "") {
    // Przekierowuje administratora na stronę logowania, jeśli nie jest zalogowany
    header("Location: login_page.php");
    exit();
}


// Załaduj klasy PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

//////////// Funkcja do wysyłania powiadomienia e-mail o usunięty WPIS
function sendEmailNotificationOnEntryDeletion($username, $userEmail, $nazwa, $opis, $gatunek, $waga, $data_wyslania, $reason) {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email admin';
    $mail->Password = 'password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('email admin');

    // Sprawdź czy adres email autora jest prawidłowy
    if (!empty($userEmail)) {
        $mail->addAddress($userEmail);
    } else {
        // Dodaj obsługę sytuacji, gdy adres email autora jest nieprawidłowy
        // Na przykład, możesz zignorować powiadomienie lub wysłać je na inny adres email
        // W tym przypadku zignorujmy powiadomienie
        echo "Ostrzeżenie: Nieprawidłowy adres email autora, powiadomienie nie zostało wysłane.";
        return; // Zakończ funkcję, nie wysyłaj powiadomienia
    }
    $mail->isHTML(true);
    $mail->Subject = "Twój wpis został usunięty";

    // Treść e-maila z informacjami o usuniętym wpisie i powodzie
    $mail->Body = "<p>Twój wpis został usunięty:</p>";
    $mail->Body .= "<p>Nazwa zwierzęcia: $nazwa</p>";
    $mail->Body .= "<p>Opis treści: $opis</p>";
    $mail->Body .= "<p>Rodzaj gatunku zwierzęcia: $gatunek</p>";
    $mail->Body .= "<p>Waga: $waga</p>";
    $mail->Body .= "<p>Data wysłania: $data_wyslania</p>";
    $mail->Body .= "<p>Powód usunięcia: $reason</p>";

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    $mail->send();
}

/////////////////////////////


////////////////////////////Powiadomienie o usunięty komentarz
function sendEmailNotificationOnCommentDeletion($username, $userEmail,$komentarz, $data_dodania, $reason) {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email admin';
    $mail->Password = 'password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('email admin');

    // Sprawdź czy adres email autora jest prawidłowy
    if (!empty($userEmail)) {
        $mail->addAddress($userEmail);
    } else {
        // Dodaj obsługę sytuacji, gdy adres email autora jest nieprawidłowy
        // Na przykład, możesz zignorować powiadomienie lub wysłać je na inny adres email
        // W tym przypadku zignorujmy powiadomienie
        echo "Ostrzeżenie: Nieprawidłowy adres email autora, powiadomienie nie zostało wysłane.";
        return; // Zakończ funkcję, nie wysyłaj powiadomienia
    }
    $mail->isHTML(true);
    $mail->Subject = "Twój komentarz został usunięty";

    // Treść e-maila z informacjami o usuniętym wpisie i powodzie
    $mail->Body = "<p>Twój komentarz został usunięty:</p>";
    $mail->Body .= "<p>Twój komentarz: $komentarz</p>";
    $mail->Body .= "<p>Data wysłania tego komentarza: $data_dodania</p>";
    $mail->Body .= "<p>Powód usunięcia: $reason</p>";

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    $mail->send();
}


//////////////////////////////////////////////////////////

function sendEmailNotificationOnUserDeletion($username, $userEmail, $passwordd, $reason) {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'email admin';
    $mail->Password = 'password';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('email admin');

    $mail->addAddress($userEmail);
    $mail->isHTML(true);
    $mail->Subject = "Zostałeś usunięty z Afryki blog!";

    // Treść e-maila z informacjami o usuniętym wpisie i powodzie
    $mail->Body = "<p>Zostałeś usunięty z Afryki blog!</p>";
    $mail->Body .= "<p>Nazwa użytkownika: $username</p>";
    $mail->Body .= "<p>Adres e-mail: $userEmail</p>";
    $mail->Body .= "<p>Hasło: $passwordd</p>";
    $mail->Body .= "<p>Powód usunięcia: $reason</p>";

    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->setLanguage('pl');

    $mail->send();
}

/////////////////////////

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'])) {
    $comment_id = $_POST['comment_id'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
    if (!$connection) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }
    
    // Pobierz autora wpisu
    $queryEntryAuthor = "SELECT author FROM komentarze_pod_wpisem WHERE id = ?";
    $stmtEntryAuthor = mysqli_prepare($connection, $queryEntryAuthor);
    mysqli_stmt_bind_param($stmtEntryAuthor, "i", $comment_id);
    mysqli_stmt_execute($stmtEntryAuthor);
    mysqli_stmt_bind_result($stmtEntryAuthor, $entryAuthor);
    mysqli_stmt_fetch($stmtEntryAuthor);
    mysqli_stmt_close($stmtEntryAuthor);

    // Pobierz szczegóły wpisu (np. nazwę, opis, gatunek, wagę, obrazek, itp.)
    $queryEntryDetails = "SELECT komentarz, data_dodania FROM komentarze_pod_wpisem WHERE id = ?";
    $stmtEntryDetails = mysqli_prepare($connection, $queryEntryDetails);
    mysqli_stmt_bind_param($stmtEntryDetails, "i", $comment_id);
    mysqli_stmt_execute($stmtEntryDetails);
    mysqli_stmt_bind_result($stmtEntryDetails, $komentarz, $data_dodania);
    mysqli_stmt_fetch($stmtEntryDetails);
    mysqli_stmt_close($stmtEntryDetails);

    // Usuń wpis z bazy danych
    $queryDeleteEntry = "DELETE FROM komentarze_pod_wpisem WHERE id = ?";
    $stmtDeleteEntry = mysqli_prepare($connection, $queryDeleteEntry);
    mysqli_stmt_bind_param($stmtDeleteEntry, "i", $comment_id);

    if (mysqli_stmt_execute($stmtDeleteEntry)) {
        // Poinformuj użytkownika, że wpis został usunięty
        $_SESSION['success'] = "Komentarz został pomyślnie usunięty.";

        // Pobierz e-mail autora wpisu
        $queryEntryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = ?";
        $stmtEntryAuthorEmail = mysqli_prepare($connection, $queryEntryAuthorEmail);
        mysqli_stmt_bind_param($stmtEntryAuthorEmail, "s", $entryAuthor);
        mysqli_stmt_execute($stmtEntryAuthorEmail);
        mysqli_stmt_bind_result($stmtEntryAuthorEmail, $entryAuthorEmail);
        mysqli_stmt_fetch($stmtEntryAuthorEmail);
        mysqli_stmt_close($stmtEntryAuthorEmail);

     
        // Poinformuj autora wpisu o usunięciu
// Poinformuj autora wpisu o usunięciu
sendEmailNotificationOnCommentDeletion($entryAuthor, $entryAuthorEmail, $komentarz, $data_dodania, $reason);    
    
    } else {
        // Poinformuj administratora o błędzie
        $_SESSION['error'] = "Nie udało się usunąć wpisu. Spróbuj ponownie później.";
    }

    header("Location: mainpage_ADMIN.php");
    exit();
}


///////////////////////komenda do usuwanie WPISU
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entry_id'])) {
    $entry_id = $_POST['entry_id'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
    if (!$connection) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }
    
    // Pobierz autora wpisu
    $queryEntryAuthor = "SELECT author FROM wpisy WHERE id = ?";
    $stmtEntryAuthor = mysqli_prepare($connection, $queryEntryAuthor);
    mysqli_stmt_bind_param($stmtEntryAuthor, "i", $entry_id);
    mysqli_stmt_execute($stmtEntryAuthor);
    mysqli_stmt_bind_result($stmtEntryAuthor, $entryAuthor);
    mysqli_stmt_fetch($stmtEntryAuthor);
    mysqli_stmt_close($stmtEntryAuthor);

    // Pobierz szczegóły wpisu (np. nazwę, opis, gatunek, wagę, obrazek, itp.)
    $queryEntryDetails = "SELECT nazwa, opis, gatunek, waga, author, data_wyslania FROM wpisy WHERE id = ?";
    $stmtEntryDetails = mysqli_prepare($connection, $queryEntryDetails);
    mysqli_stmt_bind_param($stmtEntryDetails, "i", $entry_id);
    mysqli_stmt_execute($stmtEntryDetails);
    mysqli_stmt_bind_result($stmtEntryDetails, $nazwa, $opis, $gatunek, $waga,  $entryAuthor,$data_wyslania,);
    mysqli_stmt_fetch($stmtEntryDetails);
    mysqli_stmt_close($stmtEntryDetails);

    // Usuń wpis z bazy danych
    $queryDeleteEntry = "DELETE FROM wpisy WHERE id = ?";
    $stmtDeleteEntry = mysqli_prepare($connection, $queryDeleteEntry);
    mysqli_stmt_bind_param($stmtDeleteEntry, "i", $entry_id);

    if (mysqli_stmt_execute($stmtDeleteEntry)) {
        // Poinformuj użytkownika, że wpis został usunięty
        $_SESSION['success'] = "Wpis został pomyślnie usunięty.";

        // Pobierz e-mail autora wpisu
        $queryEntryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = ?";
        $stmtEntryAuthorEmail = mysqli_prepare($connection, $queryEntryAuthorEmail);
        mysqli_stmt_bind_param($stmtEntryAuthorEmail, "s", $entryAuthor);
        mysqli_stmt_execute($stmtEntryAuthorEmail);
        mysqli_stmt_bind_result($stmtEntryAuthorEmail, $entryAuthorEmail);
        mysqli_stmt_fetch($stmtEntryAuthorEmail);
        mysqli_stmt_close($stmtEntryAuthorEmail);

     
        // Poinformuj autora wpisu o usunięciu
// Poinformuj autora wpisu o usunięciu
sendEmailNotificationOnEntryDeletion($entryAuthor, $entryAuthorEmail, $nazwa, $opis, $gatunek, $waga, $data_wyslania,$reason);    
    
    } else {
        // Poinformuj administratora o błędzie
        $_SESSION['error'] = "Nie udało się usunąć wpisu. Spróbuj ponownie później.";
    }

    header("Location: mainpage_ADMIN.php");
    exit();
} 



//////////////////////////////////


///////////////////////komenda do usuwanie UŻYTKOWNIKA
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entry_id'])) {
    $entry_id = $_POST['entry_id'];
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
    if (!$connection) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }
    
    // Pobierz autora wpisu
    $queryEntryAuthor = "SELECT author FROM wpisy WHERE id = ?";
    $stmtEntryAuthor = mysqli_prepare($connection, $queryEntryAuthor);
    mysqli_stmt_bind_param($stmtEntryAuthor, "i", $entry_id);
    mysqli_stmt_execute($stmtEntryAuthor);
    mysqli_stmt_bind_result($stmtEntryAuthor, $entryAuthor);
    mysqli_stmt_fetch($stmtEntryAuthor);
    mysqli_stmt_close($stmtEntryAuthor);

    // Pobierz szczegóły wpisu (np. nazwę, opis, gatunek, wagę, obrazek, itp.)
    $queryEntryDetails = "SELECT nazwa, opis, gatunek, waga, author, data_wyslania FROM wpisy WHERE id = ?";
    $stmtEntryDetails = mysqli_prepare($connection, $queryEntryDetails);
    mysqli_stmt_bind_param($stmtEntryDetails, "i", $entry_id);
    mysqli_stmt_execute($stmtEntryDetails);
    mysqli_stmt_bind_result($stmtEntryDetails, $nazwa, $opis, $gatunek, $waga,  $entryAuthor,$data_wyslania,);
    mysqli_stmt_fetch($stmtEntryDetails);
    mysqli_stmt_close($stmtEntryDetails);

    // Usuń wpis z bazy danych
    $queryDeleteEntry = "DELETE FROM wpisy WHERE id = ?";
    $stmtDeleteEntry = mysqli_prepare($connection, $queryDeleteEntry);
    mysqli_stmt_bind_param($stmtDeleteEntry, "i", $entry_id);

    if (mysqli_stmt_execute($stmtDeleteEntry)) {
        // Poinformuj użytkownika, że wpis został usunięty
        $_SESSION['success'] = "Wpis został pomyślnie usunięty.";

        // Pobierz e-mail autora wpisu
        $queryEntryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = ?";
        $stmtEntryAuthorEmail = mysqli_prepare($connection, $queryEntryAuthorEmail);
        mysqli_stmt_bind_param($stmtEntryAuthorEmail, "s", $entryAuthor);
        mysqli_stmt_execute($stmtEntryAuthorEmail);
        mysqli_stmt_bind_result($stmtEntryAuthorEmail, $entryAuthorEmail);
        mysqli_stmt_fetch($stmtEntryAuthorEmail);
        mysqli_stmt_close($stmtEntryAuthorEmail);

     
        // Poinformuj autora wpisu o usunięciu
// Poinformuj autora wpisu o usunięciu
sendEmailNotificationOnEntryDeletion($entryAuthor, $entryAuthorEmail, $nazwa, $opis, $gatunek, $waga, $data_wyslania,$reason);    
    
    } else {
        // Poinformuj administratora o błędzie
        $_SESSION['error'] = "Nie udało się usunąć wpisu. Spróbuj ponownie później.";
    }

    header("Location: mainpage_ADMIN.php");
    exit();
}


///////////////

elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_user"]) && isset($_POST["user_id"])) {
    $connection = mysqli_connect("localhost", "root", "", "afryka_blog");

    if (!$connection) {
        die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
    }

    $user_id = mysqli_real_escape_string($connection, $_POST["user_id"]);

    // Pobierz informacje o użytkowniku przed usunięciem
    $queryUserInfo = "SELECT username, email, passwordd FROM rejestracjatesy WHERE id = '$user_id'";
    $resultUserInfo = mysqli_query($connection, $queryUserInfo);

    if ($resultUserInfo && mysqli_num_rows($resultUserInfo) > 0) {
        $userInfo = mysqli_fetch_assoc($resultUserInfo);

        // Pobierz powód usunięcia z formularza
        $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

        // Usuń użytkownika
        $queryDeleteUser = "DELETE FROM rejestracjatesy WHERE id = '$user_id'";
        if (mysqli_query($connection, $queryDeleteUser)) {
            $_SESSION['success'] = "Użytkownik został pomyślnie usunięty.";

            // Pobierz e-mail autora wpisu
            $queryEntryAuthorEmail = "SELECT email FROM rejestracjatesy WHERE username = ?";
            $stmtEntryAuthorEmail = mysqli_prepare($connection, $queryEntryAuthorEmail);
            mysqli_stmt_bind_param($stmtEntryAuthorEmail, "s", $userInfo['username']);
            mysqli_stmt_execute($stmtEntryAuthorEmail);
            mysqli_stmt_bind_result($stmtEntryAuthorEmail, $entryAuthorEmail);
            mysqli_stmt_fetch($stmtEntryAuthorEmail);
            mysqli_stmt_close($stmtEntryAuthorEmail);

            // Wywołaj funkcję do wysyłania powiadomienia e-mail z powodem usunięcia
            sendEmailNotificationOnUserDeletion($userInfo['username'], $userInfo['email'], $userInfo['passwordd'], $reason);

            header("Location: list_a_users_ADMIN_page.php");
            exit();
        } else {
            $_SESSION['error'] = "Błąd podczas usuwania użytkownika: " . mysqli_error($connection);
            header("Location: list_a_users_ADMIN_page.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Błąd podczas pobierania informacji o użytkowniku przed usunięciem.";
        header("Location: list_a_users_ADMIN_page.php");
        exit();
    }

    mysqli_close($connection);
}


// else {
//     // Przekierowuje w przypadku nieprawidłowego żądania
//     header("Location: mainpage_ADMIN.php");
//     exit();
// }


else {
    $_SESSION['error'] = "Nieprawidłowe żądanie.";
    header("Location: mainpage_ADMIN.php");
    exit();
}
?>
