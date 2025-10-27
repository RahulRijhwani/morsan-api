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

$id           = $_POST['id'] ?? null;
$index        = $_POST['index'] ?? null;
$callback_url = $_POST['callback_url'] ?? null;
$status       = $_POST['status'] ?? null;

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
    // Fetch existing media record
    $stmt = $conn->prepare("SELECT * FROM media WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Media not found"]);
        exit;
    }

    $old = $res->fetch_assoc();

    // Handle image logic
    if ($imagePath) {
        if (!empty($old['image']) && file_exists($old['image'])) {
            @unlink($old['image']);
        }
        $finalImage = $imagePath;
    } elseif (isset($_POST['image']) && $_POST['image'] === "") {
        // explicitly remove image
        if (!empty($old['image']) && file_exists($old['image'])) {
            @unlink($old['image']);
        }
        $finalImage = null;
    } else {
        $finalImage = $old['image'];
    }

    // Only update fields that are provided, otherwise keep old values
    $finalIndex = $index !== null ? $index : $old['index'];
    $finalCallback = $callback_url !== null ? $callback_url : $old['callback_url'];
    $finalStatus = $status !== null ? $status : $old['status'];

    $sql = "UPDATE media SET 
        `index`=?, callback_url=?, image=?, updated_by=?, status=?, updated_at=NOW() 
        WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssii", $finalIndex, $finalCallback, $finalImage, $loggedUser, $finalStatus, $id);

    $message = "Media updated successfully";

} else {
    // Insert new media
    $finalImage = $imagePath ?? null;
    $sql = "INSERT INTO media (`index`, callback_url, image, created_by, updated_by, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssi", $index, $callback_url, $finalImage, $loggedUser, $loggedUser, $status);
    $message = "Media created successfully";
}

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id" => $id ?: $stmt->insert_id,
            "index" => $finalIndex ?? $index,
            "callback_url" => $finalCallback ?? $callback_url,
            "image" => $finalImage,
            "status" => $finalStatus ?? $status,
            "updated_by" => $loggedUser
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
