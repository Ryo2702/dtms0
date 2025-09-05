@extends('layouts.app')

@section('content')
    <div class="container max-w-4xl mx-auto py-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="text-center mb-6">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-success text-success-content rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.414-4.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-success">✓ Document Verified</h1>
                    <p class="text-lg text-base-content/70">This document is authentic and issued by the authorized office.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold">Document Information</h2>

                        <div class="bg-base-200 p-4 rounded-lg space-y-3">
                            <div>
                                <span class="font-semibold">Document Type:</span>
                                <span class="ml-2">{{ $verification->document_type }}</span>
                            </div>

                            <div>
                                <span class="font-semibold">Document ID:</span>
                                <span class="ml-2 font-mono">{{ $verification->document_id }}</span>
                            </div>

                            <div>
                                <span class="font-semibold">Client Name:</span>
                                <span class="ml-2">{{ $verification->client_name }}</span>
                            </div>

                            @if ($verification->official_receipt_number)
                                <div>
                                    <span class="font-semibold">OR Number:</span>
                                    <span class="ml-2">{{ $verification->official_receipt_number }}</span>
                                </div>
                            @endif

                            <div>
                                <span class="font-semibold">Verification Code:</span>
                                <span class="ml-2 font-mono text-primary">{{ $verification->verification_code }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold">Issuance Information</h2>

                        <div class="bg-base-200 p-4 rounded-lg space-y-3">
                            <div>
                                <span class="font-semibold">Issued By:</span>
                                <span class="ml-2">{{ $verification->issued_by }}</span>
                            </div>

                            <div>
                                <span class="font-semibold">Employee ID:</span>
                                <span class="ml-2">{{ $verification->issued_by_id }}</span>
                            </div>

                            <div>
                                <span class="font-semibold">Issue Date:</span>
                                <span class="ml-2">{{ $verification->issued_at->format('F d, Y') }}</span>
                            </div>

                            <div>
                                <span class="font-semibold">Issue Time:</span>
                                <span class="ml-2">{{ $verification->issued_at->format('h:i A') }}</span>
                            </div>

                            <div>
                                <span class="font-semibold">Times Verified:</span>
                                <span class="ml-2">{{ $verification->verification_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="mt-6">
                    <h2 class="text-xl font-semibold mb-4">Document Details</h2>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($verification->document_data as $key => $value)
                                @if (!in_array($key, ['action', 'reviewer_id', 'process_time', '_token', 'initial_notes']))
                                    <div>
                                        <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="ml-2">{{ $value }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Verification Status -->
                <div class="mt-6 p-4 bg-success/10 border border-success rounded-lg">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success mr-2" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold text-success">Authentication Successful</p>
                            <p class="text-sm text-success/80">This document has been verified as authentic and was issued
                                by the authorized government office.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-base-content/60">
                        Verified on {{ now()->format('F d, Y \a\t h:i A') }}
                    </p>
                    <a href="{{ route('documents.lookup') }}" class="btn btn-outline btn-sm mt-2">
                        Verify Another Document
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
