@props([
    'label' => null,
    'name',
    'accept' => 'image/*',
    'required' => false,
    'default' => null,
    'value' => null,
])

<div class="form-control">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif

    <div class="flex items-center gap-4">
        <!-- Avatar Preview -->
        <div class="avatar">
            <div class="w-20 h-20 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2 overflow-hidden">
                <img id="{{ $name }}-preview" src="{{ $value ? Storage::url($value) : $default }}"
                    alt="Logo Preview">
            </div>
        </div>

        <!-- File Input -->
        <div>
            <input type="file" name="{{ $name }}" accept="{{ $accept }}" id="{{ $name }}-input"
                @if ($required) required @endif
                {{ $attributes->merge(['class' => 'file-input file-input-bordered w-full max-w-xs ' . ($errors->has($name) ? 'file-input-error' : '')]) }}
                onchange="preview{{ ucfirst($name) }}(event)" />
        </div>
    </div>

    @error($name)
        <p class="text-error text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

<!-- Preview Script -->
@push('scripts')
    <script>
        function preview{{ ucfirst($name) }}(event) {
            const input = event.target;
            const preview = document.getElementById('{{ $name }}-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
