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

// function getLoggedInUserFromToken() {
//     $headers = getallheaders();
//     if (!isset($headers['Authorization'])) return null;

//     $authHeader = trim($headers['Authorization']);
//     if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) return null;

//     $token = $matches[1];
//     $payload = verifyJWT($token, $GLOBALS['jwtSecret']); 
//     return $payload ?: null;
// }

// $user = getLoggedInUserFromToken();
// if (!$user) {
//     echo json_encode(["success" => false, "message" => "Unauthorized or invalid token"]);
//     exit;
// }

// $loggedUser = $user['email'];  

$id     = $_POST['id'] ?? null;
$name   = $_POST['name'] ?? '';
$email  = $_POST['email'] ?? '';
$phone  = $_POST['phone'] ?? '';
$role   = $_POST['role'] ?? '';
$form_message = $_POST['message'] ?? '';
$type   = 'Career';

$filePath = null;

if (!empty($_FILES['file']['name'])) {
    $tmpName = $_FILES['file']['tmp_name'];
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $newName = uniqid("file_") . "." . $ext;

    if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
        $filePath = $uploadDir . $newName;
    }
}

if ($id) {
    $stmt = $conn->prepare("SELECT file FROM contacts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Category not found"]);
        exit;
    }
    $row = $res->fetch_assoc();
    $oldFile = $row['file'];

    if ($filePath) {
        if (!empty($oldFile) && file_exists($oldFile)) {
            @unlink($oldFile);
        }
        $finalFile = $filePath;

    } elseif (isset($_POST['file']) && $_POST['file'] === "") {
        if (!empty($oldFile) && file_exists($oldFile)) {
            @unlink($oldFile);
        }
        $finalFile = null;

    } else {
        $finalFile = $oldFile;
    }

    $sql = "UPDATE contacts SET 
        name=?, email=?, phone=?, role=?, message=?, file=?, type=?, updated_at=NOW() 
        WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssi", $name, $email, $phone, $role, $form_message, $finalFile, $type, $id);

    $message = "Career updated successfully";

} else {
    $finalFile = $filePath ?? null;

    $sql = "INSERT INTO contacts 
        (name, email, phone, role, message, file, type, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $email, $phone, $role, $form_message, $finalFile, $type);

    $message = "Career created successfully";
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id" => $id ?: $stmt->insert_id,
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "role" => $role,
            "message" => $form_message,
            "file" => $finalFile,
            "type" => $type,
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
