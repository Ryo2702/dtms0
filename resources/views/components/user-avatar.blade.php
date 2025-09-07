@props(['user', 'size' => 'w-10 h-10', 'class' => ''])

<div class="avatar {{ $class }}">
    <div class="{{ $size }} rounded-full">
        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-full object-cover" />
    </div>
</div>
