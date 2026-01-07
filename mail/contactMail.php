<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__DIR__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/PHPMailer/src/SMTP.php';
require_once dirname(__DIR__) . '/PHPMailer/src/Exception.php';

// function sendContactMail($data)
// {
//     $mail = new PHPMailer(true);

//     try {
//         $mail->isSMTP();
//         $mail->Host       = 'smtp.gmail.com';
//         $mail->SMTPAuth   = true;
//         $mail->Username   = 'candiddevsinfo@gmail.com';
//         $mail->Password   = 'dagurrqborslwlsx';
//         $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
//         $mail->Port       = 587;

//         $mail->setFrom('candiddevsinfo@gmail.com', 'Contact Form');
//         $mail->addAddress('aneripatel2502@gmail.com');
//         $mail->addReplyTo($data['email'], $data['name']);

//         $mail->isHTML(true);
//         $mail->Subject = 'New Contact Inquiry';

//         $mail->Body = "
//             <h2>Contact Form Submission</h2>
//             <p><b>Name:</b> {$data['name']}</p>
//             <p><b>Email:</b> {$data['email']}</p>
//             <p><b>Phone:</b> {$data['phone']}</p>
//             <p><b>Message:</b><br>{$data['message']}</p>
//         ";

//         $mail->send();
//         return true;
//     } catch (Exception $e) {
//         error_log("Contact Mail Error: {$mail->ErrorInfo}");
//         return false;
//     }
// }

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
        $mail->Subject = 'New Contact Form Submission';

        $mail->Body = "
            <h3>New Contact</h3>
            <p><b>Name:</b> {$data['name']}</p>
            <p><b>Email:</b> {$data['email']}</p>
            <p><b>Phone:</b> {$data['phone']}</p>
            <p><b>Country:</b> {$data['country']}</p>
            <p><b>Enquiry For:</b> {$data['inquiry_for']}</p>
            <p><b>Message:</b><br>{$data['message']}</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Contact Mail Error: ' . $mail->ErrorInfo);
        return false;
    }
}

