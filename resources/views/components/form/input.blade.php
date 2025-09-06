@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
])

<div class="form-control">
    @if ($label)
        <label class="label">
            <span class="label-text">{{ $label }}</span>
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}" @if ($required) required @endif
        {{ $attributes->merge(['class' => 'input input-bordered ' . ($errors->has($name) ? 'input-error' : '')]) }} />

    @error($name)
        <p class="text-error text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
