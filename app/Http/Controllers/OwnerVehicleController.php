<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Dono do veículo: CRUD dos próprios anúncios (com galeria de fotos). */
class OwnerVehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('photos')->where('user_id', auth()->id())->latest()->paginate(10);

        return view('owner.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('owner.vehicles.form', ['vehicle' => new Vehicle(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $newPhotos = $request->file('photos', []);

        DB::transaction(function () use ($data, $request, $newPhotos) {
            $vehicle = Vehicle::create($data + ['user_id' => $request->user()->id]);
            $this->storePhotos($vehicle, $newPhotos);
        });

        return redirect()->route('owner.vehicles.index')->with('status', 'Veículo cadastrado com sucesso!');
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorizeOwner($vehicle);

        return view('owner.vehicles.form', ['vehicle' => $vehicle->load('photos')]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorizeOwner($vehicle);
        $data = $this->validated($request, $vehicle);

        $toRemove = $vehicle->photos()->whereIn('id', $request->input('remove_photos', []))->get();
        $newPhotos = $request->file('photos', []);

        $total = $vehicle->photos()->count() - $toRemove->count() + count($newPhotos);
        if ($total > Vehicle::MAX_PHOTOS) {
            return back()->withInput()->withErrors([
                'photos' => 'Cada veículo pode ter no máximo ' . Vehicle::MAX_PHOTOS . ' fotos.',
            ]);
        }

        DB::transaction(function () use ($vehicle, $data, $toRemove, $newPhotos, $request) {
            $vehicle->update($data);

            foreach ($toRemove as $photo) {
                Storage::disk('public')->delete($photo->path);
                $photo->delete();
            }

            $this->storePhotos($vehicle, $newPhotos);
            $this->applyCover($vehicle, $request->integer('cover_photo_id') ?: null);
        });

        return redirect()->route('owner.vehicles.index')->with('status', 'Veículo atualizado!');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorizeOwner($vehicle);

        $hasActiveRentals = $vehicle->rentals()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('end_date', '>=', today())
            ->exists();

        if ($hasActiveRentals) {
            return back()->withErrors(['vehicle' => 'Este veículo possui reservas ativas. Pause o anúncio em vez de excluir.']);
        }

        foreach ($vehicle->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }
        $vehicle->delete(); // as linhas de vehicle_photos caem em cascata

        return redirect()->route('owner.vehicles.index')->with('status', 'Veículo removido.');
    }

    /** Salva os arquivos enviados no fim da galeria. */
    private function storePhotos(Vehicle $vehicle, array $files): void
    {
        $position = (int) $vehicle->photos()->max('position');
        $hasPhotos = $vehicle->photos()->exists();

        foreach ($files as $file) {
            $position = $hasPhotos ? $position + 1 : 0;
            $hasPhotos = true;

            $vehicle->photos()->create([
                'path' => $file->store("vehicles/{$vehicle->id}", 'public'),
                'position' => $position,
            ]);
        }
    }

    /** Move a foto escolhida para a capa e renumera as posições. */
    private function applyCover(Vehicle $vehicle, ?int $coverId): void
    {
        $photos = $vehicle->photos()->get();

        if ($coverId && $photos->contains('id', $coverId)) {
            $photos = $photos->sortBy(fn ($p) => $p->id === $coverId ? 0 : 1)->values();
        }

        foreach ($photos as $i => $photo) {
            if ($photo->position !== $i) {
                $photo->update(['position' => $i]);
            }
        }
    }

    private function authorizeOwner(Vehicle $vehicle): void
    {
        abort_unless($vehicle->user_id === auth()->id(), 403);
    }

        private function validated(Request $request, ?Vehicle $vehicle = null): array
    {
        // Normaliza antes de validar: maiúsculas e sem espaços/hífen (ABC-1D23 → ABC1D23)
        $request->merge([
            'plate' => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('plate'))),
        ]);

        $data = $request->validate([
            'brand' => ['required', 'string', 'max:60'],
            'model' => ['required', 'string', 'max:60'],
            'year' => ['required', 'integer', 'between:1990,' . (date('Y') + 1)],
            'plate' => ['required', 'string', 'max:10', Rule::unique('vehicles', 'plate')->ignore($vehicle?->id)],
            'color' => ['required', 'string', 'max:30'],
            'transmission' => ['required', Rule::in(['manual', 'automatico'])],
            'seats' => ['required', 'integer', 'between:1,15'],
            'city' => ['required', 'string', 'max:100'],
            'daily_price' => ['required', 'numeric', 'min:1', 'max:99999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:' . Vehicle::MAX_PHOTOS],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_photos' => ['nullable', 'array'],
            'remove_photos.*' => ['integer'],
            'cover_photo_id' => ['nullable', 'integer'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return Arr::except($data, ['photos', 'remove_photos', 'cover_photo_id']);
    }
}
