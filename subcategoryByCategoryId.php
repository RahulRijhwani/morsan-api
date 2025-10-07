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
$category_id = $_GET['category_id'] ?? $_POST['category_id'] ?? null;

if (!$category_id) {
    echo json_encode(["success" => false, "message" => "Category id is required"]);
    exit();
}

$stmt = $conn->prepare("
    SELECT s.*, c.name AS category_name
    FROM sub_categories s
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.category_id = ?
");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$res = $stmt->get_result();

$subcategories = [];
while ($row = $res->fetch_assoc()) {
    $subcategories[] = $row;
}

if (count($subcategories) > 0) {
    echo json_encode(["success" => true, "data" => $subcategories]);
} else {
    echo json_encode(["success" => false, "message" => "No subcategories found for this category"]);
}
?>
