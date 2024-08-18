<?php
session_start();

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

// Pobiera nazwę użytkownika, który jest zalogowany do swojego konta
$username = $_SESSION['user'];

// Pobiera status notification_turn z bazy danych
$query = "SELECT notification_turn FROM rejestracjatesy WHERE username = '$username'";
$result = mysqli_query($connection, $query);

if (!$result) {
    $_SESSION['error'] = "Błąd zapytania SQL: " . mysqli_error($connection);
    header("Location: change_data_user_page.php");
    exit();
}

if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_array($result);
    $current_notification_turn = $row['notification_turn'];

    // Zmienia status notification_turn w bazie danych
    $new_notification_turn = $current_notification_turn == 1 ? 0 : 1;
    $update_query = "UPDATE rejestracjatesy SET notification_turn = '$new_notification_turn' WHERE username = '$username'";

    if (mysqli_query($connection, $update_query)) {
        $_SESSION['success'] = "Status powiadomień został pomyślnie zmieniony!";
    } else {
        $_SESSION['error'] = "Nie udało się zmienić statusu powiadomień: " . mysqli_error($connection);
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
