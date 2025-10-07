<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'db.php';

$id = $_POST['id'] ?? null;

// Fetch existing record if updating
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Contact not found"]);
        exit;
    }
    $existing = $res->fetch_assoc();
} else {
    $existing = [
        'name' => null,
        'phone' => null,
        'email' => null,
        'type' => null,
        'message' => null,
        'company' => null,
        'location' => null,
        'product_type' => null,
        'is_read' => 0
    ];
}

// Get form values or fallback to existing values
$name         = $_POST['name']    ?? $existing['name'] ?? '';
$phone        = $_POST['phone']   ?? $existing['phone'] ?? '';
$email        = $_POST['email']   ?? $existing['email'] ?? '';
$type         = $_POST['type']    ?? $existing['type'] ?? '';
$form_message = $_POST['message'] ?? $existing['message'] ?? '';
$company      = $_POST['company'] ?? $existing['company'] ?? '';
$location     = $_POST['location'] ?? $existing['location'] ?? '';
$product_type = $_POST['product_type'] ?? $existing['product_type'] ?? '';

// Normalize is_read to 0 or 1
if (isset($_POST['is_read'])) {
    $is_read = filter_var($_POST['is_read'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
} else {
    $is_read = $existing['is_read'] ?? 0;
}

if ($id) {
    // Update existing record
    $sql = "UPDATE contacts SET is_read=?, updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $is_read, $id);
    $message = "Contact updated successfully";
} else {
    // Insert new record
    $sql = "INSERT INTO contacts (name, phone, email, type, message, company, location, product_type, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $name, $phone, $email, $type, $form_message, $company, $location, $product_type, $is_read);
    $message = "Contact added successfully";
}

// Execute and return result
if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id"      => $id ?: $stmt->insert_id,
            "name"    => $name,
            "phone"   => $phone,
            "email"   => $email,
            "type"    => $type,
            "message" => $form_message,
            "company" => $company,
            "location" => $location,
            "product_type" => $product_type,
            "is_read" => $is_read
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
?>
