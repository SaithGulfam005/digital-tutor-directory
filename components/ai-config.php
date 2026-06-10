<?php
/**
 * Gemini chatbot settings (safe to commit — no secrets here).
 * Add your API key in components/gemini-config.php (copy from gemini-config.example.php).
 * Get a free key at: https://aistudio.google.com/apikey
 */
return [
    'model' => 'gemini-2.5-flash-lite',
    'max_requests_per_session' => 30,
];
