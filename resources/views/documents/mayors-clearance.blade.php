@extends('layouts.app')

@section('content')
    <x-container>
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <!-- Header -->
                <div class="flex items-center mb-6">
                    <x-navigation.back />
                    <h1 class="text-2xl font-semibold">Mayor's Clearance</h1>
                </div>

                <x-alert.errors />

                <form action="{{ route('documents.download', 'Mayors_Clearance.docx') }}" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="send_for_review">

                    <!-- Client Information Section -->
                    <x-card title="Client Information">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-form.input name="name" label="Full Name *" placeholder="Enter client's full name"
                                required />

                            <x-form.input name="purpose" label="Purpose *" placeholder="e.g., Employment, Business permit"
                                required />
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
                    </x-card>

                    <!-- Fee and Receipt Information -->
                    <x-card title="Fee and Receipt Information">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-form.input name="fee" label="Fee Amount" placeholder="₱0.00" />

                            <x-form.input name="date" type="date" label="Issue Date" :value="now()->format('Y-m-d')" />
                        </div>
                    </x-card>

                    <x-document.review-section :hasFee="true" />
                </form>
            </div>
        </div>
    </x-container>

    <script>
        // Auto-format fee input
        document.querySelector('input[name="fee"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d.]/g, '');
            if (value && !value.startsWith('₱')) {
                e.target.value = value ? '₱' + value : '';
            }
        });
    </script>
@endsection
