<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Doctrams</title>
    @vite(['resources/js/app.js'])
</head>

<body class="min-h-screen bg-cover bg-center relative" style="background-image: url('{{ asset('images/bg.jpg') }}');">

    <section class="relative z-10 flex items-center justify-center min-h-screen">
        @yield('content')
    </section>
</body>

</html>
