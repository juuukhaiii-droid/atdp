<?php

namespace App\Services;

class OfficeNetworkChecker
{
    public function passes(string $ip): bool
    {
        $ranges = $this->allowedRanges();

        if (empty($ranges)) {
            return true; // no ranges configured -> don't block
        }

        foreach ($ranges as $entry) {
            if ($this->matches($ip, $entry)) {
                return true;
            }
        }

        return false;
    }

    public function allowedRanges(): array
    {
        return array_filter(array_map('trim', explode(',', (string) env('OFFICE_WIFI_NETWORKS', ''))));
    }

    private function matches(string $ip, string $entry): bool
    {
        if ($entry === '') {
            return false;
        }

        if (str_ends_with($entry, '.')) {
            return str_starts_with($ip, $entry);
        }

        return $ip === $entry;
    }
}