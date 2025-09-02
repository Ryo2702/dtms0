@extends('layouts.app')
@section('content')
    <div class="container max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <a href="{{ route('documents.index') }}" class="btn btn-ghost mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back
            </a>
            <h1 class="text-2xl font-semibold">MPOC Sample</h1>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">Fill Document Details</h2>

                <form method="POST" action="{{ route('documents.download', ['file' => 'MPOC_Sample.docx']) }}"
                    class="space-y-4">
                    @csrf

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Barangay Chairman Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="barangay_chairman" value="{{ old('barangay_chairman') }}"
                            placeholder="Enter barangay chairman's full name"
                            class="input input-bordered w-full @error('barangay_chairman') input-error @enderror"
                            required />
                        @error('barangay_chairman')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Barangay Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="barangay_name" value="{{ old('barangay_name') }}"
                            placeholder="Enter barangay name"
                            class="input input-bordered w-full @error('barangay_name') input-error @enderror" required />
                        @error('barangay_name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Barangay Clearance Date <span class="text-error">*</span></span>
                        </label>
                        <input type="date" name="barangay_clearance_date"
                            value="{{ old('barangay_clearance_date', now()->format('Y-m-d')) }}"
                            class="input input-bordered w-full @error('barangay_clearance_date') input-error @enderror"
                            required />
                        @error('barangay_clearance_date')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="divider"></div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Resident Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="resident_name"
                            value="{{ old('resident_name', auth()->user()->name ?? '') }}"
                            placeholder="Enter resident's full name"
                            class="input input-bordered w-full @error('resident_name') input-error @enderror" required />
                        @error('resident_name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Resident Barangay <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="resident_barangay" value="{{ old('resident_barangay') }}"
                            placeholder="Enter resident's barangay"
                            class="input input-bordered w-full @error('resident_barangay') input-error @enderror"
                            required />
                        @error('resident_barangay')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Resident Party <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="requesting_party" value="{{ old('requesting_party') }}"
                            placeholder="Enter resident's party"
                            class="input input-bordered w-full @error('requesting_party') input-error @enderror" required />
                        @error('requesting_party')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Certification Date</span>
                        </label>
                        <input type="date" name="certification_date"
                            value="{{ old('certification_date', now()->format('Y-m-d')) }}"
                            class="input input-bordered w-full @error('certification_date') input-error @enderror" />
                        @error('certification_date')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="card-actions justify-end mt-6">
                        <a href="{{ route('documents.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generate & Download
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle form submission loading state
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <span class="loading loading-spinner loading-sm mr-2"></span>
                Generating...
            `;
        });
    </script>
@endsection
