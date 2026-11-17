@extends('layouts.site')

@section('title', $vehicle->title)

@section('content')
<div class="grid gap-8 md:grid-cols-3">
    <div class="md:col-span-2 bg-white rounded-lg shadow overflow-hidden">
        @php $photos = $vehicle->photos; @endphp
        @if ($photos->isNotEmpty())
            <img id="main-photo" src="{{ $photos->first()->url }}" alt="{{ $vehicle->title }}" class="w-full max-h-96 object-cover">
            @if ($photos->count() > 1)
                <div class="flex gap-2 p-3 overflow-x-auto">
                    @foreach ($photos as $photo)
                        <img src="{{ $photo->url }}" alt="Foto {{ $loop->iteration }} de {{ $vehicle->title }}"
                             class="h-16 w-24 shrink-0 object-cover rounded cursor-pointer border-2 border-transparent hover:border-indigo-500"
                             onclick="document.getElementById('main-photo').src = this.src">
                    @endforeach
                </div>
            @endif
        @else
            <div class="h-64 bg-gray-200 flex items-center justify-center text-7xl">🚗</div>
        @endif
        <div class="p-6">
            <h1 class="text-2xl font-bold">{{ $vehicle->title }}</h1>
            <p class="text-gray-500">{{ $vehicle->city }} · Anunciado por {{ $vehicle->owner->name }}</p>
            <dl class="grid grid-cols-2 gap-2 mt-4 text-sm">
                <div><dt class="text-gray-500">Cor</dt><dd>{{ $vehicle->color }}</dd></div>
                <div><dt class="text-gray-500">Câmbio</dt><dd>{{ ucfirst($vehicle->transmission) }}</dd></div>
                <div><dt class="text-gray-500">Lugares</dt><dd>{{ $vehicle->seats }}</dd></div>
                <div><dt class="text-gray-500">Ano</dt><dd>{{ $vehicle->year }}</dd></div>
            </dl>
            @if ($vehicle->description)
                <p class="mt-4 whitespace-pre-line">{{ $vehicle->description }}</p>
            @endif

            @if ($bookedPeriods->isNotEmpty())
                <h2 class="font-semibold mt-6">Períodos já reservados</h2>
                <ul class="text-sm text-gray-600 list-disc list-inside">
                    @foreach ($bookedPeriods as $p)
                        <li>{{ $p->start_date->format('d/m/Y') }} a {{ $p->end_date->format('d/m/Y') }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <aside class="bg-white rounded-lg shadow p-6 h-fit">
        <p class="text-2xl font-bold text-indigo-600">R$ {{ number_format($vehicle->daily_price, 2, ',', '.') }}<span class="text-sm font-normal text-gray-500"> / dia</span></p>

        @auth
            @if ($vehicle->user_id === auth()->id())
                <p class="mt-4 text-sm text-gray-500">Este é o seu veículo.</p>
                <a href="{{ route('owner.vehicles.edit', $vehicle) }}" class="mt-2 inline-block text-indigo-600 hover:underline">Editar anúncio</a>
            @else
                <form method="POST" action="{{ route('rentals.store', $vehicle) }}" class="mt-4 space-y-3">
                    @csrf
                    <label class="block text-sm">Retirada
                        <input type="date" name="start_date" value="{{ old('start_date', request('start_date')) }}" min="{{ today()->toDateString() }}" required class="mt-1 w-full border-gray-300 rounded">
                    </label>
                    <label class="block text-sm">Devolução
                        <input type="date" name="end_date" value="{{ old('end_date', request('end_date')) }}" min="{{ today()->toDateString() }}" required class="mt-1 w-full border-gray-300 rounded">
                    </label>
                    <button class="w-full bg-indigo-600 text-white rounded py-2 hover:bg-indigo-700">Solicitar aluguel</button>
                    <p class="text-xs text-gray-500">O total é calculado pelo número de diárias (retirada e devolução inclusas). O proprietário precisa aprovar.</p>
                </form>
            @endif
        @else
            <a href="{{ route('login') }}" class="mt-4 block text-center bg-indigo-600 text-white rounded py-2 hover:bg-indigo-700">Entre para alugar</a>
        @endauth
    </aside>
</div>
@endsection
