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

// Get logged-in user from JWT
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

// Get POST data
$id             = $_POST['id'] ?? null;
$name           = $_POST['name'] ?? '';
$advantages     = $_POST['advantages'] ?? '[]';
$features       = $_POST['special_features'] ?? '[]';
$status         = $_POST['status'] ?? 1;
$category_id    = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
$subcategory_id = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
$url            = $_POST['url'] ?? '';
$technicalInput = $_POST['technical_specifications'] ?? [];

// Convert arrays to JSON strings
$advantagesJson = is_array($advantages) ? json_encode($advantages, JSON_UNESCAPED_UNICODE) : $advantages;
$featuresJson   = is_array($features) ? json_encode($features, JSON_UNESCAPED_UNICODE) : $features;
$technicalJson  = is_array($technicalInput) ? json_encode($technicalInput, JSON_UNESCAPED_UNICODE) : $technicalInput;

// Handle multiple images
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

// Handle single PDF
$pdf = '0'; // default 0 if no PDF uploaded
if (!empty($_FILES['pdf']['name'])) {
    $pdfTmp = $_FILES['pdf']['tmp_name'];
    $pdfExt = pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION);
    $pdfName = uniqid("pdf_") . "." . $pdfExt;

    if (move_uploaded_file($pdfTmp, $uploadDir . $pdfName)) {
        $pdf = $uploadDir . $pdfName;
    } 
}

// Handle INSERT or UPDATE
if ($id) {
    // UPDATE
    $stmt = $conn->prepare("SELECT images, pdf FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit;
    }
    $row = $res->fetch_assoc();
    $oldImages = json_decode($row['images'], true) ?? [];
    $oldPdf    = $row['pdf'] ?? '0';

    // Images update logic
    $keptOldImages = isset($_POST['existing_images']) ? json_decode($_POST['existing_images'], true) : null;
    if ($keptOldImages !== null) {
        $removed = array_diff($oldImages, $keptOldImages);
        foreach ($removed as $oldImg) { if (file_exists($oldImg)) @unlink($oldImg); }
        $finalImages = array_values(array_merge($keptOldImages, $uploadedImages));
    } else if (!empty($uploadedImages)) {
        foreach ($oldImages as $oldImg) { if (file_exists($oldImg)) @unlink($oldImg); }
        $finalImages = $uploadedImages;
    } else {
        $finalImages = $oldImages;
    }

    // PDF update logic
    if ($pdf !== '0') { // new PDF uploaded
        if ($oldPdf && file_exists($oldPdf)) { @unlink($oldPdf); }
        $finalPdf = $pdf;
    } else {
        $finalPdf = $oldPdf ?: '0';
    }

    // Prepare update statement
    $imagesJson = json_encode($finalImages);
    $stmt = $conn->prepare("UPDATE products SET 
        name=?, images=?, url=?, advantages=?, technical_specifications=?, 
        special_features=?, category_id=?, subcategory_id=?, 
        updated_by=?, status=?, pdf=?, updated_at=NOW() 
        WHERE id=?");
    $stmt->bind_param(
        "ssssssiisssi",
        $name, $imagesJson, $url, $advantagesJson, $technicalJson, $featuresJson,
        $category_id, $subcategory_id,
        $loggedUser, $status, $finalPdf, $id
    );

    $message = "Product updated successfully";

} else {
    // INSERT
    $imagesJson = json_encode($uploadedImages);
    $stmt = $conn->prepare("INSERT INTO products 
        (name, images, url, advantages, technical_specifications, special_features, 
         category_id, subcategory_id, created_by, updated_by, status, pdf, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param(
        "ssssssiissis",
        $name, $imagesJson, $url, $advantagesJson, $technicalJson, $featuresJson,
        $category_id, $subcategory_id,
        $loggedUser, $loggedUser, $status, $pdf
    );

    $message = "Product created successfully";
}

// Execute statement
if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id" => $id ?: $stmt->insert_id,
            "name" => $name,
            "advantages" => json_decode($advantagesJson, true),
            "technical_specifications" => json_decode($technicalJson, true),
            "special_features" => json_decode($featuresJson, true),
            "images" => $finalImages ?? $uploadedImages,
            "status" => $status,
            "category_id" => $category_id,
            "subcategory_id" => $subcategory_id,
            "created_by" => $loggedUser,
            "pdf" => $finalPdf ?? $pdf ?? '0',
            "url" => $url ?? null
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
