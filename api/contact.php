<?php
// Contact form handler for SundayLaw.com (local and production)

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendFormEmail($action, $name, $email, $subject, $message, $recipient_email = '', $scriptures = '', $is_public = 0) {
    $mail = new PHPMailer(true);

    // Map form actions to email addresses and passwords
    $emailMap = [
        'contact' => ['email' => 'contact@sundaylaw.com', 'pass' => 'Aloha@1234'],
        'prayer' => ['email' => 'prayer@sundaylaw.com', 'pass' => 'Aloha@1234'],
        'newsletter' => ['email' => 'subscribenewsletter@sundaylaw.com', 'pass' => 'Aloha@1234'],
        'share' => ['email' => 'contact@sundaylaw.com', 'pass' => 'Aloha@1234'],
        'info' => ['email' => 'info@sundaylaw.com', 'pass' => 'Aloha@1234'],
        'help' => ['email' => 'help@sundaylaw.com', 'pass' => 'Aloha@1234'],
    ];
    $to = $emailMap[$action]['email'] ?? 'contact@sundaylaw.com';
    $password = $emailMap[$action]['pass'] ?? '';

    $subjects = [
        'contact' => 'New Contact Form Submission',
        'prayer' => 'New Prayer Request',
        'newsletter' => 'New Newsletter Signup',
        'share' => 'New Share Message',
    ];
    $mailSubject = $subjects[$action] ?? 'New Form Submission';

    $body = "<b>Name:</b> $name<br>";
    $body .= "<b>Email:</b> $email<br>";
    if ($subject) $body .= "<b>Subject:</b> $subject<br>";
    if ($recipient_email) $body .= "<b>Recipient Email:</b> $recipient_email<br>";
    if ($scriptures) $body .= "<b>Scriptures:</b> $scriptures<br>";
    if ($is_public) $body .= "<b>Public Request:</b> Yes<br>";
    $body .= "<b>Message:</b><br>" . nl2br(htmlspecialchars($message));

    try {
        $mail->isSMTP();
        $mail->Host = 'mail.sundaylaw.com';
        $mail->SMTPAuth = true;
        $mail->Username = $to;
        $mail->Password = $password;
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->setFrom($to, 'SundayLaw.com');
        $mail->addAddress($to);
        if ($email) $mail->addReplyTo($email, $name);
        $mail->Subject = $mailSubject;
        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}

// Example usage (replace with your actual form handling logic):
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'contact';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';
    $recipient_email = $_POST['recipient_email'] ?? '';
    $scriptures = $_POST['scriptures'] ?? '';
    $is_public = isset($_POST['is_public']) ? 1 : 0;

    $sent = sendFormEmail($action, $name, $email, $subject, $message, $recipient_email, $scriptures, $is_public);
    if ($sent) {
        echo json_encode(['ok' => true, 'message' => 'Message sent!']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'Failed to send email.']);
    }
    exit;
}

echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
