@extends('layouts.app')
@section('content')
    <div class="container max-w-6xl mx-auto">

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

        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Document Management System</h1>
                    <div class="relative flex items-center gap-2 step step-primary">
                        Create Document
                        <button class="p-1 ml-2 transition rounded-full" onclick="toggleInstruction(this)">
                            <i data-lucide="circle-alert" class="w-5 h-5"></i>
                        </button>
                        <div
                            class="absolute left-0 hidden w-64 p-2 mt-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg instruction-box top-full">
                            Fill document form and choose to send for which department.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Request Document Form -->
        <div class="mb-8">
            <div class="bg-white-secondary card shadow-xl">
                <div class="card-body">
                    <h2 class="mb-4 text-xl card-title">
                        <i data-lucide="plus-circle" class="w-5 h-5 mr-2"></i>
                        Create Request Document
                    </h2>
                    
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <!-- Document Type -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Document Type *</span>
                            </label>
                            <select name="document_type" id="document_type_select" required 
                                class="select select-bordered w-full @error('document_type') select-error @enderror"
                                onchange="updateTitle(this)">
                                <option value="">Select document type</option>
                                @foreach ($documentTypes as $type)
                                    <option value="{{$type->title}}"
                                        data-description="{{$type->description}}">
                                        {{$type->title}}
                                    </option>
                                @endforeach
                            </select>
                            @error('document_type')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                            <label class="label">
                                <span class="label-text-alt" id="document_description"></span>
                            </label>
                        </div>

                        <!-- Document Title (Auto-filled) -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Document Title *</span>
                            </label>
                            <input type="text" name="title" id="document_title" required 
                                class="input input-bordered w-full @error('title') input-error @enderror" 
                                placeholder="Auto-filled based on document type" value="{{ old('title') }}" readonly>
                            @error('title')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Client Name -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Client Name *</span>
                                </label>
                                <input type="text" name="client_name" required 
                                    class="input input-bordered w-full @error('client_name') input-error @enderror" 
                                    placeholder="Last Name, First Name Middle Initial" value="{{ old('client_name') }}">
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
                                        @if($reviewer->id !== auth()->id())
                                            <option value="{{ $reviewer->id }}" 
                                                {{ old('reviewer_id') == $reviewer->id ? 'selected' : '' }}>
                                                {{ $reviewer->name }} ({{ $reviewer->type }})
                                                @if($reviewer->department)
                                                    - {{ $reviewer->department->name }}
                                                @endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('reviewer_id')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Processing Time -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Processing Time *</span>
                                </label>
                                <input type="number" name="process_time" min="1" required 
                                    class="input input-bordered w-full @error('process_time') input-error @enderror" 
                                    placeholder="Enter time value" value="{{ old('process_time', 1) }}">
                                @error('process_time')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Time Unit -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Time Unit *</span>
                                </label>
                                <select name="time_unit" required 
                                    class="select select-bordered w-full @error('time_unit') select-error @enderror">
                                    <option value="">Select unit</option>
                                    <option value="minutes" {{ old('time_unit') == 'minutes' ? 'selected' : '' }}>Minutes</option>
                                    <option value="days" {{ old('time_unit') == 'days' ? 'selected' : '' }}>Days</option>
                                    <option value="weeks" {{ old('time_unit') == 'weeks' ? 'selected' : '' }}>Weeks</option>
                                </select>
                                @error('time_unit')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            <!-- Difficulty -->
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Priority Level *</span>
                                </label>
                                <select name="difficulty" required 
                                    class="select select-bordered w-full @error('difficulty') select-error @enderror"
                                    onchange="updateDifficultyColor(this)">
                                    <option value="">Select priority</option>
                                    <option value="normal" style="background-color: #10b981; color: white;" {{ old('difficulty') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="important" style="background-color: #f59e0b; color: white;" {{ old('difficulty') == 'important' ? 'selected' : '' }}>Important</option>
                                    <option value="urgent" style="background-color: #ef4444; color: white;" {{ old('difficulty') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="immediate" style="background-color: #7c2d12; color: white;" {{ old('difficulty') == 'immediate' ? 'selected' : '' }}>Immediate</option>
                                </select>
                                @error('difficulty')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>
                        </div>

                        <!-- Assigned Staff -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Assign Staff *</span>
                            </label>
                            <select name="assigned_staff" required 
                                class="select select-bordered w-full @error('assigned_staff') select-error @enderror">
                                <option value="">Select staff member</option>
                                @foreach($assignedStaff as $staff)
                                    <option value="{{ $staff['full_name'] }}" 
                                        {{ old('assigned_staff') == $staff['full_name'] ? 'selected' : '' }}>
                                        {{ $staff['full_name'] }} - {{ $staff['position'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('assigned_staff')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- File Attachment -->
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Attachment (Optional)</span>
                            </label>
                            <input type="file" name="attachment" 
                                class="file-input file-input-bordered w-full @error('attachment') file-input-error @enderror"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <label class="label">
                                <span class="label-text-alt">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</span>
                            </label>
                            @error('attachment')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        function updateTitle(select) {
            const titleInput = document.getElementById('document_title');
            const descriptionSpan = document.getElementById('document_description');
            const selectedOption = select.options[select.selectedIndex];
            
            titleInput.value = selectedOption.value;
            if (descriptionSpan) {
                descriptionSpan.textContent = selectedOption.dataset.description || '';
            }
        }

        function updateDifficultyColor(select) {
            const value = select.value;
            const colors = {
                'normal': '#10b981',
                'important': '#f59e0b', 
                'urgent': '#ef4444',
                'immediate': '#7c2d12'
            };
            
            if (colors[value]) {
                select.style.backgroundColor = colors[value];
                select.style.color = 'white';
            } else {
                select.style.backgroundColor = '';
                select.style.color = '';
            }
        }
    </script>
@endsection
