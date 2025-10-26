<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Doctrams</title>
    @vite(['resources/js/app.js'])
</head>

<body class="min-h-screen relative overflow-hidden">
    <!-- Background Image with Blur Overlay -->
    <div class="fixed inset-0 -z-10">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" 
             style="background-image: url('{{ asset('images/bg.jpg') }}'); filter: blur(3px); transform: scale(1.1);">
        </div>
        <div class="absolute inset-0 bg-gradient-to-br from-primary/30 via-secondary/20 to-accent/30 backdrop-blur-sm"></div>
    </div>

    <section class="relative z-10">
        @yield('content')
    </section>
</body>

</html>
