@props(['headers' => [], 'data' => [], 'actions' => null])

<div class="overflow-x-auto">
    <table class="table table-zebra w-full">
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th class="text-sm font-medium">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
