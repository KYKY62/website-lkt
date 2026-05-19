<?php

return [
    'api_url' => env('DEPARTMENT_NEWS_API_URL', 'https://multisite.langkatkab.go.id/api/v1/all-berita'),
    'timeout' => (int) env('DEPARTMENT_NEWS_TIMEOUT', 8),
    'retry_times' => (int) env('DEPARTMENT_NEWS_RETRY_TIMES', 1),
    'retry_sleep_ms' => (int) env('DEPARTMENT_NEWS_RETRY_SLEEP_MS', 200),
];
