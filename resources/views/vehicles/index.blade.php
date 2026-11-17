@extends('layouts.site')

@section('content')
<h1 class="text-2xl font-bold mb-4">Encontre um carro para alugar</h1>

<form method="GET" class="bg-white p-4 rounded-lg shadow grid gap-3 md:grid-cols-5 mb-6">
    <input name="city" value="{{ request('city') }}" placeholder="Cidade" class="border-gray-300 rounded">
    <input type="date" name="start_date" value="{{ request('start_date') }}" class="border-gray-300 rounded" title="Retirada">
    <input type="date" name="end_date" value="{{ request('end_date') }}" class="border-gray-300 rounded" title="Devolução">
    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Diária máxima (R$)" class="border-gray-300 rounded">
    <button class="bg-indigo-600 text-white rounded px-4 py-2 hover:bg-indigo-700">Buscar</button>
</form>

@if ($vehicles->isEmpty())
    <p class="text-gray-500">Nenhum veículo encontrado.</p>
@else
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($vehicles as $vehicle)
            @include('partials.vehicle-card', ['vehicle' => $vehicle])
        @endforeach
    </div>
    <div class="mt-6">{{ $vehicles->links() }}</div>
@endif
@endsection
