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
            <h1 class="text-2xl font-semibold">Mayor's Clearance</h1>
        </div>

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">Fill Document Details</h2>

                <form method="POST" action="{{ route('documents.download', ['file' => 'Mayors_Clearance.docx']) }}"
                    class="space-y-4">
                    @csrf

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Full Name <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}"
                            placeholder="Enter full name"
                            class="input input-bordered w-full @error('name') input-error @enderror" required />
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Complete Address <span class="text-error">*</span></span>
                        </label>
                        <textarea name="address" placeholder="Enter complete address (Street, Barangay, City/Municipality, Province)"
                            class="textarea textarea-bordered w-full @error('address') textarea-error @enderror" rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Processing Fee</span>
                            </label>
                            <input type="text" name="fee" value="{{ old('fee') }}" placeholder="₱0.00"
                                class="input input-bordered w-full @error('fee') input-error @enderror" />
                            @error('fee')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Official Receipt Number</span>
                            </label>
                            <input type="text" name="or_number" value="{{ old('or_number') }}"
                                placeholder="Enter OR number"
                                class="input input-bordered w-full @error('or_number') input-error @enderror" />
                            @error('or_number')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Date Issued</span>
                        </label>
                        <input type="text" name="date" value="{{ old('date', now()->format('F d, Y')) }}"
                            class="input input-bordered w-full @error('date') input-error @enderror" readonly />
                        @error('date')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="divider"></div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Purpose <span class="text-error">*</span></span>
                        </label>
                        <select name="purpose"
                            class="select select-bordered w-full @error('purpose') select-error @enderror" required>
                            <option value="" disabled selected>Select purpose</option>
                            <option value="Employment" {{ old('purpose') == 'Employment' ? 'selected' : '' }}>Employment
                            </option>
                            <option value="Business Registration"
                                {{ old('purpose') == 'Business Registration' ? 'selected' : '' }}>Business Registration
                            </option>
                            <option value="Bank Transaction" {{ old('purpose') == 'Bank Transaction' ? 'selected' : '' }}>
                                Bank Transaction</option>
                            <option value="Scholarship Application"
                                {{ old('purpose') == 'Scholarship Application' ? 'selected' : '' }}>Scholarship Application
                            </option>
                            <option value="Travel/Visa Application"
                                {{ old('purpose') == 'Travel/Visa Application' ? 'selected' : '' }}>Travel/Visa Application
                            </option>
                            <option value="Other" {{ old('purpose') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('purpose')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="card-actions justify-end mt-6">
                        <a href="{{ route('documents.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
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
