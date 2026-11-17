<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    /** Motorista: minhas reservas. */
    public function index()
    {
        $rentals = Rental::with('vehicle')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('rentals.index', compact('rentals'));
    }

    /** Motorista: solicita o aluguel de um veículo. */
    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        if ($vehicle->user_id === $request->user()->id) {
            return back()->withErrors(['start_date' => 'Você não pode alugar o seu próprio veículo.']);
        }

        if (! $vehicle->is_active) {
            return back()->withErrors(['start_date' => 'Este veículo não está disponível para aluguel.']);
        }

        $rental = DB::transaction(function () use ($vehicle, $data, $request) {
            // trava o veículo para evitar corrida entre duas reservas simultâneas
            $locked = Vehicle::whereKey($vehicle->id)->lockForUpdate()->first();

            if (! $locked->isAvailable($data['start_date'], $data['end_date'])) {
                return null;
            }

            $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;

            return Rental::create([
                'vehicle_id' => $locked->id,
                'user_id' => $request->user()->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'total_price' => $days * $locked->daily_price,
                'status' => Rental::PENDING,
            ]);
        });

        if (! $rental) {
            return back()->withInput()->withErrors(['start_date' => 'O veículo já está reservado em parte desse período.']);
        }

        return redirect()->route('rentals.index')
            ->with('status', 'Solicitação enviada! Aguarde a aprovação do proprietário.');
    }

    /** Motorista: cancela a própria reserva. */
    public function cancel(Rental $rental)
    {
        abort_unless($rental->user_id === auth()->id(), 403);
        abort_unless(
            in_array($rental->status, [Rental::PENDING, Rental::CONFIRMED]) && $rental->start_date->gte(today()),
            422
        );

        $rental->update(['status' => Rental::CANCELLED]);

        return back()->with('status', 'Reserva cancelada.');
    }

    /** Dono: solicitações recebidas. */
    public function incoming()
    {
        $rentals = Rental::with(['vehicle', 'renter'])
            ->whereHas('vehicle', fn ($q) => $q->where('user_id', auth()->id()))
            ->latest()
            ->paginate(10);

        return view('rentals.incoming', compact('rentals'));
    }

    /** Dono: aprova uma solicitação. */
    public function approve(Rental $rental)
    {
        $this->authorizeOwner($rental);
        abort_unless($rental->status === Rental::PENDING, 422);

        $ok = DB::transaction(function () use ($rental) {
            $vehicle = Vehicle::whereKey($rental->vehicle_id)->lockForUpdate()->first();

            if (! $vehicle->isAvailable($rental->start_date->toDateString(), $rental->end_date->toDateString(), $rental->id)) {
                return false;
            }

            $rental->update(['status' => Rental::CONFIRMED]);

            // recusa automaticamente pendentes que conflitam com a reserva confirmada
            $vehicle->rentals()
                ->where('status', Rental::PENDING)
                ->where('id', '!=', $rental->id)
                ->overlapping($rental->start_date->toDateString(), $rental->end_date->toDateString())
                ->update(['status' => Rental::REJECTED]);

            return true;
        });

        return $ok
            ? back()->with('status', 'Reserva confirmada!')
            : back()->withErrors(['rental' => 'Já existe uma reserva confirmada nesse período.']);
    }

    /** Dono: recusa uma solicitação. */
    public function reject(Rental $rental)
    {
        $this->authorizeOwner($rental);
        abort_unless($rental->status === Rental::PENDING, 422);

        $rental->update(['status' => Rental::REJECTED]);

        return back()->with('status', 'Solicitação recusada.');
    }

    private function authorizeOwner(Rental $rental): void
    {
        abort_unless($rental->vehicle->user_id === auth()->id(), 403);
    }
}
