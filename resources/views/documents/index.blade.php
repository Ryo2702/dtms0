@extends('layouts.app')
@section('content')
    <div class="container max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Available Documents</h1>
        </div>

        @if (count($documents))
            <div class="space-y-3">
                @foreach ($documents as $doc)
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="card-title">{{ $doc['title'] }}</h2>
                                    <p class="text-base-content/70">{{ $doc['file'] }}</p>
                                </div>
                                <a href="{{ route('documents.form', ['file' => $doc['file']]) }}" class="btn btn-primary">
                                    Fill & Download
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>No documents available.</span>
            </div>
        @endif
    </div>
@endsection
