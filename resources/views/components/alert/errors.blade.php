@props([
    'errors' => null,
    'title' => 'Please fix the following errors: ',
])

@php
    $errorBag = $errors ?? ($errors ?? (session('errors') ?? collect()));
@endphp

@if ($errorBag && $errorBag->any())
    <div class="alert alert-error mb-4" {{ $attributes }}>
        <i data-lucide="message-square-warning"class="stroke-current shrink-0 h-6 w-6" fill="none"></i>
        <div>
            <h3 class="font-bold">{{ $title }}</h3>
            <ul class="list-disc list-inside mt-2">
                @foreach ($errorBag->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
