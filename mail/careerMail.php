<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// require_once dirname(__DIR__) . '/PHPMailer/src/PHPMailer.php';
// require_once dirname(__DIR__) . '/PHPMailer/src/SMTP.php';
// require_once dirname(__DIR__) . '/PHPMailer/src/Exception.php';

function sendContactMail($data)
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

        $mail->setFrom('candiddevsinfo@gmail.com', 'Contact Form');
        $mail->addAddress('aneripatel2502@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'New Career Form Submission';

        $mail->Body = "
            <h3>New Career</h3>
            <p><b>Name:</b> {$data['name']}</p>
            <p><b>Role:</b> {$data['role']}</p>
            <p><b>Email:</b> {$data['email']}</p>
            <p><b>Phone:</b> {$data['phone']}</p>
            <p><b>Message:</b><br>{$data['message']}</p>
            <p><b>Document:</b> {$data['document']}</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Contact Mail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

