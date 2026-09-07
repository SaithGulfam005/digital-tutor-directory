<?php
declare(strict_types=1);

function mail_config(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/mail-config.php';
    }
    return $config;
}

function send_app_mail(string $to, string $subject, string $htmlBody, ?string $replyTo = null): bool
{
    $config = mail_config();
    $fromEmail = trim((string) ($config['from_email'] ?? ''));
    $fromName = trim((string) ($config['from_name'] ?? SITE_NAME));

    if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        error_log('Mail configuration error: invalid recipient or sender address.');
        return false;
    }

    if (!empty($config['use_smtp']) && !empty($config['smtp_pass'])) {
        return send_smtp_mail($to, $subject, $htmlBody, $fromEmail, $fromName, $config, $replyTo);
    }

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . $fromName . ' <' . $fromEmail . ">\r\n";
    if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers .= 'Reply-To: ' . $replyTo . "\r\n";
    }

    $sent = mail($to, $subject, $htmlBody, $headers);
    if (!$sent) {
        error_log('PHP mail() failed. Configure SMTP in components/mail-config.php.');
    }
    return $sent;
}

function send_smtp_mail(string $to, string $subject, string $htmlBody, string $fromEmail, string $fromName, array $config, ?string $replyTo = null): bool
{
    $host = trim((string) ($config['smtp_host'] ?? ''));
    $port = (int) ($config['smtp_port'] ?? 587);
    $user = trim((string) ($config['smtp_user'] ?? ''));
    $pass = (string) ($config['smtp_pass'] ?? '');

    if ($host === '' || $port < 1 || $user === '' || $pass === '') {
        error_log('SMTP configuration error: host, port, username, and password are required.');
        return false;
    }

    $socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 20);
    if (!$socket) {
        error_log("SMTP connect failed: {$errstr} ({$errno})");
        return false;
    }

    stream_set_timeout($socket, 20);

    $read = static function () use ($socket): string {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    };

    $write = static function (string $command) use ($socket): void {
        fwrite($socket, $command . "\r\n");
    };

    $expect = static function (string $response, array $codes, string $phase): bool {
        $code = (int) substr(trim($response), 0, 3);
        if (!in_array($code, $codes, true)) {
            error_log("SMTP {$phase} failed: " . trim($response));
            return false;
        }
        return true;
    };

    if (!$expect($read(), [220], 'connect')) {
        fclose($socket);
        return false;
    }

    $write('EHLO localhost');
    if (!$expect($read(), [250], 'EHLO')) {
        fclose($socket);
        return false;
    }

    $write('STARTTLS');
    if (!$expect($read(), [220], 'STARTTLS')) {
        fclose($socket);
        return false;
    }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        error_log('SMTP TLS negotiation failed: ' . (openssl_error_string() ?: 'unknown OpenSSL error'));
        fclose($socket);
        return false;
    }

    $write('EHLO localhost');
    if (!$expect($read(), [250], 'EHLO after TLS')) {
        fclose($socket);
        return false;
    }

    $write('AUTH LOGIN');
    if (!$expect($read(), [334], 'AUTH LOGIN')) {
        fclose($socket);
        return false;
    }

    $write(base64_encode($user));
    if (!$expect($read(), [334], 'SMTP username')) {
        fclose($socket);
        return false;
    }

    $write(base64_encode($pass));
    if (!$expect($read(), [235], 'SMTP password')) {
        fclose($socket);
        return false;
    }

    $write('MAIL FROM:<' . $fromEmail . '>');
    if (!$expect($read(), [250], 'MAIL FROM')) {
        fclose($socket);
        return false;
    }

    $write('RCPT TO:<' . $to . '>');
    if (!$expect($read(), [250, 251], 'RCPT TO')) {
        fclose($socket);
        return false;
    }

    $write('DATA');
    if (!$expect($read(), [354], 'DATA')) {
        fclose($socket);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $message = "From: {$fromName} <{$fromEmail}>\r\n";
    $message .= "To: {$to}\r\n";
    if ($replyTo !== null && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $message .= "Reply-To: {$replyTo}\r\n";
    }
    $message .= "Subject: {$encodedSubject}\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= str_replace("\r\n.", "\r\n..", str_replace("\n.", "\n..", $htmlBody)) . "\r\n.";

    fwrite($socket, $message . "\r\n");
    if (!$expect($read(), [250], 'message body')) {
        fclose($socket);
        return false;
    }

    $write('QUIT');
    fclose($socket);
    return true;
}

function build_verification_email(string $otp): string
{
    $year = date('Y');
    $site = SITE_NAME;

    return <<<HTML
<html>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">
  <div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">
    <h2 style="color:#0d6efd;margin-top:0;">{$site}</h2>
    <p>Thanks for registering. Use this 6-digit code to verify your email address and activate your account:</p>
    <div style="background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:20px;text-align:center;margin:20px 0;">
      <div style="font-size:32px;font-weight:bold;letter-spacing:6px;color:#0d6efd;">{$otp}</div>
      <p style="margin:10px 0 0;color:#666;font-size:13px;">Valid for 10 minutes</p>
    </div>
    <p style="font-size:14px;color:#666;">If you did not create an account, you can safely ignore this message.</p>
    <p style="font-size:12px;color:#999;">&copy; {$year} {$site}</p>
  </div>
</body>
</html>
HTML;
}

function build_otp_email(string $otp): string
{
    $year = date('Y');
    $site = SITE_NAME;

    return <<<HTML
<html>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">
  <div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">
    <h2 style="color:#0d6efd;margin-top:0;">{$site}</h2>
    <p>We received a request to reset your password. Use this OTP to verify your email:</p>
    <div style="background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:20px;text-align:center;margin:20px 0;">
      <div style="font-size:32px;font-weight:bold;letter-spacing:6px;color:#0d6efd;">{$otp}</div>
      <p style="margin:10px 0 0;color:#666;font-size:13px;">Expires in 10 minutes</p>
    </div>
    <p style="font-size:14px;color:#666;">If you did not request this, you can ignore this email.</p>
    <p style="font-size:12px;color:#999;">&copy; {$year} {$site}</p>
  </div>
</body>
</html>
HTML;
}

function contact_inbox_email(): string
{
    $config = mail_config();
    return trim((string) ($config['contact_email'] ?? $config['from_email'] ?? ''));
}

function send_admin_notification(string $subject, string $htmlBody): bool
{
    $adminEmail = contact_inbox_email();
    if ($adminEmail === '') {
        error_log('Admin notification skipped: contact email is not configured.');
        return false;
    }
    return send_app_mail($adminEmail, $subject, $htmlBody);
}

function build_contact_email(string $name, string $email, string $subject, string $message): string
{
    $year = date('Y');
    $site = SITE_NAME;
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $safeMessage = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

    return <<<HTML
<html>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">
  <div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">
    <h2 style="color:#0d6efd;margin-top:0;">New contact message</h2>
    <p style="margin:0 0 8px;"><strong>Site:</strong> {$site}</p>
    <p style="margin:0 0 8px;"><strong>Name:</strong> {$safeName}</p>
    <p style="margin:0 0 8px;"><strong>Email:</strong> {$safeEmail}</p>
    <p style="margin:0 0 16px;"><strong>Subject:</strong> {$safeSubject}</p>
    <div style="background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:16px;">
      <p style="margin:0 0 8px;font-weight:bold;">Message</p>
      <p style="margin:0;">{$safeMessage}</p>
    </div>
    <p style="font-size:12px;color:#999;margin-top:20px;">&copy; {$year} {$site}</p>
  </div>
</body>
</html>
HTML;
}

function build_registration_admin_email(string $name, string $email, string $role): string
{
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $safeRole = htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8');
    return '<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">'
        . '<div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">'
        . '<h2 style="color:#0d6efd;margin-top:0;">New registration</h2>'
        . '<p>A new ' . $safeRole . ' account was registered on ' . htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') . '.</p>'
        . '<p><strong>Name:</strong> ' . $safeName . '<br><strong>Email:</strong> ' . $safeEmail . '<br><strong>Role:</strong> ' . $safeRole . '</p>'
        . '</div></body></html>';
}

function build_course_submitted_admin_email(string $teacherName, string $courseTitle, float $price): string
{
    $safeTeacher = htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8');
    $safeCourse = htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8');
    return '<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">'
        . '<div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">'
        . '<h2 style="color:#0d6efd;margin-top:0;">Course awaiting approval</h2>'
        . '<p>Teacher <strong>' . $safeTeacher . '</strong> submitted a course for review.</p>'
        . '<p><strong>Course:</strong> ' . $safeCourse . '<br><strong>Price:</strong> $' . number_format($price, 2) . '</p>'
        . '</div></body></html>';
}

function build_course_approved_email(string $teacherName, string $courseTitle): string
{
    return '<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">'
        . '<div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">'
        . '<h2 style="color:#198754;margin-top:0;">Course approved</h2>'
        . '<p>Hello ' . htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Your course <strong>' . htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') . '</strong> has been approved and is now published.</p>'
        . '</div></body></html>';
}

function build_teacher_approved_email(string $teacherName): string
{
    return '<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">'
        . '<div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">'
        . '<h2 style="color:#198754;margin-top:0;">Teacher account approved</h2>'
        . '<p>Hello ' . htmlspecialchars($teacherName, ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Your teacher account has been approved. You can now log in and publish courses.</p>'
        . '</div></body></html>';
}

function build_payment_submitted_email(string $studentName, string $courseTitle, float $amount, string $reference): string
{
    return '<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">'
        . '<div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">'
        . '<h2 style="color:#0d6efd;margin-top:0;">Payment submitted</h2>'
        . '<p>Hello ' . htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') . ',</p>'
        . '<p>Your payment submission for <strong>' . htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') . '</strong> is awaiting admin verification.</p>'
        . '<p><strong>Amount:</strong> $' . number_format($amount, 2) . '<br><strong>Payment reference:</strong> ' . htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div></body></html>';
}

function build_payment_admin_email(string $studentName, string $courseTitle, float $amount, string $reference, bool $confirmed = false): string
{
    $heading = $confirmed ? 'Payment confirmed' : 'New course payment';
    $message = $confirmed ? 'A payment has been confirmed by an administrator.' : 'A payment requires review.';
    return '<html><body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">'
        . '<div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">'
        . '<h2 style="color:#0d6efd;margin-top:0;">' . $heading . '</h2>'
        . '<p>' . $message . '</p>'
        . '<p><strong>Student:</strong> ' . htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') . '<br><strong>Course:</strong> ' . htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8') . '<br><strong>Amount:</strong> $' . number_format($amount, 2) . '<br><strong>Reference:</strong> ' . htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div></body></html>';
}

function build_payment_approved_email(string $studentName, string $courseTitle, float $amount, string $reference): string
{
        $year = date('Y');
        $site = SITE_NAME;
        $safeName = htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8');
        $safeCourseTitle = htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8');
        $safeReference = htmlspecialchars($reference, ENT_QUOTES, 'UTF-8');
        $formattedAmount = number_format($amount, 2);

        return <<<HTML
<html>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">
    <div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">
        <h2 style="color:#198754;margin-top:0;">Payment approved</h2>
        <p>Hello {$safeName},</p>
        <p>Your payment has been approved and your enrollment is now active.</p>
        <div style="background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:16px;margin:20px 0;">
            <p style="margin:0 0 8px;"><strong>Course:</strong> {$safeCourseTitle}</p>
            <p style="margin:0 0 8px;"><strong>Amount:</strong> {$formattedAmount}</p>
            <p style="margin:0;"><strong>Payment reference:</strong> {$safeReference}</p>
        </div>
        <p>You can now access the course from your student dashboard.</p>
        <p style="font-size:12px;color:#999;">&copy; {$year} {$site}</p>
    </div>
</body>
</html>
HTML;
}

function build_payment_rejected_email(string $studentName, string $courseTitle, float $amount, string $reference, string $reason = ''): string
{
        $year = date('Y');
        $site = SITE_NAME;
        $safeName = htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8');
        $safeCourseTitle = htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8');
        $safeReference = htmlspecialchars($reference, ENT_QUOTES, 'UTF-8');
        $safeReason = htmlspecialchars(trim($reason) !== '' ? $reason : 'No reason was provided.', ENT_QUOTES, 'UTF-8');
        $formattedAmount = number_format($amount, 2);

        return <<<HTML
<html>
<body style="font-family:Arial,sans-serif;line-height:1.6;color:#333;">
    <div style="max-width:600px;margin:0 auto;padding:24px;background:#f9fafb;border-radius:8px;">
        <h2 style="color:#dc3545;margin-top:0;">Payment not approved</h2>
        <p>Hello {$safeName},</p>
        <p>Unfortunately, your payment could not be approved.</p>
        <div style="background:#fff;border:1px solid #dee2e6;border-radius:8px;padding:16px;margin:20px 0;">
            <p style="margin:0 0 8px;"><strong>Course:</strong> {$safeCourseTitle}</p>
            <p style="margin:0 0 8px;"><strong>Amount:</strong> {$formattedAmount}</p>
            <p style="margin:0 0 8px;"><strong>Payment reference:</strong> {$safeReference}</p>
            <p style="margin:0;"><strong>Reason:</strong> {$safeReason}</p>
        </div>
        <p>Please contact support if you need help with this payment.</p>
        <p style="font-size:12px;color:#999;">&copy; {$year} {$site}</p>
    </div>
</body>
</html>
HTML;
}
