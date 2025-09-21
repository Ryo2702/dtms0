@props([
    'hasFee' => true,
])

{{-- Document Processing Section --}}
<div class="border border-green-200 card bg-gradient-to-r from-green-50 to-blue-50">
    <div class="card-body">
        <div class="relative flex justify-between">
            <h3 class="flex mb-4 text-lg text-green-800 card-title">
                <i data-lucide="send" class="w-4 h-4 mr-2" stroke='currentColor'></i>
                Send Document for Department Review
            </h3>
            <button class="p-1 ml-2 transition rounded-full" onclick="toggleInstruction(this)">
                <i data-lucide="circle-alert" class="w-5 h-5"></i>
            </button>
            <div
                class="absolute left-0 hidden w-64 p-2 mt-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg instruction-box top-full">
                All documents must go through department review{{ $hasFee ? ' or need payment' : '' }} before download.
                Document will be processed through multiple departments as needed.
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-2">
            <div class="form-control">
                <label class="label">
                    <span class="font-semibold label-text">Send to Department </span>
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
                    <span class="font-semibold label-text">Review Time Limit</span>
                </label>
                <select name="process_time" class="select select-bordered" required>
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

        <div class="mb-4 form-control">
            <label class="label">
                <span class="font-semibold label-text">Initial Instructions for Reviewer</span>
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
<div class="justify-end mt-6 card-actions">
    <a href="{{ route('documents.index') }}" class="btn btn-ghost">
        <i data-lucide="move-left" class="w-5 h-5 mr-2"></i>
        Back to Documents
    </a>
    <button type="submit" class="btn btn-primary">
        <i data-lucide="send" class="w-4 h-4 mr-2" stroke='currentColor'></i>
        Send for Department Review
    </button>
</div>

<script>
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
