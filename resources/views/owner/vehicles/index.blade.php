@extends('layouts.site')

@section('content')
<div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">Meus veículos</h1>
    <a href="{{ route('owner.vehicles.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">+ Anunciar veículo</a>
</div>

<div class="bg-white rounded-lg shadow divide-y">
    @forelse ($vehicles as $vehicle)
        <div class="p-4 flex flex-wrap items-center gap-4">
            @if ($vehicle->cover_url)
                <img src="{{ $vehicle->cover_url }}" alt="" class="h-14 w-20 object-cover rounded">
            @else
                <div class="h-14 w-20 bg-gray-200 rounded flex items-center justify-center text-2xl">🚗</div>
            @endif
            <div class="flex-1 min-w-48">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="font-semibold hover:underline">{{ $vehicle->title }}</a>
                <p class="text-sm text-gray-500">{{ $vehicle->plate }} · {{ $vehicle->city }} · R$ {{ number_format($vehicle->daily_price, 2, ',', '.') }}/dia</p>
            </div>
            <span class="text-xs px-2 py-1 rounded {{ $vehicle->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                {{ $vehicle->is_active ? 'Anunciado' : 'Pausado' }}
            </span>
            <a href="{{ route('owner.vehicles.edit', $vehicle) }}" class="text-indigo-600 hover:underline">Editar</a>
            <form method="POST" action="{{ route('owner.vehicles.destroy', $vehicle) }}" onsubmit="return confirm('Remover este veículo?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Excluir</button>
            </form>
        </div>
    @empty
        <p class="p-6 text-gray-500">Você ainda não cadastrou nenhum veículo.</p>
    @endforelse
</div>
<div class="mt-4">{{ $vehicles->links() }}</div>
@endsection
