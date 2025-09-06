@props([
    'model' => null,
    'resource' => null,

    // Fully customizable route names (string) or null
    'viewRoute' => null,
    'editRoute' => null,

    // Full custom URLs (skip route() if set)
    'viewUrl' => null,
    'editUrl' => null,

    // Toggle buttons
    'showView' => true,
    'showEdit' => true,
])

@php
    // Default resource detection (if not explicitly passed)
    $res = $resource ?? (method_exists($model, 'getTable') ? $model->getTable() : 'items');

    // Resolve URLs
    $resolvedViewUrl = $viewUrl ?? ($viewRoute ? route($viewRoute, $model) : route("admin.$res.show", $model));

    $resolvedEditUrl = $editUrl ?? ($editRoute ? route($editRoute, $model) : route("admin.$res.edit", $model));

@endphp

<div class="flex gap-2">
    {{-- View --}}
    @if ($showView)
        <a href="{{ $resolvedViewUrl }}" class="btn btn-sm btn-outline" title="View">
            <i data-lucide="eye" class="w-4 h-4 mr-3"></i>
            View
        </a>
    @endif

    {{-- Edit --}}
    @if ($showEdit)
        <a href="{{ $resolvedEditUrl }}" class="btn btn-sm btn-outline" title="Edit">
            <i data-lucide="edit" class="w-4 h-4 mr-3"></i>
            Edit
        </a>
    @endif

</div>
