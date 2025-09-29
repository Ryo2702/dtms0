@extends('layouts.app')
@section('content')
    <div class="container max-w-6xl mx-auto">

        <div class="mb-6">
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


        @if (count($documents))
            <!-- Documents Grid -->
            <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
                @foreach ($documents as $doc)
                    <div class="transition-shadow duration-300 shadow-xl bg-white-secondary card hover:shadow-2xl">
                        <div class="card-body">
                            <div class="flex items-start justify-between ">
                                <div class="flex-1">
                                    <h2 class="mb-2 text-lg card-title">{{ $doc['title'] }}</h2>
                                    <p class="mb-3 text-sm text-base-content/70">{{ $doc['file'] }}</p>

                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @if ($doc['title'] === "Mayor's Clearance")
                                            <div class="badge badge-info badge-sm">Requires OR Number</div>
                                        @endif
                                        @if (isset($doc['template']))
                                            <div class="badge badge-outline badge-sm">Template Available</div>
                                        @endif
                                    </div>

                                    <!-- Document Description -->
                                    <div class="mb-4 text-xs text-base-content/60 ">
                                        @if ($doc['title'] === "Mayor's Clearance")
                                            Official clearance document issued by the Mayor's office for various purposes.
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
                <i data-lucide="circle-alert" class="w-6 h-6 stroke-current shrink"></i>
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
