@php
    $colors = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-green-100 text-green-700',
        'rejected' => 'bg-red-100 text-red-700',
        'cancelled' => 'bg-gray-200 text-gray-600',
    ];
@endphp
<span class="text-xs px-2 py-1 rounded {{ $colors[$rental->status] ?? '' }}">{{ $rental->status_label }}</span>
