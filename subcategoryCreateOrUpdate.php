<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';
require_once "functions.php";

$uploadDir = "uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function getLoggedInUserFromToken() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) return null;

    $authHeader = trim($headers['Authorization']);
    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) return null;

    $token = $matches[1];
    $payload = verifyJWT($token, $GLOBALS['jwtSecret']); 
    return $payload ?: null;
}

$user = getLoggedInUserFromToken();
if (!$user) {
    echo json_encode(["success" => false, "message" => "Unauthorized or invalid token"]);
    exit;
}

$loggedUser = $user['email'];  

$id           = $_POST['id'] ?? null;
$name         = $_POST['name'] ?? '';
$category_id  = $_POST['category_id'] ?? null;
$status       = $_POST['status'] ?? 1;
$sort_index = $_POST['sort_index'] ?? 0;

// SHIFT sort indexes inside same parent category
if ($sort_index > 0 && $category_id) {

    if ($id) {
        // Update existing → avoid shifting its own index
        $shiftStmt = $conn->prepare("
            UPDATE sub_categories 
            SET sort_index = sort_index + 1 
            WHERE category_id = ? AND sort_index >= ? AND id != ?
        ");
        $shiftStmt->bind_param("iii", $category_id, $sort_index, $id);

    } else {
        // Creating new subcategory
        $shiftStmt = $conn->prepare("
            UPDATE sub_categories 
            SET sort_index = sort_index + 1 
            WHERE category_id = ? AND sort_index >= ?
        ");
        $shiftStmt->bind_param("ii", $category_id, $sort_index);
    }

    $shiftStmt->execute();
}


$imagePath = null;

if (!empty($_FILES['image']['name'])) {
    $tmpName = $_FILES['image']['tmp_name'];
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $newName = uniqid("img_") . "." . $ext;

    if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
        $imagePath = $uploadDir . $newName;
    }
}

if ($id) {
    $stmt = $conn->prepare("SELECT image FROM sub_categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Category not found"]);
        exit;
    }
    $row = $res->fetch_assoc();
    $oldImage = $row['image'];

    if ($imagePath) {
        if (!empty($oldImage) && file_exists($oldImage)) {
            @unlink($oldImage);
        }
        $finalImage = $imagePath;
    } elseif (isset($_POST['image']) && $_POST['image'] === "") {
        if (!empty($oldImage) && file_exists($oldImage)) {
            @unlink($oldImage);
        }
        $finalImage = null;

    } else {
        $finalImage = $oldImage;
    }

    $sql = "UPDATE sub_categories SET 
    name=?, category_id=?, image=?, updated_by=?, status=?, sort_index=?, updated_at=NOW() 
    WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sissiii", $name, $category_id, $finalImage, $loggedUser, $status, $sort_index, $id);

    $message = "Category updated successfully";

} else {
    $finalImage = $imagePath ?? null;

    $sql = "INSERT INTO sub_categories 
    (name, category_id, image, created_by, updated_by, status, sort_index, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sisssii", $name, $category_id, $finalImage, $loggedUser, $loggedUser, $status, $sort_index);


    $message = "Category created successfully";
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id" => $id ?: $stmt->insert_id,
            "name" => $name,
            "category_id" => $category_id,
            "image" => $finalImage,
            "sort_index" => $sort_index,
            "status" => $status,
            "created_by" => $loggedUser
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
