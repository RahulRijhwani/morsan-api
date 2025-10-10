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

$id     = $_POST['id'] ?? null;
$name   = $_POST['name'] ?? '';
$form_message = $_POST['message'] ?? '';
$status = $_POST['status'] ?? 1;

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
    $stmt = $conn->prepare("SELECT image FROM testimonials WHERE id=?");
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

    $sql = "UPDATE testimonials SET 
        name=?, message=?, image=?, updated_by=?, status=?, updated_at=NOW() 
        WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $name, $form_message, $finalImage, $loggedUser, $status, $id);

    $message = "Testimonial updated successfully";

} else {
    $finalImage = $imagePath ?? null;

    $sql = "INSERT INTO testimonials 
        (name, message, image, created_by, updated_by, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $name, $form_message, $finalImage, $loggedUser, $loggedUser, $status);

    $message = "Testimonial created successfully";
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id" => $id ?: $stmt->insert_id,
            "name" => $name,
            "message" => $form_message,
            "image" => $finalImage,
            "status" => $status,
            "created_by" => $loggedUser
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
