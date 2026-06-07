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

function send_app_mail(string $to, string $subject, string $htmlBody): bool
{
    $config = mail_config();
    $fromEmail = $config['from_email'];
    $fromName = $config['from_name'];

    if (!empty($config['use_smtp']) && !empty($config['smtp_pass'])) {
        return send_smtp_mail($to, $subject, $htmlBody, $fromEmail, $fromName, $config);
    }

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . $fromName . ' <' . $fromEmail . ">\r\n";

    return @mail($to, $subject, $htmlBody, $headers);
}

function send_smtp_mail(string $to, string $subject, string $htmlBody, string $fromEmail, string $fromName, array $config): bool
{
    $host = $config['smtp_host'];
    $port = (int) ($config['smtp_port'] ?? 587);
    $user = $config['smtp_user'];
    $pass = $config['smtp_pass'];

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

    $expect = static function (string $response, array $codes) use ($read): bool {
        $code = (int) substr(trim($response), 0, 3);
        return in_array($code, $codes, true);
    };

    if (!$expect($read(), [220])) {
        fclose($socket);
        return false;
    }

    $write('EHLO localhost');
    if (!$expect($read(), [250])) {
        fclose($socket);
        return false;
    }

    $write('STARTTLS');
    if (!$expect($read(), [220])) {
        fclose($socket);
        return false;
    }

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return false;
    }

    $write('EHLO localhost');
    if (!$expect($read(), [250])) {
        fclose($socket);
        return false;
    }

    $write('AUTH LOGIN');
    if (!$expect($read(), [334])) {
        fclose($socket);
        return false;
    }

    $write(base64_encode($user));
    if (!$expect($read(), [334])) {
        fclose($socket);
        return false;
    }

    $write(base64_encode($pass));
    if (!$expect($read(), [235])) {
        fclose($socket);
        return false;
    }

    $write('MAIL FROM:<' . $fromEmail . '>');
    if (!$expect($read(), [250])) {
        fclose($socket);
        return false;
    }

    $write('RCPT TO:<' . $to . '>');
    if (!$expect($read(), [250, 251])) {
        fclose($socket);
        return false;
    }

    $write('DATA');
    if (!$expect($read(), [354])) {
        fclose($socket);
        return false;
    }

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $message = "From: {$fromName} <{$fromEmail}>\r\n";
    $message .= "To: {$to}\r\n";
    $message .= "Subject: {$encodedSubject}\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $htmlBody . "\r\n.";

    fwrite($socket, $message . "\r\n");
    if (!$expect($read(), [250])) {
        fclose($socket);
        return false;
    }

    $write('QUIT');
    fclose($socket);
    return true;
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
