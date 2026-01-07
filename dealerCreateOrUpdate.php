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
require_once __DIR__ . '/mail/dealerMail.php';

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
        'first_name' => null,
        'last_name' => null,
        'phone' => null,
        'email' => null,
        'country' => null,
        'location' => null,
        'city' => null,
        'dist' => null,
        'state' => null,
        'pin_code' => null,
        'firm_name' => null,
        'is_read' => 0
    ];
}

// Get form values or fallback to existing values
$first_name         = $_POST['first_name']    ?? $existing['first_name'] ?? '';
$last_name         = $_POST['last_name']    ?? $existing['last_name'] ?? '';
$phone        = $_POST['phone']   ?? $existing['phone'] ?? '';
$email        = $_POST['email']   ?? $existing['email'] ?? '';
$type         = 'Dealer';
$firm_name = $_POST['firm_name'] ?? $existing['firm_name'] ?? '';
$country      = $_POST['country'] ?? $existing['country'] ?? '';
$location     = $_POST['location'] ?? $existing['location'] ?? '';
$city     = $_POST['city'] ?? $existing['city'] ?? '';
$dist     = $_POST['dist'] ?? $existing['dist'] ?? '';
$state     = $_POST['state'] ?? $existing['state'] ?? '';
$pin_code     = $_POST['pin_code'] ?? $existing['pin_code'] ?? '';

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
    $sql = "INSERT INTO contacts (first_name, last_name, firm_name, country, city, dist, state, pin_code, phone, email, type, location, is_read, created_at) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssi", $first_name, $last_name, $firm_name, $country, $city, $dist, $state, $pin_code, $phone, $email, $type, $location, $is_read);
    $message = "Contact added successfully";
}

// Execute and return result
if ($stmt->execute()) {
    $mailSent = null;

    if (! $id) {
        $mailSent = sendContactMail([
            'firm_name'    => $firm_name,
            'address'    => $location,
            'city'    => $city,
            'district'    => $dist,
            'state'    => $state,
            'pin_code'    => $pin_code,
            'country'    => $country,
            'first_name'        => $first_name,
            'last_name'        => $last_name,
            'email'       => $email,
            'phone'       => $phone,
        ]);
    }
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id"      => $id ?: $stmt->insert_id,
            "first_name"    => $first_name,
            "last_name"    => $last_name,
            "firm_name"    => $firm_name,
            "country"    => $country,
            "city"    => $city,
            "dist"    => $dist,
            "state"    => $state,
            "pin_code"    => $pin_code,
            "phone"   => $phone,
            "email"   => $email,
            "location" => $location,
            "type"    => $type,
            "is_read" => $is_read
        ],
        "mailSent" => $mailSent
    ]);
} else {
    echo json_encode(["success" => false, "message" => $conn->error]);
}
