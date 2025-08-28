@props([
    'label' => '',
    'name' => '',
    'type' => 'text',
    'value' => '',
    'required' => 'false',
    'placeholder' => '',
    'class' => '',
])

<div class="form-control w-full {{ $class }}">
    <label class="label">
        <span class="label-text {{ $required ? 'font-bold' : '' }}">{{ $label }}</span>
    </label>
    <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder ?: $label }}" {{ $required ? 'required' : '' }}
        class="input input-bordered w-full focus:outline-none" />

    @error($name)
        <span class="label label-text-alt text-error mt-1">{{ $message }}</span>
    @enderror
</div>
