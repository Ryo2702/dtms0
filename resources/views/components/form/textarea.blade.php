@props([
    'label' => null,
    'name',
    'placeholder' => '',
    'required' => false,
    'value' => null,
])

<div class="form-control">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif

    <textarea name="{{ $name }}" placeholder="{{ $placeholder }}" @if ($required) required @endif
        {{ $attributes->merge(['class' => 'textarea textarea-bordered ' . ($errors->has($name) ? 'textarea-error' : '')]) }}>{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="text-error text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
