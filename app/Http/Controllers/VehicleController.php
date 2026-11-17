<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;

/** Área pública: busca e visualização de veículos. */
class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'city' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date', 'required_with:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'required_with:start_date'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $vehicles = Vehicle::query()
            ->with('photos')
            ->where('is_active', true)
            ->when($data['city'] ?? null, fn ($q, $city) => $q->where('city', 'like', "%{$city}%"))
            ->when($data['max_price'] ?? null, fn ($q, $max) => $q->where('daily_price', '<=', $max))
            ->when(
                ($data['start_date'] ?? null) && ($data['end_date'] ?? null),
                fn ($q) => $q->availableBetween($data['start_date'], $data['end_date'])
            )
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('vehicles.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle)
    {
        abort_unless($vehicle->is_active || $vehicle->user_id === auth()->id(), 404);

        $vehicle->load(['photos', 'owner']);

        $bookedPeriods = $vehicle->rentals()
            ->where('status', 'confirmed')
            ->where('end_date', '>=', today())
            ->orderBy('start_date')
            ->get(['start_date', 'end_date']);

        return view('vehicles.show', compact('vehicle', 'bookedPeriods'));
    }
}
