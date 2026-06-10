<?php
/**
 * Copy this file to gemini-config.php and paste your Gemini API key.
 * gemini-config.php is gitignored and never pushed to GitHub.
 *
 * Get a key: https://aistudio.google.com/apikey
 * The key should start with AIza...
 */
return [
    'api_key' => 'YOUR_GEMINI_API_KEY_HERE',
    'model' => 'gemini-2.5-flash-lite',
    'max_requests_per_session' => 30,
];
