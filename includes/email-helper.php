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

/**
 * Send verification email with code
 */
function sendVerificationEmail($toEmail, $code, $userName)
{
    // Dev mode - just log
    if (defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE) {
        error_log("DEV MODE - Email to: $toEmail, Code: $code");
        return true;
    }

    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found, falling back to mail()");
        return sendVerificationEmailFallback($toEmail, $code, $userName);
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($toEmail, $userName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'รหัสยืนยันการสมัคร Babybib';
        $mail->Body = getVerificationEmailTemplate($code, $userName);
        $mail->AltBody = "รหัสยืนยันของคุณคือ: $code (หมดอายุใน 15 นาที)";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Email send failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Fallback using PHP mail() function
 */
function sendVerificationEmailFallback($toEmail, $code, $userName)
{
    $subject = "=?UTF-8?B?" . base64_encode("รหัสยืนยัน Babybib") . "?=";
    $message = getVerificationEmailTemplate($code, $userName);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";

    return @mail($toEmail, $subject, $message, $headers);
}

/**
 * Get beautiful HTML email template
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
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4F46E5 0%, #3B82F6 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">📚 Babybib</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">ระบบสร้างบรรณานุกรม APA 7</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1a1a2e; margin: 0 0 10px; font-size: 22px;">สวัสดีคุณ {$userName} 👋</h2>
                            <p style="color: #1a1a1a; line-height: 1.6; margin: 0 0 30px;">
                                ขอบคุณที่สมัครสมาชิก Babybib!<br>
                                กรุณาใช้รหัสด้านล่างเพื่อยืนยันอีเมลของคุณ
                            </p>
                            
                            <!-- Verification Code Box -->
                            <div style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 16px; padding: 30px; text-align: center; margin-bottom: 30px;">
                                <p style="color: #1a1a1a; font-size: 12px; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 1px;">รหัสยืนยันของคุณ</p>
                                <div style="font-size: 36px; font-weight: 800; color: #4F46E5; letter-spacing: 8px; font-family: monospace;">{$code}</div>
                                <p style="color: #ef4444; font-size: 13px; margin: 15px 0 0;">⏰ รหัสจะหมดอายุใน 15 นาที</p>
                            </div>
                            
                            <p style="color: #333333; font-size: 13px; line-height: 1.6;">
                                หากคุณไม่ได้สมัครสมาชิก Babybib กรุณาเพิกเฉยอีเมลนี้
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #333333; font-size: 12px; margin: 0;">
                                © 2024 Babybib - APA 7 Bibliography Generator<br>
                                <a href="{$siteUrl}" style="color: #4F46E5; text-decoration: none;">เข้าสู่เว็บไซต์</a>
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
 * Send password reset email with code
 */
function sendPasswordResetEmail($toEmail, $code, $userName)
{
    // Dev mode - just log
    if (defined('EMAIL_DEV_MODE') && EMAIL_DEV_MODE) {
        error_log("DEV MODE - Password Reset Email to: $toEmail, Code: $code");
        return true;
    }

    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not found, falling back to mail()");
        return sendPasswordResetEmailFallback($toEmail, $code, $userName);
    }

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($toEmail, $userName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'รหัสรีเซ็ตรหัสผ่าน Babybib';
        $mail->Body = getPasswordResetEmailTemplate($code, $userName);
        $mail->AltBody = "รหัสรีเซ็ตรหัสผ่านของคุณคือ: $code (หมดอายุใน 15 นาที)";

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log("Password reset email send failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Fallback for password reset email
 */
function sendPasswordResetEmailFallback($toEmail, $code, $userName)
{
    $subject = "=?UTF-8?B?" . base64_encode("รหัสรีเซ็ตรหัสผ่าน Babybib") . "?=";
    $message = getPasswordResetEmailTemplate($code, $userName);

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";

    return @mail($toEmail, $subject, $message, $headers);
}

/**
 * Get password reset email template
 */
function getPasswordResetEmailTemplate($code, $userName)
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
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">🔐 Babybib</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0; font-size: 14px;">รีเซ็ตรหัสผ่าน</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="color: #1a1a2e; margin: 0 0 10px; font-size: 22px;">สวัสดีคุณ {$userName}</h2>
                            <p style="color: #1a1a1a; line-height: 1.6; margin: 0 0 30px;">
                                เราได้รับคำขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณ<br>
                                กรุณาใช้รหัสด้านล่างเพื่อตั้งรหัสผ่านใหม่
                            </p>
                            
                            <!-- Reset Code Box -->
                            <div style="background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%); border-radius: 16px; padding: 30px; text-align: center; margin-bottom: 30px; border: 1px solid #FECACA;">
                                <p style="color: #991B1B; font-size: 12px; margin: 0 0 10px; text-transform: uppercase; letter-spacing: 1px;">รหัสรีเซ็ตรหัสผ่าน</p>
                                <div style="font-size: 36px; font-weight: 800; color: #DC2626; letter-spacing: 8px; font-family: monospace;">{$code}</div>
                                <p style="color: #ef4444; font-size: 13px; margin: 15px 0 0;">⏰ รหัสจะหมดอายุใน 15 นาที</p>
                            </div>
                            
                            <p style="color: #333333; font-size: 13px; line-height: 1.6;">
                                หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาเพิกเฉยอีเมลนี้<br>
                                รหัสผ่านของคุณจะยังคงปลอดภัย
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 25px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #333333; font-size: 12px; margin: 0;">
                                © 2024 Babybib - APA 7 Bibliography Generator<br>
                                <a href="{$siteUrl}" style="color: #4F46E5; text-decoration: none;">เข้าสู่เว็บไซต์</a>
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
