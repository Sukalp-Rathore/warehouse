@extends('layout')

@section('title', 'Item Categories')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Item Categories</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#createCategoryForm">
                            <i class="ri-add-line me-1"></i> Add Category
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Create Category Form -->
                    <div class="collapse mb-4" id="createCategoryForm">
                        <div class="card card-body bg-light">
                            <h5>Create New Category</h5>
                            <form action="{{ route('tenant.categories.store') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="category_code" class="form-label">Category Code *</label>
                                            <input type="text" class="form-control @error('category_code') is-invalid @enderror" 
                                                   id="category_code" name="category_code" value="{{ old('category_code') }}" required>
                                            @error('category_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="category_name" class="form-label">Category Name *</label>
                                            <input type="text" class="form-control @error('category_name') is-invalid @enderror" 
                                                   id="category_name" name="category_name" value="{{ old('category_name') }}" required>
                                            @error('category_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="parent_id" class="form-label">Parent Category</label>
                                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                                <option value="">Root Category (No Parent)</option>
                                                @foreach($categories as $cat)
                                                    <option value="{{ $cat->_id }}" {{ old('parent_id') == $cat->_id ? 'selected' : '' }}>
                                                        {{ $cat->getFullPath() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('parent_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="ri-save-line me-1"></i> Create
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                                      id="description" name="description" rows="2">{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Categories List -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Category Code</th>
                                    <th>Category Name</th>
                                    <th>Full Path</th>
                                    <th>Parent</th>
                                    <th>Subcategories</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rootCategories as $category)
                                    @include('tenant.categories.category-row', ['category' => $category, 'level' => 0, 'categories' => $categories])
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No categories found. Create your first category above.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_code" class="form-label">Category Code *</label>
                        <input type="text" class="form-control" id="edit_category_code" name="category_code" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_parent_id" class="form-label">Parent Category</label>
                        <select class="form-select" id="edit_parent_id" name="parent_id">
                            <option value="">Root Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->_id }}">{{ $cat->getFullPath() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, code, name, parentId, description) {
    document.getElementById('editCategoryForm').action = '{{ url("/wms/tenant/categories") }}/' + id;
    document.getElementById('edit_category_code').value = code;
    document.getElementById('edit_category_name').value = name;
    document.getElementById('edit_parent_id').value = parentId;
    document.getElementById('edit_description').value = description || '';
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>
@endsection

