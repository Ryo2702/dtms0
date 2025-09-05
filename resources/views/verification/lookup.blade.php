@extends('layouts.app')

@section('content')
    <div class="container max-w-2xl mx-auto py-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="text-center mb-6">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 bg-primary text-primary-content rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold">🔍 Document Verification</h1>
                    <p class="text-lg text-base-content/70">Enter the verification code to authenticate a document</p>
                </div>

                <form action="{{ route('documents.lookup') }}" method="GET" class="space-y-6">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Verification Code</span>
                        </label>
                        <input type="text" name="code" class="input input-bordered input-lg text-center font-mono"
                            placeholder="VER-20250904-ABC12345" value="{{ request('code') }}" required autocomplete="off">
                        <label class="label">
                            <span class="label-text-alt">Enter the code found on your document</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Verify Document
                    </button>
                </form>

                <div class="divider">OR</div>

                <div class="text-center">
                    <p class="text-sm text-base-content/70 mb-4">
                        Scan the QR code on your document using your phone's camera
                    </p>
                    <div class="mockup-phone">
                        <div class="camera"></div>
                        <div class="display">
                            <div class="artboard artboard-demo phone-1 bg-base-200">
                                <div class="flex items-center justify-center h-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-base-content/50"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
