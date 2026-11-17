<?php
return [
    // Leave empty on a direct server. Configure only proxy IPs whose headers you trust.
    'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))))),
];
