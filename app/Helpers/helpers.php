<?php
if (!function_exists('approvalStatus')) {
    function approvalStatus(): array { return ['Approved', 'Pending', 'Rejected', 'Cancelled']; }
}
if (!function_exists('t')) {
    function t(string $english, string $bangla): string {
        return session('portal_lang', 'en') === 'bn' ? $bangla : $english;
    }
}
