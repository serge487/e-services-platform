@extends('layouts.public')
@section('title', 'Services')
@section('page-title', 'Browse Services')
@push('styles')
<style>
    .services-page {
        --services-green: #0a5c4a;
        --services-green-dark: #064e3b;
        --services-green-soft: #ecfdf5;
        --services-mint: #6ee7b7;
        --services-border: #d9eee7;
        --services-muted: #64748b;
        --services-ink: #1f2937;
    }

    .services-page .filter-panel,
    .services-page .service-card,
    .services-page .empty-panel {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 8px rgba(15, 23, 42, 0.06);
    }

    .services-page .filter-panel {
        padding: 1rem;
    }

    .services-page .filter-label {
        color: var(--services-green);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.7px;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }

    .services-page .form-select {
        min-height: 42px;
        border-color: #cbd5e1;
        color: var(--services-ink);
        font-size: 0.9rem;
        border-radius: 7px;
    }

    .services-page .form-select:focus {
        border-color: var(--services-green);
        box-shadow: 0 0 0 0.2rem rgba(10, 92, 74, 0.14);
    }

    .services-page .btn-filter {
        min-height: 42px;
        background: var(--services-green);
        border: 0;
        color: #fff;
        border-radius: 7px;
        font-weight: 700;
        transition: background 0.15s, transform 0.15s;
    }

    .services-page .btn-filter:hover,
    .services-page .btn-filter:focus {
        background: var(--services-green-dark);
        color: #fff;
    }

    .services-page .service-card {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transition: border-color 0.15s, box-shadow 0.15s, transform 0.15s;
    }

    .services-page .service-card:hover {
        border-color: var(--services-border);
        box-shadow: 0 4px 14px rgba(10, 92, 74, 0.12);
        transform: translateY(-1px);
    }

    .services-page .service-card-body {
        padding: 1.05rem;
        flex: 1;
    }

    .services-page .service-title {
        color: var(--services-ink);
        font-size: 0.98rem;
        font-weight: 750;
        line-height: 1.35;
        margin-bottom: 0.45rem;
    }

    .services-page .service-description {
        color: #475569;
        font-size: 0.84rem;
        line-height: 1.5;
        min-height: 3.75rem;
        margin-bottom: 0.75rem;
    }

    .services-page .service-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.9rem;
    }

    .services-page .badge-office {
        background: var(--services-green-soft);
        border: 1px solid var(--services-border);
        color: var(--services-green);
        border-radius: 6px;
        font-weight: 800;
        font-size: 0.72rem;
        padding: 0.28rem 0.45rem;
    }

    .services-page .badge-category {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        color: #475569;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.72rem;
        padding: 0.28rem 0.45rem;
    }

    .services-page .service-meta {
        border-top: 1px solid #eef2f7;
        border-bottom: 1px solid #eef2f7;
        padding: 0.75rem 0;
        margin-bottom: 0.8rem;
    }

    .services-page .meta-label {
        color: var(--services-muted);
        display: block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .services-page .meta-value {
        color: #0f172a;
        font-size: 0.88rem;
        font-weight: 800;
    }

    .services-page .meta-value.price {
        color: var(--services-green);
    }

    .services-page .document-note {
        background: #f8fafc;
        border: 1px solid #edf2f7;
        border-radius: 7px;
        color: var(--services-muted);
        font-size: 0.79rem;
        padding: 0.6rem 0.7rem;
    }

    .services-page .service-card-footer {
        background: #fff;
        border-top: 1px solid #f1f5f9;
        padding: 0.9rem 1.05rem 1.05rem;
    }

    .services-page .btn-service-details {
        border: 1.5px solid var(--services-green);
        color: var(--services-green);
        border-radius: 7px;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 0.48rem 0.75rem;
    }

    .services-page .btn-service-details:hover,
    .services-page .btn-service-details:focus {
        background: var(--services-green);
        color: #fff;
    }

    .services-page .empty-panel {
        color: var(--services-muted);
        padding: 3rem 1rem;
        text-align: center;
    }

    .services-page .empty-panel i {
        color: var(--services-green);
    }

    .services-page .pagination .page-link {
        color: var(--services-green);
        border-color: #dbe7e2;
    }

    .services-page .pagination .page-link:hover {
        background: var(--services-green-soft);
        color: var(--services-green-dark);
    }

    .services-page .pagination .active .page-link {
        background: var(--services-green);
        border-color: var(--services-green);
        color: #fff;
    }
</style>
@endpush
@section('content')
<div class="container-fluid services-page">
    <!-- Filters -->
    <div class="filter-panel mb-4">
        <form method="GET" action="{{ route('citizen.services') }}" class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label for="office_id" class="form-label filter-label">Filter by Office</label>
                    <select id="office_id" name="office_id" class="form-select">
                        <option value="">-- All Offices --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ $officeFilter == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-5">
                    <label for="category_id" class="form-label filter-label">Filter by Category</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">-- All Categories --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $categoryFilter == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-filter w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
    </div>

    <!-- Services Grid -->
    @if($services->count() > 0)
        <div class="row g-4">
            @foreach($services as $service)
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-card-body">
                            <h5 class="service-title">{{ $service->name }}</h5>
                            <p class="service-description">{{ $service->description }}</p>
                            
                            <div class="service-badges">
                                <span class="badge-office">{{ $service->office->name }}</span>
                                <span class="badge-category">{{ $service->category->name }}</span>
                            </div>

                            <div class="row g-2 service-meta">
                                <div class="col-6">
                                    <span class="meta-label">Price</span>
                                    <span class="meta-value price">${{ number_format($service->price, 2) }}</span>
                                </div>
                                <div class="col-6">
                                    <span class="meta-label">Duration</span>
                                    <span class="meta-value">{{ $service->duration_days }} days</span>
                                </div>
                            </div>

                            @if($service->required_documents && count($service->required_documents) > 0)
                                <div class="document-note">
                                    <i class="bi bi-file-earmark-text me-1"></i>
                                    {{ count($service->required_documents) }} document(s) required
                                </div>
                            @endif
                        </div>
                        <div class="service-card-footer">
                            <a href="{{ route('citizen.services.show', $service) }}" class="btn btn-service-details btn-sm w-100">
                                <i class="bi bi-eye"></i> View Details & Request
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $services->render() }}
        </div>
    @else
        <div class="empty-panel">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            <p class="mb-0">No services found. Try adjusting your filters.</p>
        </div>
    @endif
</div>
@endsection
