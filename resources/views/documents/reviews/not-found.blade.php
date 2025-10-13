@extends('layouts.app')

@section('content')
    <div class="container max-w-4xl mx-auto">
        <div class="card bg-white shadow-xl">
            <div class="card-body text-center">
                <div class="mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                
                <h1 class="text-3xl font-bold text-gray-800 mb-4">Document Not Found</h1>
                
                <p class="text-lg text-gray-600 mb-6">
                    The document with ID <strong class="text-primary">{{ $documentId }}</strong> could not be found.
                </p>
                
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <p class="text-sm text-gray-700">
                        This could happen if:
                    </p>
                    <ul class="list-disc list-inside text-sm text-gray-700 mt-2 space-y-1">
                        <li>The document ID was entered incorrectly</li>
                        <li>The document has been removed from the system</li>
                        <li>You don't have permission to view this document</li>
                    </ul>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('documents.reviews.index') }}" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                        </svg>
                        View All Documents
                    </a>
                    
                    <a href="{{ route('dashboard') }}" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection