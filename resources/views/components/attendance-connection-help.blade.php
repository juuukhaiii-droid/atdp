@php
    $secureBase = config('app.url');
    $secureLink = parse_url($secureBase, PHP_URL_SCHEME) === 'https'
        ? rtrim($secureBase, '/') . request()->getPathInfo() : null;
@endphp
<section class="attendance-connection-help" data-device-help hidden aria-label="Connection help">
    <span class="connection-help-icon" aria-hidden="true">!</span>
    <h2>ត្រូវការតំណ HTTPS ដើម្បីស្កេន</h2>
    <p>អាសយដ្ឋាន HTTP នេះមិនអាចប្រើកាមេរ៉ា និង GPS បានទេ ទោះបីប្រើបណ្ដាញ WiFi ដូចគ្នាក៏ដោយ។ មិនទាន់បានកត់ត្រាវត្តមានទេ។</p>
    @if($secureLink)
        <a href="{{ $secureLink }}" class="connection-help-link">បើកទំព័រសុវត្ថិភាពដើម្បីបន្ត</a>
    @else
        <p class="connection-help-action">សូមស្នើសុំតំណ HTTPS ពីអ្នកគ្រប់គ្រង រួចបើកក្នុង Safari ឬ Chrome។</p>
    @endif
</section>
