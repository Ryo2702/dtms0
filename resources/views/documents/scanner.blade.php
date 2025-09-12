@extends('layouts.app')

@section('content')
    <x-container>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-qrcode"></i> Document Scanner</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Instructions:</strong> Click in the input field below and scan the QR code using your 2D
                            barcode scanner.
                        </div>

                        <form id="scannerForm">
                            @csrf
                            <div class="form-group">
                                <label for="qr_data">Scan QR Code:</label>
                                <input type="text" name="qr_data" id="qr_data" class="form-control form-control-lg"
                                    placeholder="Click here and scan QR code with your scanner..." autocomplete="off"
                                    autofocus>
                                <small class="form-text text-muted">
                                    The scanner will automatically input the QR code data here.
                                </small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search"></i> Process Scanned Data
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="clearInput()">
                                <i class="fas fa-trash"></i> Clear
                            </button>
                        </form>

                        <div id="loading" class="mt-4 text-center" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Processing...</span>
                            </div>
                            <p class="mt-2">Processing scanned data...</p>
                        </div>

                        <div id="result" class="mt-4" style="display: none;">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5><i class="fas fa-file-alt"></i> Document Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Document ID:</strong> <span id="doc-id"></span></p>
                                            <p><strong>Title:</strong> <span id="doc-title"></span></p>
                                            <p><strong>Name:</strong> <span id="doc-name"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date:</strong> <span id="doc-date"></span></p>
                                            <p><strong>Process:</strong> <span id="doc-process"></span></p>
                                            <p><strong>Processing Time:</strong> <span id="doc-time"></span></p>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <p><strong>Employee ID:</strong> <span id="doc-employee"></span></p>
                                        <p><strong>Department:</strong> <span id="doc-department"></span></p>
                                        <p><strong>Status:</strong> <span id="doc-status" class="badge"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="error" class="mt-4 alert alert-danger" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="error-message"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-container>

    <script>
        // Auto-submit when scanner inputs data (most scanners send Enter key)
        document.getElementById('qr_data').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processScannedData();
            }
        });

        // Manual submit
        document.getElementById('scannerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            processScannedData();
        });

        function processScannedData() {
            const qrData = document.getElementById('qr_data').value.trim();

            if (!qrData) {
                showError('Please scan a QR code first.');
                return;
            }

            // Show loading
            document.getElementById('loading').style.display = 'block';
            document.getElementById('result').style.display = 'none';
            document.getElementById('error').style.display = 'none';

            // Send data to server for processing
            fetch('{{ route('documents.scanner') }}', {
                    method: 'POST',
                    body: JSON.stringify({
                        qr_text: qrData,
                        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }),
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading').style.display = 'none';

                    if (data.success) {
                        showResult(data.data);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    document.getElementById('loading').style.display = 'none';
                    showError('Error: ' + error.message);
                });
        }

        function showResult(data) {
            document.getElementById('result').style.display = 'block';
            document.getElementById('error').style.display = 'none';

            document.getElementById('doc-id').textContent = data.document_id || 'N/A';
            document.getElementById('doc-title').textContent = data.title || 'N/A';
            document.getElementById('doc-name').textContent = data.name || 'N/A';
            document.getElementById('doc-date').textContent = data.date || 'N/A';
            document.getElementById('doc-process').textContent = data.process || 'N/A';
            document.getElementById('doc-time').textContent = data.time || 'N/A';
            document.getElementById('doc-employee').textContent = data.employee_id || 'N/A';
            document.getElementById('doc-department').textContent = data.department || 'N/A';

            const statusBadge = document.getElementById('doc-status');
            statusBadge.textContent = data.status || 'unknown';
            statusBadge.className = 'badge ' + (data.status === 'active' ? 'badge-success' : 'badge-secondary');
        }

        function showError(message) {
            document.getElementById('error').style.display = 'block';
            document.getElementById('result').style.display = 'none';
            document.getElementById('error-message').textContent = message;
        }

        function clearInput() {
            document.getElementById('qr_data').value = '';
            document.getElementById('result').style.display = 'none';
            document.getElementById('error').style.display = 'none';
            document.getElementById('qr_data').focus();
        }

        // Auto-focus on input when page loads
        window.onload = function() {
            document.getElementById('qr_data').focus();
        };
    </script>
@endsection
