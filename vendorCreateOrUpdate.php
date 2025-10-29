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
        echo json_encode(["success" => false, "message" => "Vendor not found"]);
        exit;
    }
    $existing = $res->fetch_assoc();
} else {
    $existing = [
        'first_name' => null,
        'last_name' => null,
        'category' => null,
        'firm_name' => null,
        'phone' => null,
        'email' => null,
        'gst_no' => null,
        'pan_no' => null,
        'location' => null,
        'is_read' => 0
    ];
}

// Get form values or fallback to existing values
$first_name         = $_POST['first_name']    ?? $existing['first_name'] ?? '';
$last_name         = $_POST['last_name']    ?? $existing['last_name'] ?? '';
$category         = $_POST['category']    ?? $existing['category'] ?? '';
$firm_name         = $_POST['firm_name']    ?? $existing['firm_name'] ?? '';
$phone        = $_POST['phone']   ?? $existing['phone'] ?? '';
$email        = $_POST['email']   ?? $existing['email'] ?? '';
$type         = 'Vendor';
$gst_no = $_POST['gst_no'] ?? $existing['gst_no'] ?? '';
$pan_no      = $_POST['pan_no'] ?? $existing['pan_no'] ?? '';
$location      = $_POST['location'] ?? $existing['location'] ?? '';

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
    $message = "Vendor updated successfully";
} else {
    // Insert new record
    $sql = "INSERT INTO contacts (first_name, last_name, category, firm_name, gst_no, pan_no, location, phone, email, type, is_read, created_at) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssi", $first_name, $last_name, $category, $firm_name, $gst_no, $pan_no, $location, $phone, $email, $type, $is_read);
    $message = "Vendor added successfully";
}

// Execute and return result
if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id"      => $id ?: $stmt->insert_id,
            "first_name"    => $first_name,
            "last_name"    => $last_name,
            "category"    => $category,
            "firm_name"    => $firm_name,
            "gst_no"    => $gst_no,
            "pan_no"    => $pan_no,
            "location"    => $location,
            "phone"   => $phone,
            "email"   => $email,
            "type"    => $type,
            "is_read" => $is_read
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
?>
