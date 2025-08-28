@props([
    'label' => '',
    'name' => '',
    'options' => [],
    'selected' => null,
    'required' => false,
    'class' => '',
])
<div class="form-control w-full {{ $class }}">
    @if ($label)
        <label class="label">
            <span class="label-text {{ $required ? 'font-bold' : '' }}">{{ $label }}</span>
        </label>
    @endif

    <select name="{{ $name }}" {{ $required ? 'required' : '' }} class="select select-bordered w-full">
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected(old($name, $selected) == $value)>{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
        <span class="label-text-alt text-error mt-1">{{ $message }}</span>
    @enderror
</div>
