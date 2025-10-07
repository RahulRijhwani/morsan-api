<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db.php';

// Accept ID from GET (query param), POST, or DELETE body
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && !$id) {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = $_DELETE['id'] ?? null;
}

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "ID is required"]);
    exit;
}

$sql = "DELETE FROM contacts WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Contact deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No contact found with this ID"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $conn->error]);
}
?>
