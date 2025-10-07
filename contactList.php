<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;

$data = [];

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
        echo json_encode([
            "success" => true,
            "data" => $data
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Contact not found"
        ]);
    }
} else {
    $sql = "SELECT * FROM contacts";
    $query = $conn->query($sql);

    while ($row = $query->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);
}
?>
