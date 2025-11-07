@php
    $children = $categories->where('parent_id', $category->_id);
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
@endphp

<tr>
    <td><strong>{{ $category->category_code }}</strong></td>
    <td>{!! $indent !!}{{ $category->category_name }}</td>
    <td>{{ $category->getFullPath() }}</td>
    <td>{{ $category->parent ? $category->parent->category_name : '-' }}</td>
    <td>
        @if($children->count() > 0)
            <span class="badge bg-info">{{ $children->count() }} subcategories</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @if($category->is_active)
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-danger">Inactive</span>
        @endif
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-warning" onclick="editCategory('{{ $category->_id }}', '{{ $category->category_code }}', '{{ $category->category_name }}', '{{ $category->parent_id ?? '' }}', '{{ $category->description ?? '' }}')">
            <i class="ri-pencil-line"></i>
        </button>
        <form action="{{ route('tenant.categories.destroy', $category->_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This will delete the category and cannot be undone if it has items or subcategories.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="ri-delete-bin-line"></i>
            </button>
        </form>
    </td>
</tr>

@foreach($children as $child)
    @include('tenant.categories.category-row', ['category' => $child, 'level' => $level + 1, 'categories' => $categories])
@endforeach

