<?php
/**
 * Optional local overrides. The chatbot loads components/ai-config.php by default.
 * Copy this file to gemini-config.php only if you need to override settings locally.
 */
return [
    'api_key' => 'YOUR_GEMINI_API_KEY_HERE',
    'model' => 'gemini-2.0-flash-lite',
    'max_requests_per_session' => 30,
];
