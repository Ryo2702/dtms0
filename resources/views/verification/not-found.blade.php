@extends('layouts.app')

@section('content')
    <div class="container max-w-4xl mx-auto py-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-error text-error-content rounded-full mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <h1 class="text-3xl font-bold text-error mb-4">❌ Document Not Found</h1>
                <p class="text-lg text-base-content/70 mb-6">
                    The verification code <code class="bg-base-200 px-2 py-1 rounded">{{ $verificationCode }}</code> is not
                    valid or the document may have been revoked.
                </p>

                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.728-.833-2.498 0L3.732 16c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div>
                        <h3 class="font-bold">Possible Reasons:</h3>
                        <ul class="text-sm mt-2 list-disc list-inside">
                            <li>The verification code was entered incorrectly</li>
                            <li>The document has been revoked or is no longer valid</li>
                            <li>This may be a fraudulent document</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('documents.lookup') }}" class="btn btn-primary mr-2">
                        Try Again
                    </a>
                    <a href="/" class="btn btn-outline">
                        Go Home
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
