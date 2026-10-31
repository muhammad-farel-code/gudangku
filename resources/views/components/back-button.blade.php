@props(['route', 'label' => 'Kembali'])

<a href="{{ $route }}" {{ $attributes->merge(['class' => 'btn btn-gk-outline']) }}>
    <i class="bi bi-arrow-left"></i>
    <span>{{ $label }}</span>
</a>
