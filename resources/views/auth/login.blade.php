@extends('layouts.guest')

@section('content')
    <div
        class="min-h-screen flex items-center justify-end lg:justify-end px-4 bg-[url('/images/background.jpg')] bg-cover bg-center">
        <div class="w-full max-w-md h-full lg:h-screen flex items-center card glass bg-white/20 backdrop-blur-md shadow-xl">
            <div class="card-body">
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

        function checkLockStatus() {
            const employeeId = document.querySelector('input[name="employee_id"]').value;
            
            if (!employeeId) return;

            const errorMessages = document.querySelectorAll('.alert-error li');
            for (let message of errorMessages) {
                if (message.textContent.includes('Too many login attempts')) {
                    const timeMatch = message.textContent.match(/(\d+):(\d+)/);
                    if (timeMatch) {
                        const minutes = parseInt(timeMatch[1]);
                        const seconds = parseInt(timeMatch[2]);
                        const totalSeconds = (minutes * 60) + seconds;
                        startCountdown(totalSeconds);
                        return;
                    }
                }
            }
        }

        function startCountdown(seconds) {
            isLocked = true;
            const loginButton = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            const errorDisplay = document.getElementById('errorDisplay');
            
            // Disable form and show error
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

        // Check lock status on page load if there are validation errors
        document.addEventListener('DOMContentLoaded', function() {
            checkLockStatus();
        });

        // Prevent form submission when locked
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (isLocked) {
                e.preventDefault();
                return false;
            }
        });
    </script>
@endsection
