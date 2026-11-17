<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
<nav class="bg-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
        <a href="{{ route('vehicles.index') }}" class="flex items-center" title="Início">
            <img src="{{ asset('images/logo.png') }}" alt="Partiu" class="h-12 w-auto">
        </a>
        <a href="{{ auth()->check() ? route('owner.vehicles.create') : route('register') }}"
           class="hidden md:block border-l-2 border-amber-400 pl-4 font-semibold text-[#04163a] hover:text-amber-600">
            Ganhe dinheiro alugando seu veículo pessoal
        </a>
        <a href="{{ route('vehicles.index') }}" class="hover:underline">Buscar carros</a>
        <div class="flex-1"></div>
        @auth
            <a href="{{ route('rentals.index') }}" class="hover:underline">Minhas reservas</a>
            <a href="{{ route('owner.vehicles.index') }}" class="hover:underline">Meus veículos</a>
            <a href="{{ route('rentals.incoming') }}" class="hover:underline">Solicitações recebidas</a>
            <a href="{{ route('profile.edit') }}" class="hover:underline">{{ auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="text-red-600 hover:underline">Sair</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="hover:underline">Entrar</a>
            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded hover:bg-indigo-700">Cadastrar</a>
        @endauth
    </div>
</nav>

<main class="max-w-6xl mx-auto px-4 py-8">
    @if (session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
