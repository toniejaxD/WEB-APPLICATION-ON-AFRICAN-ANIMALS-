<?php
session_start();

// Sprawdź, czy administrator jest zalogowany do swojego konta
if (!isset($_SESSION['admin']) || $_SESSION['admin'] == "") {
    header("Location: login_page.php");
    exit();
}
$username = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Strona Administratora</title>
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/responsive_ADMIN.css">

</head>
<body>
    <!--zawiera punkt nawigacyjny do innej strony-->
    <nav class="navbar">
        <ul class="menu">
            <li><a href="mainpage.html">Wyloguj się</a></li>
        </ul>
        <ul class="menu">
            <li><a href="mainpage_ADMIN.php">Powrót na stronę główną</a></li>
        </ul>     
    </nav>
    <!--białe tło które zawiera dany wpisy od użytkowników-->
    <div class="container">
        <!--będzie się odnosić się z dane które dany administrator się zalogował, będzie pokazać się jego nazwy username-->
        <h2>Witaj, <?php echo $username; ?></h2>
        <?php
        //będzie się pokazać komunikat czy dany wpis zostało zmienione czy nie
        if (isset($_SESSION['error'])) {
            echo "<h2><p class='error'>{$_SESSION['error']}</p></h2>";
            unset($_SESSION['error']);
        } elseif (isset($_SESSION['success'])) {
            echo "<h2><p>{$_SESSION['success']}</p></h2>";
            unset($_SESSION['success']);
        }
        ?>
        
            <h3>Lista użytkowników</h3>
            <table border="1">
                <tr>
                    <th>Username</th>
                    <th>Hasło</th>
                    <th>E-mail</th>
                    <th>Rola</th>
                    <th>Działanie</th>
                </tr>
                <?php
                $connection = mysqli_connect("localhost", "root", "", "afryka_blog");
                if (!$connection) {
                    die("Błąd połączenia z bazą danych: " . mysqli_connect_error());
                }
                //pobieranie z tabeli wpisy
                $query = "SELECT id, username, passwordd, email, user_role FROM rejestracjatesy";
                $result = $connection->query($query);
                // Sprawdź, czy zapytanie zwróciło wyniki
                if ($result->num_rows > 0) {
                    // Wyświetl listę użytkowników
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$row['username']}</td>";
                        echo "<td>{$row['passwordd']}</td>";
                        echo "<td>{$row['email']}</td>";
                        echo "<td>{$row['user_role']}</td>";

                        // Dodaj formularz usuwania tylko dla roli "user"
                        if ($row['user_role'] == 'user') {
                            echo "<td>";
                            echo "<form method='POST' action='delete_users_in_list_page_ADMIN.php'>";
                            echo "<div class='button-group'>";
                            echo "<input type='hidden' name='delete_user' value='1'>";
                            echo "<input type='hidden' name='user_id' value='{$row['id']}'>";
                            echo "<input type='submit' value='Usuń'>";
                            echo "</div>";
                            echo "</form>";
                            echo "</td>";
                        } else {
                            echo "<td></td>";
                        }

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Brak użytkowników</td></tr>";
                }

                ?>
            </table>
        </div>

    
</body>
</html>
