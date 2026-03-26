@extends('municipality.layouts.app')
@section('title', 'Edit Service')
@section('page-title', 'Edit Service')

@section('content')

<div class="mb-4">
    <a href="{{ route('municipality.services', absolute: false) }}" class="text-decoration-none text-muted small">
        <i class="bi bi-arrow-left me-1"></i>Back to Services
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-2"></i>Please fix the errors below.
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm" style="max-width: 760px;">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-pencil me-2"></i>Edit: {{ $service->name }}</h6>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('municipality.services.update', $service, absolute: false) }}">
            @csrf @method('PUT')
            @include('municipality.partials.service-form', ['service' => $service, 'offices' => $offices])
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-floppy me-2"></i>Save Changes
                </button>
                <a href="{{ route('municipality.services', absolute: false) }}" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection