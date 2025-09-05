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

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Document Management System</h1>
            <p class="text-base-content/70 mt-2">Generate and manage official documents with review workflow</p>
        </div>

        <!-- Navigation Tabs -->
        <div class="tabs tabs-boxed mb-6">
            <a href="{{ route('documents.index') }}" class="tab tab-active">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Documents
            </a>
            <a href="{{ route('documents.reviews.index') }}" class="tab">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Reviews
                @php
                    $pendingCount = \App\Models\DocumentReview::where('assigned_to', auth()->id())
                        ->where('status', 'pending')
                        ->count();
                @endphp
                @if ($pendingCount > 0)
                    <div class="badge badge-error badge-sm ml-2">{{ $pendingCount }}</div>
                @endif
            </a>
            @if (in_array($user->role, ['staff', 'head']))
                <a href="#" class="tab" onclick="showQuickStats()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Analytics
                </a>
            @endif
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
                                        <div class="badge badge-ghost badge-sm">
                                            {{ strtoupper(pathinfo($doc['file'], PATHINFO_EXTENSION)) }}</div>
                                    </div>

                                    <!-- Document Description -->
                                    <div class="text-xs text-base-content/60 mb-4">
                                        @if ($doc['title'] === "Mayor's Clearance")
                                            Official clearance document issued by the Mayor's office for various purposes.
                                        @elseif($doc['title'] === 'MPOC Sample')
                                            Municipal Peace and Order Council certification document.
                                        @else
                                            Standard municipal document template.
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <a href="{{ route('documents.form', ['file' => $doc['file']]) }}"
                                        class="btn btn-primary btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Fill Form
                                    </a>
                                    @if (in_array($user->role, ['staff', 'head']))
                                        <button onclick="quickPreview('{{ $doc['title'] }}')"
                                            class="btn btn-outline btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Preview
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if (in_array($user->role, ['staff', 'head']))
                <!-- Review System Information Card -->
                <div class="card bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 shadow-xl mb-8">
                    <div class="card-body">
                        <h3 class="card-title text-info">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Document Review System
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-semibold mb-2">Features & Capabilities:</h4>
                                <div class="text-sm space-y-2">
                                    <p><strong>• Multi-Department Review:</strong> Send documents across departments for
                                        verification and approval.</p>
                                    <p><strong>• Forwarding Chain:</strong> Track complete document journey with timestamps
                                        and notes.</p>
                                    <p><strong>• OR Number Management:</strong> Update official receipt numbers during
                                        review process.</p>
                                    <p><strong>• Time-bound Process:</strong> Set 1-10 minute review periods with expiration
                                        tracking.</p>
                                </div>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Quick Actions:</h4>
                                <div class="space-y-2">
                                    <a href="{{ route('documents.reviews.index') }}"
                                        class="btn btn-info btn-sm btn-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                        Manage Reviews
                                    </a>
                                    <button onclick="showWorkflowGuide()" class="btn btn-outline btn-sm btn-block">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Workflow Guide
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats Dashboard -->
                <div class="stats shadow w-full">
                    <div class="stat">
                        <div class="stat-figure text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="stat-title">Pending Reviews</div>
                        <div class="stat-value text-primary">
                            {{ \App\Models\DocumentReview::where('assigned_to', auth()->id())->where('status', 'pending')->count() }}
                        </div>
                        <div class="stat-desc">Awaiting your action</div>
                    </div>

                    <div class="stat">
                        <div class="stat-figure text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2M7 4h10M7 4v12a2 2 0 002 2h6a2 2 0 002-2V4" />
                            </svg>
                        </div>
                        <div class="stat-title">Created Reviews</div>
                        <div class="stat-value text-secondary">
                            {{ \App\Models\DocumentReview::where('created_by', auth()->id())->count() }}
                        </div>
                        <div class="stat-desc">Documents you initiated</div>
                    </div>

                    <div class="stat">
                        <div class="stat-figure text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="stat-title">Approved Today</div>
                        <div class="stat-value text-accent">
                            {{ \App\Models\DocumentReview::where('status', 'approved')->whereDate('reviewed_at', today())->count() }}
                        </div>
                        <div class="stat-desc">Successfully processed</div>
                    </div>

                    <div class="stat">
                        <div class="stat-figure text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="stat-title">Avg. Process Time</div>
                        <div class="stat-value text-warning">
                            @php
                                $avgTime = \App\Models\DocumentReview::where('status', 'approved')
                                    ->whereNotNull('reviewed_at')
                                    ->selectRaw('AVG(process_time_minutes) as avg_time')
                                    ->value('avg_time');
                            @endphp
                            {{ round($avgTime ?? 0, 1) }}m
                        </div>
                        <div class="stat-desc">Average review duration</div>
                    </div>
                </div>
            @endif
        @else
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    class="stroke-current shrink-0 w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>No documents available. Please contact your administrator to add document templates.</span>
            </div>
        @endif
    </div>

    <!-- Workflow Guide Modal -->
    <div id="workflowModal" class="modal">
        <div class="modal-box max-w-4xl">
            <h3 class="font-bold text-lg mb-4">Document Review Workflow Guide</h3>

            <div class="space-y-4">
                <div class="steps steps-vertical lg:steps-horizontal">
                    <div class="step step-primary">Create Document</div>
                    <div class="step step-primary">Send for Review</div>
                    <div class="step step-primary">Department Review</div>
                    <div class="step step-primary">Forward/Approve</div>
                    <div class="step step-primary">Final Download</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div class="card bg-base-200">
                        <div class="card-body">
                            <h4 class="card-title text-sm">Step 1-2: Document Creation</h4>
                            <p class="text-xs">Fill document form and choose to send for review. Select reviewer and set
                                process time (1-10 minutes).</p>
                        </div>
                    </div>

                    <div class="card bg-base-200">
                        <div class="card-body">
                            <h4 class="card-title text-sm">Step 3-4: Review Process</h4>
                            <p class="text-xs">Reviewer can approve, reject, or forward to another department. OR numbers
                                can be added during review.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-action">
                <button class="btn" onclick="document.getElementById('workflowModal').close()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function quickPreview(docTitle) {
            alert(`Preview for: ${docTitle}\n\nThis feature will show document template preview in a future update.`);
        }

        function showQuickStats() {
            alert(
                'Analytics dashboard coming soon!\n\nThis will include detailed statistics, performance metrics, and reporting features.');
        }

        function showWorkflowGuide() {
            document.getElementById('workflowModal').showModal();
        }
    </script>
@endsection
