@extends('layouts.site')

@section('content')
<h1 class="text-2xl font-bold mb-4">Minhas reservas</h1>

<div class="bg-white rounded-lg shadow divide-y">
    @forelse ($rentals as $rental)
        <div class="p-4 flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-56">
                <a href="{{ route('vehicles.show', $rental->vehicle) }}" class="font-semibold hover:underline">{{ $rental->vehicle->title }}</a>
                <p class="text-sm text-gray-500">
                    {{ $rental->start_date->format('d/m/Y') }} a {{ $rental->end_date->format('d/m/Y') }}
                    ({{ $rental->days }} {{ $rental->days > 1 ? 'diárias' : 'diária' }})
                    · Total R$ {{ number_format($rental->total_price, 2, ',', '.') }}
                </p>
            </div>
            @include('rentals._badge', ['rental' => $rental])
            @if (in_array($rental->status, ['pending', 'confirmed']) && $rental->start_date->gte(today()))
                <form method="POST" action="{{ route('rentals.cancel', $rental) }}" onsubmit="return confirm('Cancelar esta reserva?')">
                    @csrf @method('PATCH')
                    <button class="text-red-600 hover:underline">Cancelar</button>
                </form>
            @endif
        </div>
    @empty
        <p class="p-6 text-gray-500">Você ainda não fez nenhuma reserva. <a class="text-indigo-600 underline" href="{{ route('vehicles.index') }}">Buscar carros</a></p>
    @endforelse
</div>
<div class="mt-4">{{ $rentals->links() }}</div>
@endsection
