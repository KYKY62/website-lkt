<?php

return [
    'connection' => env('LEGACY_DB_CONNECTION', 'legacy'),
    'base_url' => rtrim((string) env('LEGACY_BASE_URL', 'https://www.langkatkab.go.id'), '/'),
];
