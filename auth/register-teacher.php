<?php
require_once __DIR__ . '/../components/config.php';
header('Location: ' . url('auth/register.php?role=teacher'));
exit;
