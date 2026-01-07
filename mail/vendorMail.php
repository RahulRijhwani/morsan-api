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
        $mail->addAddress('purchase@metaltecproducts.com');

        $mail->isHTML(true);
        $mail->Subject = 'New Vendor Form Submission';

        $mail->Body = "
            <h3>New Vendor</h3>
            <p><b>Category:</b> {$data['category']}</p>
            <p><b>Firm Name:</b> {$data['firm_name']}</p>
            <p><b>Address:</b> {$data['address']}</p>
            <p><b>GST No:</b> {$data['gst_no']}</p>
            <p><b>PAN No:</b> {$data['pan_no']}</p>
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

