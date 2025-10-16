<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

// Preflight
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

// Get existing images and PDF
$stmt = $conn->prepare("SELECT images, pdf FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}

$row = $res->fetch_assoc();
$images = json_decode($row['images'], true) ?? [];
$pdf = $row['pdf'] ?? null;

// Delete images
foreach ($images as $img) {
    if (file_exists($img)) {
        @unlink($img);
    }
}

// Delete PDF
if ($pdf && $pdf !== '0' && file_exists($pdf)) {
    @unlink($pdf);
}

// Delete product
$stmt = $conn->prepare("DELETE FROM products WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Product, related images, and PDF deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No product found with this ID"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $conn->error]);
}
