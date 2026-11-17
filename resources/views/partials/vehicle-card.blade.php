<a href="{{ route('vehicles.show', $vehicle) }}" class="block bg-white rounded-lg shadow hover:shadow-md transition overflow-hidden">
    @if ($vehicle->cover_url)
        <img src="{{ $vehicle->cover_url }}" alt="{{ $vehicle->title }}" class="h-44 w-full object-cover">
    @else
        <div class="h-44 bg-gray-200 flex items-center justify-center text-5xl">🚗</div>
    @endif
    <div class="p-4">
        <h3 class="font-semibold">{{ $vehicle->title }}</h3>
        <p class="text-sm text-gray-500">{{ $vehicle->city }} · {{ ucfirst($vehicle->transmission) }} · {{ $vehicle->seats }} lugares</p>
        <p class="mt-2 text-lg font-bold text-indigo-600">R$ {{ number_format($vehicle->daily_price, 2, ',', '.') }}<span class="text-sm font-normal text-gray-500"> / dia</span></p>
    </div>
</a>
