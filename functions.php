<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once "db.php";

$jwtSecret = "m8ztab604p9zv8aw9iy";

// Function to generate JWT
function generateJWT($payload, $secret)
{
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

    $payloadJson = json_encode($payload);
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));

    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $secret, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
}

// Function to send OTP email using PHPMailer
function sendOtpEmail($toEmail, $otp)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'candiddevsinfo@gmail.com';
        $mail->Password   = 'dagurrqborslwlsx';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('candiddevsinfo@gmail.com', 'OTP Verification');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "<h3>Your OTP is:</h3><p style='font-size:20px;'><b>{$otp}</b></p><p>This code is valid for 5 minutes.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Function to create and save OTP
function createOtp($email, $conn)
{
    $otp = rand(100000, 999999);

    // Delete any existing OTP for this email
    $stmt = $conn->prepare("DELETE FROM otp WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Insert new OTP with created_at timestamp
    $stmt = $conn->prepare("INSERT INTO otp (email, otp, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();

    return $otp;
}

// Function to verify OTP
function verifyOtpCode($email, $otp, $conn)
{
    // First, delete expired OTPs (older than 5 minutes)
    $conn->query("DELETE FROM otp WHERE created_at < (NOW() - INTERVAL 5 MINUTE)");

    // Check if OTP exists and is valid
    $stmt = $conn->prepare("SELECT * FROM otp WHERE email = ? AND otp = ?");
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    $otpRow = $result->fetch_assoc();

    if ($otpRow) {
        // Delete OTP immediately after successful use
        $deleteStmt = $conn->prepare("DELETE FROM otp WHERE id = ?");
        $deleteStmt->bind_param("i", $otpRow['id']);
        $deleteStmt->execute();
    }

    return $otpRow;
}


function base64UrlDecode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function verifyJWT($jwt, $secret) {
    // Split into 3 parts
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        return false;
    }

    list($header64, $payload64, $signatureProvided) = $parts;

    // Decode payload
    $payload = json_decode(base64UrlDecode($payload64), true);

    // Verify signature
    $signature = hash_hmac('sha256', "$header64.$payload64", $secret, true);
    $signatureBase64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    if (!hash_equals($signatureBase64, $signatureProvided)) {
        return false; // invalid signature
    }

    // Check expiration if present
    if (isset($payload['exp']) && time() >= $payload['exp']) {
        return false; // token expired
    }

    return $payload; // valid payload
}
