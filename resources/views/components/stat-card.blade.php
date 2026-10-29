@props([
    'variant' => 'light', // 'dark' or 'light'
    'icon' => 'bi-box-seam',
    'iconBg' => null,
    'iconColor' => null,
    'label' => '',
    'value' => '0',
    'trend' => null,
    'trendUp' => true,
    'trendText' => '',
    'gauge' => null, // e.g. 20 (percent)
    'gaugeSub' => 'Persen',
])

@if ($variant === 'dark')
    <div class="gk-stat-card-dark h-100 d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
            <div class="gk-stat-icon-wrap">
                <i class="bi {{ $icon }}"></i>
            </div>
            <i class="bi bi-arrow-up-right gk-stat-arrow"></i>
        </div>
        <div>
            <div class="gk-stat-label">{{ $label }}</div>
            <div class="gk-stat-value mb-2">{{ $value }}</div>
            @if ($trend)
                <div class="d-flex align-items-center">
                    <span class="gk-trend-pill" style="background-color: rgba(216, 242, 58, 0.2); color: var(--gk-accent);">
                        <i class="bi {{ $trendUp ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i> {{ $trend }}
                    </span>
                    @if ($trendText)
                        <span class="gk-trend-subtext" style="color: #a1a1aa;">{{ $trendText }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@else
    <div class="gk-stat-card-light h-100 d-flex flex-column justify-content-between">
        <div class="d-flex justify-content-between align-items-start">
            <div class="gk-stat-icon-wrap" style="background-color: {{ $iconBg ?? 'var(--gk-surface)' }}; color: {{ $iconColor ?? 'var(--gk-text)' }};">
                <i class="bi {{ $icon }}"></i>
            </div>
            @if (!$gauge)
                <i class="bi bi-arrow-up-right gk-stat-arrow"></i>
            @else
                {{-- Half-circle SVG Gauge --}}
                <div class="gk-gauge-container">
                    <svg class="gk-gauge-svg" viewBox="0 0 100 50">
                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="#f1f1f0" stroke-width="8" stroke-linecap="round" />
                        <path d="M 10 50 A 40 40 0 0 1 90 50" fill="none" stroke="{{ $iconColor ?? 'var(--gk-danger)' }}" stroke-width="8" stroke-linecap="round"
                              stroke-dasharray="125.66"
                              stroke-dashoffset="{{ 125.66 - (125.66 * (min(100, max(0, $gauge)) / 100)) }}" />
                    </svg>
                    <div class="gk-gauge-center-text">
                        <span class="gk-gauge-pct">{{ $gauge }}%</span>
                        <span class="gk-gauge-sub">{{ $gaugeSub }}</span>
                    </div>
                </div>
            @endif
        </div>

        <div>
            <div class="gk-stat-label">{{ $label }}</div>
            <div class="gk-stat-value mb-2">{{ $value }}</div>
            @if ($trend)
                <div class="d-flex align-items-center">
                    <span class="gk-trend-pill {{ $trendUp ? 'gk-trend-up' : 'gk-trend-down' }}">
                        <i class="bi {{ $trendUp ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i> {{ $trend }}
                    </span>
                    @if ($trendText)
                        <span class="gk-trend-subtext">{{ $trendText }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endif
