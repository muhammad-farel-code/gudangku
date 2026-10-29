@props([
    'status' => 'Aman',
])

@php
    $class = match(strtolower($status)) {
        'aman', 'safe' => 'badge-status-safe',
        'menipis', 'warning', 'low' => 'badge-status-warning',
        'habis', 'danger', 'out' => 'badge-status-danger',
        default => 'badge-status-safe',
    };
@endphp

<span class="badge-status {{ $class }}">
    {{ $status }}
</span>
