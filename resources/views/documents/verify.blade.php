@extends('layouts.app')

@section('content')
    <div class="container max-w-2xl mx-auto py-10">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                @if (isset($verification) && $verification)
                    <div class="text-center mb-6">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 bg-success text-success-content rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-success mb-2">Document Verified</h1>
                        <p class="text-lg text-base-content/70">This document is authentic and issued by the authorized
                            office.</p>
                    </div>

                    <div class="mb-6 flex justify-center">
                        <div>
                            <div class="mb-2 text-center font-semibold">Scan to verify</div>
                            <div class="bg-white p-2 rounded border inline-block">
                                <img src="{{ route('documents.qrcode', $verification->verification_code) }}" alt="QR Code"
                                    width="120" height="120">
                            </div>
                            <div class="mt-2 text-xs text-center text-base-content/60">
                                Or visit: <br>
                                <span
                                    class="font-mono text-xs">{{ route('documents.verify', $verification->verification_code) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <h2 class="font-semibold mb-2">Document Info</h2>
                            <ul class="space-y-1 text-sm">
                                <li><strong>Type:</strong> {{ $verification->document_type }}</li>
                                <li><strong>Document ID:</strong> {{ $verification->document_id }}</li>
                                <li><strong>Client:</strong> {{ $verification->client_name }}</li>
                                @if ($verification->official_receipt_number)
                                    <li><strong>OR Number:</strong> {{ $verification->official_receipt_number }}</li>
                                @endif
                                <li><strong>Verification Code:</strong> {{ $verification->verification_code }}</li>
                            </ul>
                        </div>
                        <div>
                            <h2 class="font-semibold mb-2">Issued By</h2>
                            <ul class="space-y-1 text-sm">
                                <li><strong>Name:</strong> {{ $verification->issued_by }}</li>
                                <li><strong>Employee ID:</strong> {{ $verification->issued_by_id }}</li>
                                <li><strong>Issued At:</strong> {{ $verification->issued_at->format('F d, Y h:i A') }}</li>
                                <li><strong>Times Verified:</strong> {{ $verification->verification_count }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h2 class="font-semibold mb-2">Document Details</h2>
                        <div class="bg-base-200 p-3 rounded">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                @foreach ($verification->document_data as $key => $value)
                                    @if (!in_array($key, ['action', 'reviewer_id', 'process_time', '_token', 'initial_notes']))
                                        <div>
                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            {{ $value }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success mt-4">
                        <div>
                            <span class="font-bold">Authentication Successful:</span>
                            This document has been verified as authentic and was issued by the authorized office.
                        </div>
                    </div>
                @else
                    <div class="alert alert-error">
                        <span>Document not found or verification code invalid.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
