@extends('layouts.guest')

@section('content')
    <div
        class="min-h-screen flex items-center justify-end px-4 bg-[url('/images/background.jpg')] bg-cover bg-center">

        <!-- Right-side card -->
        <div class="w-full max-w-md h-auto lg:h-screen flex flex-col items-center justify-center card glass bg-white/20 backdrop-blur-md shadow-xl p-8 mr-8">

            <!-- Logo -->
            <div class="avatar mb-6">
                <div class="w-32 h-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-4 shadow-2xl overflow-hidden bg-gradient-to-br from-primary to-secondary">
                    <img src="{{ asset('images/logo.jpg') }}" alt="DOCTRAMS Logo" class="object-cover w-full h-full" />
                </div>
            </div>

            <!-- Card Body -->
            <div class="card-body w-full">
                <h1 class="text-2xl font-bold text-center mb-4">Login</h1>

                @if (isset($errors) && $errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                    @csrf
                    <label class="form-control">
                        <div class="label"><span class="label-text">Employee ID</span></div>
                        <input type="text" name="employee_id" class="input input-bordered" required value="{{ old('employee_id') }}">
                    </label>

                    <label class="form-control mt-4">
                        <div class="label"><span class="label-text">Password</span></div>
                        <input type="password" name="password" class="input input-bordered" required>
                    </label>

                    <button type="submit" class="btn btn-primary mt-6 w-full" id="loginButton">
                        <span id="buttonText">Login</span>
                    </button>

                    <div id="errorDisplay" class="text-center mt-2 text-red-600 hidden">
                        <p class="text-sm">Too many failed attempts. Please try again later.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let countdownInterval;
        let isLocked = false;

        async function checkLockStatus() {
            const employeeIdInput = document.querySelector('input[name="employee_id"]');
            const employeeId = employeeIdInput.value || localStorage.getItem('lastEmployeeId');
            
            if (!employeeId) return;

            try {
                const response = await fetch('{{ route("login.check-lock") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ employee_id: employeeId })
                });

                const data = await response.json();

                if (data.locked) {
                    startCountdown(data.remaining_time);
                }
            } catch (error) {
                console.error('Error checking lock status:', error);
            }
        }

        function startCountdown(seconds) {
            isLocked = true;
            const loginButton = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const errorDisplay = document.getElementById('errorDisplay');

            loginButton.disabled = true;
            loginButton.classList.add('btn-disabled');
            errorDisplay.classList.remove('hidden');

            countdownInterval = setInterval(() => {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = seconds % 60;
                const timeString = `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
                buttonText.textContent = `Try again in ${timeString}`;

                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    enableForm();
                }

                seconds--;
            }, 1000);
        }

        function enableForm() {
            isLocked = false;
            const loginButton = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const errorDisplay = document.getElementById('errorDisplay');

            loginButton.disabled = false;
            loginButton.classList.remove('btn-disabled');
            buttonText.textContent = 'Login';
            errorDisplay.classList.add('hidden');

            if (countdownInterval) {
                clearInterval(countdownInterval);
            }
        }

        // Store employee ID on input
        document.querySelector('input[name="employee_id"]').addEventListener('input', (e) => {
            if (e.target.value) {
                localStorage.setItem('lastEmployeeId', e.target.value);
            }
        });

        // Check lock status on page load and when employee ID changes
        document.addEventListener('DOMContentLoaded', () => {
            checkLockStatus();
            
            // Auto-fill last employee ID
            const lastEmployeeId = localStorage.getItem('lastEmployeeId');
            if (lastEmployeeId) {
                document.querySelector('input[name="employee_id"]').value = lastEmployeeId;
            }
        });

        document.querySelector('input[name="employee_id"]').addEventListener('blur', checkLockStatus);

        document.getElementById('loginForm').addEventListener('submit', e => {
            if (isLocked) {
                e.preventDefault();
            } else {
                // Store employee ID before submitting
                const employeeId = document.querySelector('input[name="employee_id"]').value;
                localStorage.setItem('lastEmployeeId', employeeId);
            }
        });
    </script>
@endsection
