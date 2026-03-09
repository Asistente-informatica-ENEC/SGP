<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SGP - Bienvenido</title>
        <!-- Puedes cambiar el favicon aquí si lo deseas -->
        <link href="{{ asset('images/logoico.ico') }}" type="image/x-icon" id="favicon" rel="icon">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased h-screen flex flex-col items-center justify-center" style="background-color: #79bde4ff;">
        <div class="text-center">
            <div class="mb-6 flex justify-center">
               <img src="{{ asset('images/logo2.png') }}" alt="Logo SGP" class="h-32 drop-shadow-lg">
            </div>
            
            <h1 class="text-black text-4xl md:text-5xl font-bold mb-8 drop-shadow-md">
                Sistema de Gestión Patrimonial
            </h1>
            
            <a href="{{ route('platform.login') }}" class="inline-block bg-white text-[#97E6E6] px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-100 hover:scale-105 transition-all shadow-lg">
                Ingresar
            </a>
        </div>
    </body>
</html>
