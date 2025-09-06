@extends('layouts.app')
@section('content')
    <div class="container max-w-6xl mx-auto">

        @if (session('success'))
            <div class="alert alert-success mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-3xl font-bold">Document Management System</h1>
            <div class="step step-primary flex items-center gap-2 relative">
                Create Document
                <button class="ml-2 p-1 rounded-full transition" onclick="toggleInstruction(this)">
                    <i data-lucide="circle-alert" class="w-5 h-5"></i>
                </button>
                <div
                    class="instruction-box absolute top-full left-0 mt-2 w-64 p-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg hidden">
                    Fill document form and choose to send for which department.
                </div>
            </div>
        </div>


        @if (count($documents))
            <!-- Documents Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @foreach ($documents as $doc)
                    <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                        <div class="card-body">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h2 class="card-title text-lg mb-2">{{ $doc['title'] }}</h2>
                                    <p class="text-base-content/70 text-sm mb-3">{{ $doc['file'] }}</p>

                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @if ($doc['title'] === "Mayor's Clearance")
                                            <div class="badge badge-info badge-sm">Requires OR Number</div>
                                        @endif
                                        @if (isset($doc['template']))
                                            <div class="badge badge-outline badge-sm">Template Available</div>
                                        @endif
                                    </div>

                                    <!-- Document Description -->
                                    <div class="text-xs text-base-content/60 mb-4">
                                        @if ($doc['title'] === "Mayor's Clearance")
                                            Official clearance document issued by the Mayor's office for various purposes.
                                        @elseif($doc['title'] === 'Municipal Peace and Order Council')
                                            Municipal Peace and Order Council certification document.
                                        @else
                                            Standard municipal document template.
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('documents.form', ['file' => $doc['file']]) }}"
                                        class="btn btn-primary btn-sm">
                                        <i data-lucide="square-pen" class="w-4 h-4 pr-1"></i>
                                        Fill Form
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <i data-lucide="circle-alert" class="stroke-current shrink w-6 h-6"></i>
                <span>No documents available. Please contact your administrator to add document templates.</span>
            </div>
        @endif
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
    </script>
@endsection
