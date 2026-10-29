@if (session('success'))
    <div class="alert-gk alert-gk-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
        <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert-gk alert-gk-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
        <div class="flex-grow-1 fw-medium">{{ session('error') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert-gk alert-dismissible fade show shadow-sm" style="background-color: var(--gk-warning-bg); color: #855d04; border-color: #fef08a;" role="alert">
        <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
        <div class="flex-grow-1 fw-medium">{{ session('warning') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('info'))
    <div class="alert-gk alert-dismissible fade show shadow-sm" style="background-color: var(--gk-info-bg); color: #1e40af; border-color: #bfdbfe;" role="alert">
        <i class="bi bi-info-circle-fill fs-5 text-primary"></i>
        <div class="flex-grow-1 fw-medium">{{ session('info') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
