@extends('layouts.app')

@section('content')
    <div class="container max-w-4xl mx-auto">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success mb-6">
                <i data-lucide="check-circle" class="w-6 h-6 stroke-current shrink"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error mb-6">
                <i data-lucide="x-circle" class="w-6 h-6 stroke-current shrink"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error mb-6">
                <i data-lucide="alert-circle" class="w-6 h-6 stroke-current shrink"></i>
                <div>
                    <p class="font-semibold">Please correct the following errors:</p>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Header with Back Button -->
        <div class="flex items-center mb-6">
            <a href="{{ route('documents.index') }}" class="btn btn-ghost mr-4">
                <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
                Back
            </a>
            <h1 class="text-2xl font-semibold">Create New Document</h1>
        </div>

        <!-- Create Document Form -->
        <div class="bg-white-secondary card shadow-xl">
            <div class="card-body">
                <form action="{{ route('documents.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Document Title -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Document Title *</span>
                            </label>
                            <input type="text" name="title" required 
                                class="input input-bordered w-full @error('title') input-error @enderror" 
                                placeholder="Enter document title" value="{{ old('title') }}">
                            @error('title')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Document Type -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Document Type *</span>
                            </label>
                            <select name="document_type" required 
                                class="select select-bordered w-full @error('document_type') select-error @enderror">
                                <option value="">Select document type</option>
                                <option value="Certificate" {{ old('document_type') == 'Certificate' ? 'selected' : '' }}>Certificate</option>
                                <option value="Clearance" {{ old('document_type') == 'Clearance' ? 'selected' : '' }}>Clearance</option>
                                <option value="Permit" {{ old('document_type') == 'Permit' ? 'selected' : '' }}>Permit</option>
                                <option value="License" {{ old('document_type') == 'License' ? 'selected' : '' }}>License</option>
                                <option value="Registration" {{ old('document_type') == 'Registration' ? 'selected' : '' }}>Registration</option>
                                <option value="Application" {{ old('document_type') == 'Application' ? 'selected' : '' }}>Application</option>
                                <option value="Other" {{ old('document_type') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('document_type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Client Name -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Client Name *</span>
                            </label>
                            <input type="text" name="client_name" required 
                                class="input input-bordered w-full @error('client_name') input-error @enderror" 
                                placeholder="Enter client's full name" value="{{ old('client_name') }}">
                            @error('client_name')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Reviewer -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Assign to Reviewer *</span>
                            </label>
                            <select name="reviewer_id" required 
                                class="select select-bordered w-full @error('reviewer_id') select-error @enderror">
                                <option value="">Select reviewer</option>
                                @foreach($reviewers as $reviewer)
                                    <option value="{{ $reviewer->id }}" 
                                        {{ old('reviewer_id') == $reviewer->id ? 'selected' : '' }}>
                                        {{ $reviewer->name }} ({{ $reviewer->type }})
                                        @if($reviewer->department)
                                            - {{ $reviewer->department->name }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('reviewer_id')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <!-- Processing Time -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Expected Processing Time (minutes) *</span>
                        </label>
                        <input type="number" name="process_time" min="1" max="10" required 
                            class="input input-bordered w-full @error('process_time') input-error @enderror" 
                            placeholder="Enter processing time in minutes (1-10)" value="{{ old('process_time', 5) }}">
                        <label class="label">
                            <span class="label-text-alt">Processing time must be between 1 and 10 minutes</span>
                        </label>
                        @error('process_time')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-between pt-4">
                        <a href="{{ route('documents.index') }}" class="btn btn-ghost">
                            <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                            Create & Send for Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
