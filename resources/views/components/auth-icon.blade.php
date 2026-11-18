@props(['name'])
<svg {{ $attributes }} xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('user')<circle cx="12" cy="8" r="4"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/>@break
        @case('lock')<rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/>@break
        @case('eye')<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>@break
        @case('eye-off')<path d="m3 3 18 18M10 5.2A11 11 0 0 1 12 5c6.5 0 10 7 10 7a20 20 0 0 1-3 3.8M6 6.5A22 22 0 0 0 2 12s3.5 7 10 7a12 12 0 0 0 5.3-1.3M10 10a3 3 0 0 0 4 4"/>@break
        @case('arrow')<path d="M4 12h16m-6-6 6 6-6 6"/>@break
        @case('bulb')<path d="M9 18h6m-5 3h4M9 15a6 6 0 1 1 6 0v3H9Z"/>@break
        @case('location')<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
        @case('qr')<path d="M3 3h6v6H3zm12 0h6v6h-6zM3 15h6v6H3zm12 0h2v2h-2zm6 0v3h-3v3h-3m6 0h.01M12 3v2m0 5v2H3m9 3v6m6-9h3"/>@break
    @endswitch
</svg>
