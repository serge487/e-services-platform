@extends('municipality.layouts.app')
@section('title', 'Services')
@section('page-title', 'Services')

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-semibold mb-0">All Services</h5>
        @if(auth()->user()->isOfficeStaff())
            <small class="text-muted">View only — municipality admins manage the catalog.</small>
        @endif
    </div>
    @if(auth()->user()->isMunicipalityAdmin())
        <a href="{{ route('municipality.services.create', absolute: false) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Add Service
        </a>
    @endif
</div>

@forelse($offices as $office)
    <div class="card border-0 shadow-sm mb-4">
        {{-- Office header --}}
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold">
                <i class="bi bi-building me-2 text-primary"></i>{{ $office->name }}
            </h6>
            @if(auth()->user()->isMunicipalityAdmin())
                <button class="btn btn-outline-secondary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#addCategoryModal"
                        data-office-id="{{ $office->id }}"
                        data-office-name="{{ $office->name }}">
                    <i class="bi bi-folder-plus me-1"></i>Add Category
                </button>
            @endif
        </div>

        <div class="card-body p-0">
            @forelse($office->categories as $category)
                <div class="border-bottom">
                    {{-- Category row --}}
                    <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light">
                        <span class="fw-semibold small text-secondary">
                            <i class="bi bi-folder me-1"></i>{{ $category->name }}
                            <span class="badge bg-secondary bg-opacity-25 text-secondary ms-1">
                                {{ $category->services->count() }} service(s)
                            </span>
                        </span>
                        @if(auth()->user()->isMunicipalityAdmin())
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editCategoryModal"
                                        data-category-id="{{ $category->id }}"
                                        data-category-name="{{ $category->name }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST"
                                      action="{{ route('municipality.categories.destroy', $category, absolute: false) }}"
                                      onsubmit="return confirm('Delete category \'{{ $category->name }}\' and all its services?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    {{-- Services table --}}
                    @if($category->services->isNotEmpty())
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Service Name</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Required Documents</th>
                                    @if(auth()->user()->isMunicipalityAdmin())
                                        <th class="text-end pe-4">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->services as $service)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold small">{{ $service->name }}</div>
                                            @if($service->description)
                                                <div class="text-muted" style="font-size:0.78rem;">{{ Str::limit($service->description, 60) }}</div>
                                            @endif
                                        </td>
                                        <td class="small">
                                            ${{ number_format($service->price, 2) }}
                                        </td>
                                        <td class="small">{{ $service->duration_days }} day(s)</td>
                                        <td class="small">
                                            @if(!empty($service->required_documents))
                                                @foreach($service->required_documents as $doc)
                                                    <span class="badge bg-light text-dark border me-1 mb-1">{{ $doc }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </td>
                                        @if(auth()->user()->isMunicipalityAdmin())
                                            <td class="text-end pe-4">
                                                <a href="{{ route('municipality.services.edit', $service, absolute: false) }}"
                                                   class="btn btn-outline-primary btn-sm me-1">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST"
                                                      action="{{ route('municipality.services.destroy', $service, absolute: false) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Delete service \'{{ $service->name }}\'?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-muted small px-4 py-3">
                            No services in this category yet.
                            @if(auth()->user()->isMunicipalityAdmin())
                                <a href="{{ route('municipality.services.create', absolute: false) }}">Add one</a>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-muted small px-4 py-3">
                    No categories yet for this office.
                </div>
            @endforelse
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="bi bi-grid-3x3-gap fs-1 d-block mb-2"></i>
            <p class="mb-0">No offices found for your municipality.</p>
        </div>
    </div>
@endforelse

@if(auth()->user()->isMunicipalityAdmin())
{{-- ── Add Category Modal ─────────────────────────────────────────── --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('municipality.categories.store', absolute: false) }}">
                @csrf
                <input type="hidden" name="office_id" id="addCategoryOfficeId">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Adding category to: <strong id="addCategoryOfficeName"></strong></p>
                    <label for="addCategoryName" class="form-label fw-semibold">Category Name</label>
                    <input type="text" id="addCategoryName" name="name" class="form-control" required placeholder="e.g. Civil Registry">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Category Modal ────────────────────────────────────────── --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editCategoryForm" action="">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="editCategoryName" class="form-label fw-semibold">Category Name</label>
                    <input type="text" id="editCategoryName" name="name" class="form-control" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Populate Add Category modal
document.getElementById('addCategoryModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('addCategoryOfficeId').value  = button.dataset.officeId;
    document.getElementById('addCategoryOfficeName').textContent = button.dataset.officeName;
});

// Populate Edit Category modal
document.getElementById('editCategoryModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const categoryId   = button.dataset.categoryId;
    const categoryName = button.dataset.categoryName;
    document.getElementById('editCategoryName').value = categoryName;
    document.getElementById('editCategoryForm').action =
        '/municipality/categories/' + categoryId;
});
</script>
@endif

@endsection