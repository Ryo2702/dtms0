@extends('layouts.app')
@section('content')
    <div class="container max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <x-navigation.back />
            <h1 class="text-2xl font-semibold">Municipal Peace Order Council</h1>
        </div>


        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title mb-4">Fill Document Details</h2>

                <form method="POST" action="{{ route('documents.download', ['file' => 'MPOC.docx']) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="action" value="send_for_review">
                    <!-- Barangay Information -->
                    <x-card title="Barangay Information">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input name="barangay_chairman" label="Barangay Chairman Name *"
                                placeholder="Enter barangay chairman's full name" required />

                            <x-form.input name="barangay_name" label="Barangay Name *" placeholder="Enter barangay name"
                                required />
                        </div>

                        <x-form.input name="barangay_clearance_date" type="date" label="Barangay Clearance Date *"
                            :value="now()->format('Y-m-d')" required />
                    </x-card>

                    <!-- Resident Information -->
                    <x-card title="Resident Information">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input name="resident_name" label="Resident Name *"
                                placeholder="Enter resident's full name" :value="auth()->user()->name ?? ''" required />

                            <x-form.input name="resident_barangay" label="Resident Barangay *"
                                placeholder="Enter resident's barangay" required />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input name="requesting_party" label="Resident Party *"
                                placeholder="Enter resident's party" required />

                            <x-form.input name="certification_date" type="date" label="Certification Date"
                                :value="now()->format('Y-m-d')" />
                        </div>
                    </x-card>
                    <x-document.review-section :hasFee="false" />
                </form>
            </div>
        </div>
    </div>
@endsection
