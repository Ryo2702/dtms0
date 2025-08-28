@extends('layouts.app')

@section('content')
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Available Documents</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($documents as $doc)
                <a href="{{ route('documents.download', $doc['file']) }}"
                    class="block bg-white shadow-md rounded-lg p-6 hover:shadow-lg transition">
                    <h2 class="text-lg font-semibold mb-2">{{ $doc['title'] }}</h2>
                    <p class="text-gray-600">Click to download</p>
                </a>
            @endforeach
        </div>
    </div>
@endsection
