<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

auth_logout();
redirect_with(url('pages/home.php'), 'You have been logged out.');
