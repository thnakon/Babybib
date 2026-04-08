<?php

/**
 * Babybib - Email Helper Functions
 * ==================================
 * Uses PHPMailer for SMTP email sending
 */

require_once __DIR__ . '/email-config.php';

// Load PHPMailer
$phpmailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
$exceptionPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
$smtpPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

if (file_exists($phpmailerPath) && file_exists($exceptionPath) && file_exists($smtpPath)) {
    require_once $exceptionPath;
    require_once $phpmailerPath;
    require_once $smtpPath;
}


function createMailer(array $overrides = [])
{
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return null;
    }

    $host = trim((string) ($overrides['smtp_host'] ?? SMTP_HOST));
    $port = (int) ($overrides['smtp_port'] ?? SMTP_PORT);
    $secure = trim((string) ($overrides['smtp_secure'] ?? SMTP_SECURE));
    $username = trim((string) ($overrides['smtp_username'] ?? SMTP_USERNAME));
    $password = (string) ($overrides['smtp_password'] ?? SMTP_PASSWORD);
    $fromEmail = trim((string) ($overrides['email_from'] ?? EMAIL_FROM ?: $username));
    $fromName = trim((string) ($overrides['email_from_name'] ?? EMAIL_FROM_NAME));

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $username;
    $mail->Password = $password;
    $mail->SMTPSecure = $secure;
    $mail->Port = $port;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($fromEmail, $fromName !== '' ? $fromName : 'Babybib');

    return $mail;
}


function sendMailMessage($toEmail, $toName, $subject, $htmlBody, $altBody, array $overrides = [])
{
    if (defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE) {
        error_log("DEV MODE - Email to: $toEmail, Subject: $subject");
        return [
            'success' => true,
            'error' => null
        ];
    }

    try {
        $mail = createMailer($overrides);
        if (!$mail) {
            return [
                'success' => false,
                'error' => 'Mailer library unavailable'
            ];
        }

        $mail->addAddress($toEmail, $toName ?: $toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $altBody;
        $mail->send();

        return [
            'success' => true,
            'error' => null
        ];
    } catch (\Exception $e) {
        error_log("Email send failed: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}



/**
 * Send email verification code
 */
function sendVerificationEmail($toEmail, $code, $userName)
{
    // Dev mode - just log
    if (defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE) {
        error_log("DEV MODE - Verification Email to: $toEmail, Code: $code");
        return true;
    }

    $result = sendMailMessage(
        $toEmail,
        $userName,
        'รหัสยืนยันการสมัครสมาชิก Babybib',
        getVerificationEmailTemplate($code, $userName),
        "รหัสยืนยันการสมัครสมาชิกของคุณคือ: $code"
    );

    return $result['success'];
}

/**
 * Send password reset email with link
 */
function sendPasswordResetLinkEmail($toEmail, $token, $userName)
{
    // Dev mode - just log
    if (defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE) {
        error_log("DEV MODE - Password reset requested for: $toEmail");
        return true;
    }

    $result = sendMailMessage(
        $toEmail,
        $userName,
        'รีเซ็ตรหัสผ่าน Babybib',
        getPasswordResetLinkTemplate($token, $userName),
        "กรุณาใช้ลิงก์นี้เพื่อรีเซ็ตรหัสผ่านของคุณ: " . SITE_URL . "/reset-password.php?token=$token"
    );

    return $result['success'];
}

/**
 * Get verification email template
 */
function getVerificationEmailTemplate($code, $userName)
{
    $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost/babybib_db';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" max-width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; max-width: 600px;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">✨ Babybib</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">ยืนยันการสมัครสมาชิก</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1a1a2e; margin: 0 0 10px; font-size: 22px;">สวัสดีคุณ {$userName}</h2>
                            <p style="color: #1a1a1a; line-height: 1.6; margin: 0 0 30px;">
                                ยินดีต้อนรับสู่ Babybib!<br>
                                กรุณาใช้รหัสยืนยันด้านล่างเพื่อเปิดใช้งานบัญชีของคุณ
                            </p>
                            <div style="background: #F5F3FF; border-radius: 16px; padding: 30px; text-align: center; margin-bottom: 30px; border: 1px solid #DDD6FE;">
                                <p style="color: #5B21B6; font-size: 12px; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 1px;">รหัสยืนยันของคุณ</p>
                                <div style="font-size: 36px; font-weight: 800; color: #7C3AED; letter-spacing: 8px; font-family: monospace;">{$code}</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #666666; font-size: 12px; margin: 0;">
                                © 2024 Babybib - APA 7<sup>th</sup> Bibliography Generator
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Get password reset link template
 */
function getPasswordResetLinkTemplate($token, $userName)
{
    $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost/babybib_db';
    $resetUrl = $siteUrl . "/reset-password.php?token=" . $token;

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f7; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" max-width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; max-width: 600px;">
                    <tr>
                        <td style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">🔐 Babybib</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">รีเซ็ตรหัสผ่าน</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1a1a2e; margin: 0 0 10px; font-size: 22px;">สวัสดีคุณ {$userName}</h2>
                            <p style="color: #1a1a1a; line-height: 1.6; margin: 0 0 30px;">
                                เราได้รับคำขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณ<br>
                                กรุณาคลิกปุ่มด้านล่างเพื่อตั้งรหัสผ่านใหม่
                            </p>
                            <div style="text-align: center; margin-bottom: 30px;">
                                <!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{$resetUrl}" style="height:50px;v-text-anchor:middle;width:200px;" arcsize="20%" stroke="f" fillcolor="#DC2626">
                                    <w:anchorlock/>
                                    <center>
                                <![endif]-->
                                <a href="{$resetUrl}" style="background-color:#DC2626;border-radius:10px;color:#ffffff;display:inline-block;font-family:sans-serif;font-size:16px;font-weight:bold;line-height:50px;text-align:center;text-decoration:none;width:200px;-webkit-text-size-adjust:none;">รีเซ็ตรหัสผ่าน</a>
                                <!--[if mso]>
                                    </center>
                                </v:roundrect>
                                <![endif]-->
                            </div>
                            <p style="color: #666666; font-size: 13px; line-height: 1.6; text-align: center;">
                                หรือคัดลอกลิงก์ด้านล่างไปวางในเบราว์เซอร์:<br>
                                <span style="word-break: break-all; color: #8B5CF6;">{$resetUrl}</span>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Send password reset email with code (Legacy/Internal)
 */
function sendPasswordResetEmail($toEmail, $code, $userName)
{
    // Keeping this for compatibility if needed elsewhere
    // but the main flow will use Reset Link
    $result = sendMailMessage(
        $toEmail,
        $userName,
        'รหัสรีเซ็ตรหัสผ่าน Babybib',
        getPasswordResetEmailTemplate($code, $userName),
        "รหัสรีเซ็ตรหัสผ่านของคุณคือ: $code"
    );

    return $result['success'];
}


function sendSmtpTestEmail($toEmail, array $overrides = [])
{
    $toEmail = trim($toEmail);
    if ($toEmail === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'error' => 'Invalid recipient email'
        ];
    }

    $subject = 'Babybib SMTP Test Email';
    $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost/babybib_db';
    $timestamp = date('Y-m-d H:i:s');
    $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; padding:24px; color:#111827;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; padding:32px;">
        <h2 style="margin-top:0;">Babybib SMTP Test</h2>
        <p>อีเมลฉบับนี้ถูกส่งจากหน้าตั้งค่าระบบเพื่อทดสอบการเชื่อมต่อ SMTP</p>
        <ul>
            <li>เวลาเซิร์ฟเวอร์: {$timestamp}</li>
            <li>เว็บไซต์: {$siteUrl}</li>
        </ul>
        <p>หากคุณได้รับอีเมลนี้ แปลว่าการตั้งค่า SMTP ใช้งานได้</p>
    </div>
</body>
</html>
HTML;
    $altBody = "Babybib SMTP test email sent at {$timestamp} from {$siteUrl}";

    return sendMailMessage($toEmail, $toEmail, $subject, $htmlBody, $altBody, $overrides);
}

/**
 * Fallback for simple mail
 */
function sendPasswordResetEmailFallback($toEmail, $code, $userName)
{
    $subject = "=?UTF-8?B?" . base64_encode("รหัสรีเซ็ตรหัสผ่าน Babybib") . "?=";
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
    return @mail($toEmail, $subject, getPasswordResetEmailTemplate($code, $userName), $headers);
}

/**
 * Get password reset email template (Original Code Based)
 */
function getPasswordResetEmailTemplate($code, $userName)
{
    // ... logic remains same as old getPasswordResetEmailTemplate ...
    $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost/babybib_db';
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: sans-serif;">
    <div style="padding: 20px; border: 1px solid #eee; border-radius: 10px;">
        <h2>สวัสดีคุณ {$userName}</h2>
        <p>รหัสรีเซ็ตรหัสผ่านของคุณคือ:</p>
        <h1 style="color: #DC2626; letter-spacing: 5px;">{$code}</h1>
        <p>รหัสจะหมดอายุใน 15 นาที</p>
    </div>
</body>
</html>
HTML;
}
