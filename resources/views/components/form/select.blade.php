@props([
    'label' => null,
    'name',
    'value' => null,
    'required' => false,
])

<div class="form-control">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif

    <select name="{{ $name }}" @if ($required) required @endif
        {{ $attributes->merge(['class' => 'select select-bordered ' . ($errors->has($name) ? 'select-error' : '')]) }}>
        {{ $slot }}
    </select>

    @error($name)
        <p class="text-error text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
