@extends('layouts.site')

@section('content')
<h1 class="text-2xl font-bold mb-4">Solicitações recebidas</h1>

<div class="bg-white rounded-lg shadow divide-y">
    @forelse ($rentals as $rental)
        <div class="p-4 flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-56">
                <p class="font-semibold">{{ $rental->vehicle->title }} <span class="text-gray-400 font-normal">({{ $rental->vehicle->plate }})</span></p>
                <p class="text-sm text-gray-500">
                    {{ $rental->renter->name }} · {{ $rental->start_date->format('d/m/Y') }} a {{ $rental->end_date->format('d/m/Y') }}
                    · R$ {{ number_format($rental->total_price, 2, ',', '.') }}
                </p>
            </div>
            @include('rentals._badge', ['rental' => $rental])
            @if ($rental->status === 'pending')
                <form method="POST" action="{{ route('rentals.approve', $rental) }}">@csrf @method('PATCH')
                    <button class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Aprovar</button></form>
                <form method="POST" action="{{ route('rentals.reject', $rental) }}">@csrf @method('PATCH')
                    <button class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Recusar</button></form>
            @endif
        </div>
    @empty
        <p class="p-6 text-gray-500">Nenhuma solicitação recebida até agora.</p>
    @endforelse
</div>
<div class="mt-4">{{ $rentals->links() }}</div>
@endsection
