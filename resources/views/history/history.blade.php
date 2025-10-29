@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-5">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Documents</h6>
                            <h3 class="mb-0">{{ $stats['totalDocuments'] }}</h3>
                        </div>
                        <i class="fas fa-file-alt fa-2x text-primary opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Documents Sent</h6>
                            <h3 class="mb-0">{{ $stats['documentsSent'] }}</h3>
                        </div>
                        <i class="fas fa-paper-plane fa-2x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Pending Documents</h6>
                            <h3 class="mb-0">{{ $stats['pendingDocuments'] }}</h3>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Tabs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="own-tab" data-bs-toggle="tab" href="#own" role="tab">
                        My Documents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sent-tab" data-bs-toggle="tab" href="#sent" role="tab">
                        Sent to Departments
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                <!-- Own Documents Tab -->
                <div class="tab-pane fade show active" id="own" role="tabpanel">
                    <x-data-table
                        :headers="[
                            'id' => 'ID',
                            'title' => 'Title',
                            'type' => 'Type',
                            'status' => 'Status',
                            'created_at' => 'Created Date',
                            'action' => 'Action',
                        ]"
                        :paginator="$ownDocuments"
                        emptyMessage="No documents created yet."
                        :sortableFields="['id', 'title', 'type', 'status', 'created_at']"
                    >
                        @forelse($ownDocuments as $doc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium">#{{ $doc->id }}</td>
                                <td class="px-4 py-3 text-sm">{{ $doc->title }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $doc->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($doc->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @elseif($doc->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">{{ optional($doc->created_at)->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('head.document-history.show', $doc) }}" class="inline-flex items-center px-3 py-1.5 rounded text-white bg-blue-600 hover:bg-blue-700 text-xs font-medium">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                    No documents created yet.
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                </div>

                <!-- Sent to Departments Tab -->
                <div class="tab-pane fade" id="sent" role="tabpanel">
                    <x-data-table
                        :headers="[
                            'id' => 'ID',
                            'title' => 'Title',
                            'recipient_department' => 'Recipient Department',
                            'sent_at' => 'Sent Date',
                            'status' => 'Status',
                            'action' => 'Action',
                        ]"
                        :paginator="$sentToDepartments"
                        emptyMessage="No documents sent to departments yet."
                        :sortableFields="['id', 'title', 'sent_at', 'status']"
                    >
                        @forelse($sentToDepartments as $doc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-medium">#{{ $doc->id }}</td>
                                <td class="px-4 py-3 text-sm">{{ $doc->title }}</td>
                                <td class="px-4 py-3 text-sm">{{ $doc->recipient_department->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-sm">{{ optional($doc->sent_at)->format('M d, Y H:i') ?? 'Not sent' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($doc->status === 'received')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Received
                                        </span>
                                    @elseif($doc->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ ucfirst($doc->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('head.document-history.show', $doc) }}" class="inline-flex items-center px-3 py-1.5 rounded text-white bg-blue-600 hover:bg-blue-700 text-xs font-medium">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                    No documents sent to departments yet.
                                </td>
                            </tr>
                        @endforelse
                    </x-data-table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection