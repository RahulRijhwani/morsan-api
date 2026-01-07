<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/PHPMailer/src/SMTP.php';
require_once dirname(__DIR__) . '/PHPMailer/src/Exception.php';

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
        $mail->Subject = 'New Dealer Form Submission';

        $mail->Body = "
            <h3>New Dealer</h3>
            <p><b>Firm Name:</b> {$data['firm_name']}</p>
            <p><b>Address:</b> {$data['address']}</p>
            <p><b>City:</b> {$data['city']}</p>
            <p><b>District:</b> {$data['district']}</p>
            <p><b>State:</b> {$data['state']}</p>
            <p><b>Pin Code:</b> {$data['pin_code']}</p>
            <p><b>Country:</b> {$data['country']}</p>
            <h3>Contact Person Details</h3>
            <p><b>First Name:</b> {$data['first_name']}</p>
            <p><b>Last Name:</b> {$data['last_name']}</p>
            <p><b>Email:</b> {$data['email']}</p>
            <p><b>Phone:</b> {$data['phone']}</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Contact Mail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

