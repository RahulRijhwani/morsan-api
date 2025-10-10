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

// Get category_id from GET or POST
$subcategory_id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$subcategory_id) {
    echo json_encode(["success" => false, "message" => "Subcategory id is required"]);
    exit();
}

$stmt = $conn->prepare("
    SELECT s.*, c.name AS subcategory_name
    FROM products s
    LEFT JOIN sub_categories c ON s.subcategory_id = c.id
    WHERE s.subcategory_id = ?
");
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$res = $stmt->get_result();

$products = [];
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}

if (count($products) > 0) {
    echo json_encode(["success" => true, "data" => $products]);
} else {
    echo json_encode(["success" => false, "message" => "No products found for this subcategory"]);
}
?>
