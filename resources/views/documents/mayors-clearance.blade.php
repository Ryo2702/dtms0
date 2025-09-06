@extends('layouts.app')

@section('content')
    <div class="container max-w-3xl mx-auto">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="card-title text-2xl">Mayor's Clearance Form</h2>
                        <p class="text-base-content/70">Send document to another department for review and processing
                        </p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('documents.download', 'Mayors_Clearance.docx') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="send_for_review">

                    <!-- Client Information Section -->
                    <div class="card bg-base-200 mb-6">
                        <div class="card-body">
                            <h3 class="card-title text-lg mb-4">Client Information</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Full Name *</span>
                                    </label>
                                    <input type="text" name="name" class="input input-bordered" required
                                        value="{{ old('name') }}" placeholder="Enter client's full name">
                                    @error('name')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Purpose *</span>
                                    </label>
                                    <input type="text" name="purpose" class="input input-bordered" required
                                        value="{{ old('purpose') }}" placeholder="e.g., Employment, Business permit">
                                    @error('purpose')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text font-semibold">Complete Address *</span>
                                </label>
                                <textarea name="address" class="textarea textarea-bordered" required rows="3"
                                    placeholder="Enter complete address including barangay, municipality, province">{{ old('address') }}</textarea>
                                @error('address')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Fee and Receipt Information -->
                    <div class="card bg-base-200 mb-6">
                        <div class="card-body">
                            <h3 class="card-title text-lg mb-4">Fee and Receipt Information</h3>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Fee Amount</span>
                                    </label>
                                    <input type="text" name="fee" class="input input-bordered"
                                        value="{{ old('fee') }}" placeholder="₱0.00">
                                    @error('fee')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Issue Date</span>
                                    </label>
                                    <input type="date" name="date" class="input input-bordered"
                                        value="{{ old('date', now()->format('Y-m-d')) }}">
                                    @error('date')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Document Processing Section - REQUIRED -->
                    <div class="card bg-gradient-to-r from-green-50 to-blue-50 border border-green-200">
                        <div class="card-body">
                            <div class="flex justify-between relative">
                                <h3 class="card-title text-lg text-green-800 mb-4 flex">
                                    <i data-lucide="send" class="h-4 w-4 mr-2" stroke='currentColor'></i>
                                    Send Document for Department Review
                                </h3>
                                <button class="ml-2 p-1 rounded-full transition" onclick="toggleInstruction(this)">
                                    <i data-lucide="circle-alert" class="w-5 h-5"></i>
                                </button>
                                <div
                                    class="instruction-box absolute top-full left-0 mt-2 w-64 p-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg hidden">
                                    All documents must go through department review or need payment before download.
                                    Document will be processed through multiple departments as needed.
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Send to Department </span>
                                        <span class="label-text-alt">Choose the first department to review</span>
                                    </label>
                                    <select name="reviewer_id" class="select select-bordered" required>
                                        <option value="">Select department for initial review</option>
                                        @foreach (\App\Models\User::with('department')->where('type', 'Head')->where('id', '!=', auth()->id())->get()->groupBy('department.name') as $deptName => $users)
                                            <optgroup label="{{ $deptName ?? 'No Department' }}">
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->name }} - {{ $deptName }} (Head)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                    @error('reviewer_id')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>

                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text font-semibold">Review Time Limit</span>
                                    </label>
                                    <select name="process_time" class="select select-bordered mt-6" required>
                                        <option value="">Set time limit for review</option>
                                        @for ($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ $i == 5 ? 'selected' : '' }}>
                                                {{ $i }} minute{{ $i > 1 ? 's' : '' }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('process_time')
                                        <label class="label">
                                            <span class="label-text-alt text-error">{{ $message }}</span>
                                        </label>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-semibold">Initial Instructions for Reviewer</span>
                                    <span class="label-text-alt">Provide specific instructions or notes</span>
                                </label>
                                <textarea name="initial_notes" class="textarea textarea-bordered" rows="3" required
                                    placeholder="e.g., Please verify client information, check for completeness, validate requirements, etc.">{{ old('initial_notes') }}</textarea>
                                @error('initial_notes')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card-actions justify-end mt-6">
                        <a href="{{ route('documents.index') }}" class="btn btn-outline">
                            <i data-lucide="move-left" class="h-5 w-5 mr-2"></i>
                            Back to Documents
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="send" class="h-4 w-4 mr-2" stroke='currentColor'></i>
                            Send for Department Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-format fee input
        document.querySelector('input[name="fee"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d.]/g, '');
            if (value && !value.startsWith('₱')) {
                e.target.value = value ? '₱' + value : '';
            }
        });

        // Form validation enhancement
        document.querySelector('form').addEventListener('submit', function(e) {
            const reviewerId = document.querySelector('select[name="reviewer_id"]').value;
            const processTime = document.querySelector('select[name="process_time"]').value;
            const initialNotes = document.querySelector('textarea[name="initial_notes"]').value.trim();

            if (!reviewerId || !processTime || !initialNotes) {
                e.preventDefault();
                alert('Please fill in all required fields: Department, Time Limit, and Instructions.');
                return false;
            }

            if (confirm(
                    'Send this document for department review? The document will go through the review process before you can download it.'
                )) {
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });

        function toggleInstruction(button) {
            const box = button.parentElement.querySelector('.instruction-box');
            box.classList.toggle('hidden');
        }

        document.addEventListener('click', (e) => {
            document.querySelectorAll('.instruction-box').forEach((box) => {
                if (!box.contains(e.target) && !box.previousElementSibling.contains(e.target)) {
                    box.classList.add('hidden');
                }
            });
        });
    </script>
@endsection
