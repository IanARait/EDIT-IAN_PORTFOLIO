<?php
/**
 * PHPMailer Wrapper for Contact Form
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/vendor/autoload.php';

/**
 * Send contact form email
 */
function sendContactEmail($name, $email, $company, $budget, $message) {
    $mail = new PHPMailer(true);

    try {
        $settings = \Database::getInstance();
        $smtpHost = getSetting('smtp_host', $settings) ?: 'smtp.gmail.com';
        $smtpPort = (int)(getSetting('smtp_port', $settings) ?: 587);
        $smtpUser = getSetting('smtp_username', $settings);
        $smtpPass = getSetting('smtp_password', $settings);
        $smtpEnc  = getSetting('smtp_encryption', $settings) ?: 'tls';
        $siteEmail = getSetting('email', $settings) ?: 'hello@portfolio.com';

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = $smtpEnc;
        $mail->Port       = $smtpPort;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom($email, sanitize($name));
        $mail->addAddress($siteEmail, 'Portfolio Admin');
        $mail->addReplyTo($email, sanitize($name));

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Message from ' . sanitize($name);

        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Inter', sans-serif; background: #090909; color: #ffffff; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #111111; border: 1px solid #222222; border-radius: 12px; padding: 30px; }
                h2 { color: #00E676; margin-bottom: 20px; border-bottom: 1px solid #222222; padding-bottom: 15px; }
                .field { margin-bottom: 12px; }
                .label { color: #777777; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
                .value { color: #ffffff; font-size: 15px; margin-top: 4px; }
                .message-box { background: #1a1a1a; border: 1px solid #222222; border-radius: 8px; padding: 15px; margin-top: 20px; }
                .footer { margin-top: 25px; padding-top: 15px; border-top: 1px solid #222222; color: #777777; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <h2>New Contact Message</h2>
                <div class=\"field\">
                    <div class=\"label\">Name</div>
                    <div class=\"value\">" . sanitize($name) . "</div>
                </div>
                <div class=\"field\">
                    <div class=\"label\">Email</div>
                    <div class=\"value\">" . sanitize($email) . "</div>
                </div>
                <div class=\"field\">
                    <div class=\"label\">Company</div>
                    <div class=\"value\">" . sanitize($company ?: 'Not specified') . "</div>
                </div>
                <div class=\"field\">
                    <div class=\"label\">Budget</div>
                    <div class=\"value\">" . sanitize($budget ?: 'Not specified') . "</div>
                </div>
                <div class=\"message-box\">
                    <div class=\"label\">Message</div>
                    <div class=\"value\">" . nl2br(sanitize($message)) . "</div>
                </div>
                <div class=\"footer\">
                    Sent via Portfolio Contact Form &bull; " . date('F j, Y \a\t g:i A') . "
                </div>
            </div>
        </body>
        </html>";

        $mail->Body = $htmlBody;
        $mail->AltBody = "Name: $name\nEmail: $email\nCompany: $company\nBudget: $budget\n\n$message";

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to send message. Please try again later.'];
    }
}
