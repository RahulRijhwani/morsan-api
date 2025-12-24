<?php
if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')) {
    // Local database settings
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "moron";
    
} else if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'https://stageapi.morsan.co.in/' || $_SERVER['HTTP_HOST'] === 'stageapi.morsan.co.in')) {
    // Staging database settings
    $db_host = "localhost";
    $db_user = "u696140658_morsan_stage";
    $db_pass = "Morsan@2025";
    $db_name = "u696140658_morsan_stage";
} else {
    // Server (production) database settings
    $db_host = "localhost";
    $db_user = "u696140658_morsan";
    $db_pass = "morsan@DB12";
    $db_name = "u696140658_morsan";
}

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
