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
require_once 'functions.php';

/* ================= AUTH ================= */

function getLoggedInUserFromToken()
{
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) return null;

    if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) return null;

    $token = $matches[1];
    $payload = verifyJWT($token, $GLOBALS['jwtSecret']);
    return $payload ?: null;
}

$user = getLoggedInUserFromToken();

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized or invalid token"
    ]);
    exit;
}

$loggedUser = $user['email'];

/* ================= INPUT ================= */

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? 1;

/* ================= FETCH EXISTING (FOR UPDATE) ================= */

if ($id) {
    $stmt = $conn->prepare("SELECT * FROM open_position WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Open position not found"
        ]);
        exit;
    }

    $existing = $res->fetch_assoc();
} else {
    $existing = [];
}

/* ================= FORM DATA ================= */

$job_title       = $_POST['job_title']       ?? $existing['job_title']       ?? '';
$workplace_type  = $_POST['workplace_type']  ?? $existing['workplace_type']  ?? '';
$job_location    = $_POST['job_location']    ?? $existing['job_location']    ?? '';
$job_type        = $_POST['job_type']        ?? $existing['job_type']        ?? '';
$job_description = $_POST['job_description'] ?? $existing['job_description'] ?? '';

/* ================= VALIDATION ================= */

if (
    empty($job_title) ||
    empty($workplace_type) ||
    empty($job_location) ||
    empty($job_type) ||
    empty($job_description)
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

/* ================= INSERT / UPDATE ================= */

if ($id) {

    /* ===== UPDATE ===== */

    $sql = "
        UPDATE open_position SET
            job_title = ?,
            workplace_type = ?,
            job_location = ?,
            job_type = ?,
            job_description = ?,
            status = ?,
            updated_by = ?,
            updated_at = NOW()
        WHERE id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssi",
        $job_title,
        $workplace_type,
        $job_location,
        $job_type,
        $job_description,
        $status,
        $loggedUser,
        $id
    );

    $message = "Open position updated successfully";
} else {

    /* ===== INSERT ===== */

    $sql = "
        INSERT INTO open_position
        (job_title, workplace_type, job_location, job_type, job_description, status, created_by, updated_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssiss",
        $job_title,
        $workplace_type,
        $job_location,
        $job_type,
        $job_description,
        $status,
        $loggedUser,
        $loggedUser
    );

    $message = "Open position added successfully";
}

/* ================= RESPONSE ================= */

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $message,
        "data" => [
            "id" => $id ?: $stmt->insert_id,
            "job_title" => $job_title,
            "workplace_type" => $workplace_type,
            "job_location" => $job_location,
            "job_type" => $job_type,
            "job_description" => $job_description,
            "status" => $status,
            "created_by" => $loggedUser,
            "updated_by" => $loggedUser
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => $conn->error
    ]);
}
