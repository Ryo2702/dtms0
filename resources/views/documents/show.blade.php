@extends('layouts.app')
@section('content')
<div class="container max-w-xl mx-auto py-10">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h1 class="text-2xl font-bold mb-4">Document Details</h1>
            <div class="mb-2"><strong>Title:</strong> {{ $document->document_data['title'] ?? '' }}</div>
            <div class="mb-2"><strong>Date:</strong> {{ $document->document_data['date'] ?? '' }}</div>
            <div class="mb-2"><strong>Name:</strong> {{ $document->client_name ?? '' }}</div>
            <div class="mb-2"><strong>Type:</strong> {{ $document->document_type ?? '' }}</div>
            @if (!empty($document->document_data['attachment_path']))
                <div class="mb-2">
                    <strong>Attachment:</strong>
                    <a href="{{ asset('storage/' . $document->document_data['attachment_path']) }}" target="_blank" class="btn btn-sm btn-secondary">View Attachment</a>
                </div>
            @endif
            <div class="mt-6">
                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-primary">Download Document</a>
            </div>
        </div>
    </div>
</div>
@endsection
