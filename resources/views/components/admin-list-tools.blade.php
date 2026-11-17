@props(['target', 'title', 'count'])
<div class="admin-list-tools">
    <div><strong>{{ $title }}</strong><span class="list-count">{{ $count }} total</span></div>
    <label class="admin-list-search" for="{{ $target }}-search"><i class="fas fa-search" aria-hidden="true"></i><span
            class="sr-only">Search {{ strtolower($title) }}</span><input id="{{ $target }}-search" type="search"
            placeholder="Search {{ strtolower($title) }}…" data-admin-search="{{ $target }}"
            aria-controls="{{ $target }}" autocomplete="off"></label>
</div>