<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

require_once "functions.php";

$action = $_GET['action'] ?? '';

if ($action === 'sendOtp') {
    $data = json_decode(file_get_contents("php://input"), true);
    // $name  = $data['name'] ?? '';
    $email = $data['email'] ?? '';

    if (!$email) {
        echo json_encode(["success" => false, "message" => "Email is required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if (!$admin) {
        echo json_encode(["success" => false, "message" => "Email not exists"]);
        exit;
    }

    $otp = createOtp($email, $conn);
    sendOtpEmail($email, $otp);

    echo json_encode(["success" => true, "message" => "OTP sent successfully"]);
    exit;
}

if ($action === 'verifyOtp') {
    $data = json_decode(file_get_contents("php://input"), true);
    // $name  = $data['name'] ?? ''; 
    $email = $data['email'] ?? '';
    $otp   = $data['otp'] ?? '';

    if (!$email || !$otp) {
        echo json_encode(["success" => false, "message" => "Email, and OTP are required"]);
        exit;
    }

    $storedOtp = verifyOtpCode($email, $otp, $conn);
    if (!$storedOtp) {
        echo json_encode(["success" => false, "message" => "Invalid or expired OTP"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ? ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    global $jwtSecret;
    $payload = [
        "id"    => $admin['id'],
        "email" => $admin['email'],
        "type"  => "admin",
        "iat"   => time(),
        // "exp"   => time() + (60 * 60)
    ];
    $jwt = generateJWT($payload, $jwtSecret);

    $stmt = $conn->prepare("DELETE FROM otp WHERE id = ?");
    $stmt->bind_param("i", $storedOtp['id']);
    $stmt->execute();

    echo json_encode(["success" => true, "token" => $jwt, "admin" => $admin]);
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid action"]);
