<?php
// if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1')) {
    // Local database settings
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "moron";
    
// } else {
//     // Server (production) database settings
//     $db_host = "localhost";
//     $db_user = "";
//     $db_pass = "";
//     $db_name = "";
// }

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
