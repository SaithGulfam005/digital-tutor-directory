<?php
/**
 * Email configuration for OTP and notifications.
 * For Gmail: enable 2FA and create an App Password for digitaltutordirectory@gmail.com.
 * Set DTD_SMTP_PASS in the hosting environment; never commit the app password.
 */
declare(strict_types=1);

return [
    'from_email' => 'digitaltutordirectory@gmail.com',
    'from_name' => 'Digital Tutor Directory',
    'contact_email' => 'digitaltutordirectory@gmail.com',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'digitaltutordirectory@gmail.com',
    // Set DTD_SMTP_PASS to the 16-character Gmail App Password.
    'smtp_pass' => (string) (getenv('DTD_SMTP_PASS') ?: ($_SERVER['DTD_SMTP_PASS'] ?? '')),
    'use_smtp' => true,
];
