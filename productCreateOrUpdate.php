<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

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

$id             = $_POST['id'] ?? null;
$name           = $_POST['name'] ?? '';
$advantages     = $_POST['advantages'] ?? '[]';
$features       = $_POST['special_features'] ?? '[]';
$status         = $_POST['status'] ?? 1;
$category_id    = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;

$technicalInput = $_POST['technical_specifications'] ?? [];
if (is_array($technicalInput)) {
    $technical = json_encode($technicalInput, JSON_UNESCAPED_UNICODE);
} else {
    $technical = $technicalInput; 
}

$uploadedImages = [];
if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['name'] as $key => $nameFile) {
        $tmpName = $_FILES['images']['tmp_name'][$key];
        $ext = pathinfo($nameFile, PATHINFO_EXTENSION);
        $newName = uniqid("img_") . "." . $ext;

        if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
            $uploadedImages[] = $uploadDir . $newName;
        }
    }
}

if ($id) {
    $stmt = $conn->prepare("SELECT images FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit;
    }
    $row = $res->fetch_assoc();
    $oldImages = json_decode($row['images'], true) ?? [];

    $keptOldImages = isset($_POST['existing_images']) ? json_decode($_POST['existing_images'], true) : null;

    if ($keptOldImages !== null) {
        $removed = array_diff($oldImages, $keptOldImages);
        foreach ($removed as $oldImg) {
            if (file_exists($oldImg)) { @unlink($oldImg); }
        }
        $finalImages = array_values(array_merge($keptOldImages, $uploadedImages));
    } else if (!empty($uploadedImages)) {
        foreach ($oldImages as $oldImg) {
            if (file_exists($oldImg)) { @unlink($oldImg); }
        }
        $finalImages = $uploadedImages;
    } else {
        $finalImages = $oldImages;
    }

    $imagesStr = json_encode($finalImages);

    $sql = "UPDATE products SET 
        name=?, images=?, advantages=?, technical_specifications=?, 
        special_features=?, category_id=?, subcategory_id=?, 
        updated_by=?, status=?, updated_at=NOW() 
        WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssiisii",
        $name, $imagesStr, $advantages, $technical, $features,
        $category_id, $subcategory_id,
        $loggedUser, $status, $id
    );

    $message = "Product updated successfully";

} else {
    $imagesStr = json_encode($uploadedImages);

    $sql = "INSERT INTO products 
        (name, images, advantages, technical_specifications, special_features, 
         category_id, subcategory_id, created_by, updated_by, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssiissi",
        $name, $imagesStr, $advantages, $technical, $features,
        $category_id, $subcategory_id,
        $loggedUser, $loggedUser, $status
    );

    $message = "Product created successfully";
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id" => $id ?: $stmt->insert_id,
            "name" => $name,
            "advantages" => json_decode($advantages, true),
            "technical_specifications" => json_decode($technical, true),
            "special_features" => json_decode($features, true),
            "images" => $finalImages ?? $uploadedImages,
            "status" => $status,
            "category_id" => $category_id,
            "subcategory_id" => $subcategory_id,
            "created_by" => $loggedUser
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
