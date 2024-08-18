<?php
session_start();

// sprawdza czy użytkownik jest zalogowany na swoim koncie
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: login_page.php");
    // przekierowuje użytkownika na stronę logowania jeśli się nie zalogował
    exit();
}
//przechowuje nazwę zalogowanego użytkownika
$username = $_SESSION['user'];

$connection = new mysqli("localhost", "root", "", "afryka_blog");

// sprawdzenie czy udało się połączyć z bazą danych
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// zapytanie SQL do pobrania danych o zwierzętach z bazy danych
$query = "SELECT * FROM zwierzeta";
$result = $connection->query($query);

// inicjalizacja tablicy na dane o zwierzętach
$animals = [];

// pobranie danych o zwierzętach z wyników zapytania
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $animals[] = $row;
    }
}

// zamknięcie połączenia z bazą danych
$connection->close();
?>

<!DOCTYPE html>
<html lang="pl-PL">
<head>
    <title>Blog o zwierzętach afrykańskich - Wyszukiwarka zwierząt</title>
    <meta charset="UTF-8">
    <meta name="keywords" content="HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/responsive_user.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 500px;
            width: 100%;
        }
    </style>
    
</head>
<body>
    
    <nav class="navbar">
        <ul class="menu">
            <li><a href="mainpage.html">Wyloguj się</a></li>
        </ul>
        <ul class="menu">
            <li><a href="mainpage_user.php">Powrót na główną stronę</a></li>
        </ul>
    </nav>
    <!--białe tło które zawiera pole do uzupełnienia-->
    <div class="container">
        <!--będzie się odnosić się z dane które dany użytkownik się zalogował, będzie pokazać się jego nazwy username-->
        <h2>Witaj, <?php echo $username; ?></h2>
      
        <div id="map"></div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([1, 20], 3);

    var southWest = L.latLng(-40, -40),
        northEast = L.latLng(40, 60),
        bounds = L.latLngBounds(southWest, northEast);

    map.setMaxBounds(bounds);
    map.on('drag', function () {
        map.panInsideBounds(bounds, { animate: false });
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        minZoom: 2,
        maxZoom: 6,
        attribution: ''
    }).addTo(map);

    // Dodaj markery dla zwierząt
    <?php foreach ($animals as $animal): ?>
        var animalMarker = L.marker([<?= $animal['wysokosc'] ?>, <?= $animal['szerokosc'] ?>]).addTo(map)
            .bindPopup('<b><?= $animal['nazwa'] ?></b><br><img src="data:image/jpeg;base64,<?= base64_encode($animal['obraz']) ?>" width="100">');
    <?php endforeach; ?>
</script>
    </div>
    
</body>
</html>
