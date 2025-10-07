<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db.php';

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

$stmt = $conn->prepare("SELECT image FROM sub_categories WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Category not found"]);
    exit;
}

$row = $res->fetch_assoc();
$image = $row['image'] ?? null;

if (!empty($image) && file_exists($image)) {
    @unlink($image);
}

$stmt = $conn->prepare("DELETE FROM sub_categories WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Category and related image deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No category found with this ID"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $conn->error]);
}
