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
function getLoggedInUserFromToken()
{
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
$description    = $_POST['description'] ?? '';
$sub_description    = $_POST['sub_description'] ?? '';
$accessories    = $_POST['accessories'] ?? '[]'; // Expecting JSON or array

$sort_index = isset($_POST['sort_index']) ? (int)$_POST['sort_index'] : 0;

// SHIFT PRODUCT INDEX (avoid duplicate sort_index)
if ($sort_index > 0) {

    if ($subcategory_id) {
        // Product belongs to a subcategory
        if ($id) {
            // update
            $shiftStmt = $conn->prepare("
                UPDATE products 
                SET sort_index = sort_index + 1
                WHERE subcategory_id = ? AND sort_index >= ? AND id != ?
            ");
            $shiftStmt->bind_param("iii", $subcategory_id, $sort_index, $id);

        } else {
            // insert
            $shiftStmt = $conn->prepare("
                UPDATE products 
                SET sort_index = sort_index + 1
                WHERE subcategory_id = ? AND sort_index >= ?
            ");
            $shiftStmt->bind_param("ii", $subcategory_id, $sort_index);
        }

    } else {
        // Product belongs directly to a category
        if ($id) {
            $shiftStmt = $conn->prepare("
                UPDATE products 
                SET sort_index = sort_index + 1
                WHERE category_id = ? AND subcategory_id IS NULL AND sort_index >= ? AND id != ?
            ");
            $shiftStmt->bind_param("iii", $category_id, $sort_index, $id);

        } else {
            $shiftStmt = $conn->prepare("
                UPDATE products 
                SET sort_index = sort_index + 1
                WHERE category_id = ? AND subcategory_id IS NULL AND sort_index >= ?
            ");
            $shiftStmt->bind_param("ii", $category_id, $sort_index);
        }
    }

    $shiftStmt->execute();
}

// Convert arrays to JSON strings
$advantagesJson = is_array($advantages) ? json_encode($advantages, JSON_UNESCAPED_UNICODE) : $advantages;
$featuresJson   = is_array($features) ? json_encode($features, JSON_UNESCAPED_UNICODE) : $features;
$technicalJson  = is_array($technicalInput) ? json_encode($technicalInput, JSON_UNESCAPED_UNICODE) : $technicalInput;

// Convert accessories (if passed as array)
if (is_array($accessories)) {
    $accessoriesArr = $accessories;
} else {
    $accessoriesArr = json_decode($accessories, true) ?? [];
}

// Handle uploaded accessory images
if (!empty($_FILES['accessory_images']['name'][0])) {
    foreach ($_FILES['accessory_images']['name'] as $key => $accFile) {
        $tmpName = $_FILES['accessory_images']['tmp_name'][$key];
        $ext = pathinfo($accFile, PATHINFO_EXTENSION);
        $newName = uniqid("acc_") . "." . $ext;
        if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
            // Attach new image to corresponding accessory title
            $title = $_POST['accessory_titles'][$key] ?? '';
            $accessoriesArr[] = ["image" => $uploadDir . $newName, "title" => $title];
        }
    }
}

$accessoriesJson = json_encode($accessoriesArr, JSON_UNESCAPED_UNICODE);

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
$pdf = '0';
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
    $stmt = $conn->prepare("SELECT images, pdf, accessories FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Product not found"]);
        exit;
    }
    $row = $res->fetch_assoc();

    $oldImages = json_decode($row['images'], true) ?? [];
    $oldPdf = $row['pdf'] ?? '0';
    $oldAccessories = json_decode($row['accessories'], true) ?? [];

    // Images logic
    $keptOldImages = isset($_POST['existing_images']) ? json_decode($_POST['existing_images'], true) : null;
    if ($keptOldImages !== null) {
        $removed = array_diff($oldImages, $keptOldImages);
        foreach ($removed as $oldImg) {
            if (file_exists($oldImg)) @unlink($oldImg);
        }
        $finalImages = array_values(array_merge($keptOldImages, $uploadedImages));
    } else if (!empty($uploadedImages)) {
        foreach ($oldImages as $oldImg) {
            if (file_exists($oldImg)) @unlink($oldImg);
        }
        $finalImages = $uploadedImages;
    } else {
        $finalImages = $oldImages;
    }

    // PDF logic
    if ($pdf !== '0') {
        if ($oldPdf && file_exists($oldPdf)) {
            @unlink($oldPdf);
        }
        $finalPdf = $pdf;
    } else {
        $finalPdf = $oldPdf ?: '0';
    }

    // // Accessories logic — keep old + add new
    // $finalAccessories = array_merge($oldAccessories, $accessoriesArr);
    // $accessoriesJson = json_encode($finalAccessories, JSON_UNESCAPED_UNICODE);

    // ACCESSORIES DELETE/KEEP/ADD LOGIC
    $keptOldAccessories = isset($_POST['existing_accessories'])
        ? json_decode($_POST['existing_accessories'], true)
        : null;

    if ($keptOldAccessories !== null) {

        // Find removed accessories
        $removedAccessories = array_udiff(
            $oldAccessories,
            $keptOldAccessories,
            function ($a, $b) {
                return strcmp($a['image'], $b['image']);
            }
        );

        // Delete removed accessory images
        foreach ($removedAccessories as $acc) {
            if (!empty($acc['image']) && file_exists($acc['image'])) {
                @unlink($acc['image']);
            }
        }

        // Final = kept old + new uploaded
        $finalAccessories = array_merge($keptOldAccessories, $accessoriesArr);
    } else {
        // No old accessory list provided → keep all old + add new
        $finalAccessories = array_merge($oldAccessories, $accessoriesArr);
    }

    $accessoriesJson = json_encode($finalAccessories, JSON_UNESCAPED_UNICODE);


    // Prepare update statement
    $imagesJson = json_encode($finalImages);
    $stmt = $conn->prepare("UPDATE products SET 
  name=?, images=?, url=?, advantages=?, description=?, sub_description=?, 
  technical_specifications=?, special_features=?, 
  category_id=?, subcategory_id=?, updated_by=?, status=?, pdf=?, accessories=?, 
  sort_index=?, updated_at=NOW() 
WHERE id=?
");
    $stmt->bind_param(
    "ssssssssiissssii",
    $name, $imagesJson, $url, $advantagesJson, $description, $sub_description,
    $technicalJson, $featuresJson, $category_id, $subcategory_id, $loggedUser,
    $status, $finalPdf, $accessoriesJson, $sort_index, $id
);


    $message = "Product updated successfully";
} else {
    // INSERT
    $imagesJson = json_encode($uploadedImages);
    $stmt = $conn->prepare("INSERT INTO products (
  name, images, url, advantages, description, sub_description,
  technical_specifications, special_features, category_id, subcategory_id,
  created_by, updated_by, status, pdf, accessories, sort_index, created_at
)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
    $stmt->bind_param(
    "ssssssssiississi",
    $name, $imagesJson, $url, $advantagesJson, $description, $sub_description,
    $technicalJson, $featuresJson, $category_id, $subcategory_id,
    $loggedUser, $loggedUser, $status, $pdf, $accessoriesJson, $sort_index
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
            "description" => $description,
            "sub_description" => $sub_description,
            "advantages" => json_decode($advantagesJson, true),
            "technical_specifications" => json_decode($technicalJson, true),
            "special_features" => json_decode($featuresJson, true),
            "images" => $finalImages ?? $uploadedImages,
            "accessories" => json_decode($accessoriesJson, true),
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
