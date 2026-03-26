
@php
    $selectedOfficeId   = old('office_id',   $service?->office_id);
    $selectedCategoryId = old('category_id', $service?->category_id);
@endphp

{{-- Office --}}
<div class="mb-3">
    <label for="service_office_id" class="form-label fw-semibold">Office <span class="text-danger">*</span></label>
    <select id="service_office_id" name="office_id"
            class="form-select @error('office_id') is-invalid @enderror" required>
        <option value="">— Select office —</option>
        @foreach($offices as $office)
            <option value="{{ $office->id }}"
                    data-categories="{{ $office->categories->toJson() }}"
                    {{ $selectedOfficeId == $office->id ? 'selected' : '' }}>
                {{ $office->name }}
            </option>
        @endforeach
    </select>
    @error('office_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Category (populated dynamically by JS) --}}
<div class="mb-3">
    <label for="service_category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
    <select id="service_category_id" name="category_id"
            class="form-select @error('category_id') is-invalid @enderror" required>
        <option value="">— Select office first —</option>
        @if($service)
            <option value="{{ $service->category_id }}" selected>{{ $service->category->name }}</option>
        @endif
    </select>
    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Name --}}
<div class="mb-3">
    <label for="service_name" class="form-label fw-semibold">Service Name <span class="text-danger">*</span></label>
    <input type="text" id="service_name" name="name"
           class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $service?->name) }}"
           placeholder="e.g. Birth Certificate Issuance" required>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Description --}}
<div class="mb-3">
    <label for="service_description" class="form-label fw-semibold">Description</label>
    <textarea id="service_description" name="description" rows="3"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="Brief description of this service">{{ old('description', $service?->description) }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

{{-- Price & Duration --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <label for="service_price" class="form-label fw-semibold">Price (USD) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0" id="service_price" name="price"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $service?->price) }}"
                   placeholder="0.00" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <label for="service_duration_days" class="form-label fw-semibold">Duration (days) <span class="text-danger">*</span></label>
        <div class="input-group">
            <input type="number" min="1" id="service_duration_days" name="duration_days"
                   class="form-control @error('duration_days') is-invalid @enderror"
                   value="{{ old('duration_days', $service?->duration_days) }}"
                   placeholder="e.g. 5" required>
            <span class="input-group-text">days</span>
            @error('duration_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

{{-- Required Documents --}}
<div class="mb-3">
    <label class="form-label fw-semibold">Required Documents</label>
    <p class="text-muted small mb-2">List the documents citizens must bring or upload for this service.</p>
    <div id="required-documents-list">
        @php
            $existingDocs = old('required_documents', $service?->required_documents ?? []);
            if (empty($existingDocs)) $existingDocs = [''];
        @endphp
        @foreach($existingDocs as $index => $doc)
            <div class="input-group mb-2 document-row">
                <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
                <input type="text" name="required_documents[]"
                       class="form-control"
                       value="{{ $doc }}"
                       placeholder="e.g. National ID Card">
                <button type="button" class="btn btn-outline-danger remove-document-row">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        @endforeach
    </div>
    <button type="button" id="add-document-row" class="btn btn-outline-secondary btn-sm mt-1">
        <i class="bi bi-plus me-1"></i>Add Document
    </button>
</div>

<script>
// ── Dynamic category dropdown based on selected office ────────────────────────
const officeSelect   = document.getElementById('service_office_id');
const categorySelect = document.getElementById('service_category_id');
const preselectedCategoryId = {{ $selectedCategoryId ?? 'null' }};

function populateCategories(officeSelect) {
    const selectedOption = officeSelect.options[officeSelect.selectedIndex];
    const categories = selectedOption.dataset.categories
        ? JSON.parse(selectedOption.dataset.categories)
        : [];

    categorySelect.innerHTML = '<option value="">— Select category —</option>';

    categories.forEach(function (category) {
        const option = document.createElement('option');
        option.value = category.id;
        option.textContent = category.name;
        if (preselectedCategoryId && category.id === preselectedCategoryId) {
            option.selected = true;
        }
        categorySelect.appendChild(option);
    });
}

// Run on page load if an office is already selected (edit mode)
if (officeSelect.value) {
    populateCategories(officeSelect);
}

officeSelect.addEventListener('change', function () {
    populateCategories(this);
});

// ── Dynamic required documents list ──────────────────────────────────────────
document.getElementById('add-document-row').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'input-group mb-2 document-row';
    row.innerHTML = `
        <span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
        <input type="text" name="required_documents[]" class="form-control"
               placeholder="e.g. Proof of Address">
        <button type="button" class="btn btn-outline-danger remove-document-row">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    document.getElementById('required-documents-list').appendChild(row);
});

document.getElementById('required-documents-list').addEventListener('click', function (event) {
    if (event.target.closest('.remove-document-row')) {
        const rows = document.querySelectorAll('.document-row');
        if (rows.length > 1) {
            event.target.closest('.document-row').remove();
        }
    }
});
</script>