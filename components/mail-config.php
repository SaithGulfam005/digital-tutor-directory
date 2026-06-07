<?php
/**
 * Email configuration for OTP and notifications.
 * For Gmail: enable 2FA and create an App Password for digitaltutordirectory@gmail.com
 */
declare(strict_types=1);

return [
    'from_email' => 'digitaltutordirectory@gmail.com',
    'from_name' => 'Digital Tutor Directory',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'digitaltutordirectory@gmail.com',
    // Set your Gmail App Password here (16 characters, no spaces)
    'smtp_pass' => 'zmmfiewpewxqhtwq',
    'use_smtp' => true,
];
