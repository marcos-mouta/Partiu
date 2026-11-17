@extends('layouts.site')

@php
    $editing = $vehicle->exists;
    $field = 'mt-1 w-full border-gray-300 rounded';
@endphp

@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $editing ? 'Editar veículo' : 'Anunciar veículo' }}</h1>

<form method="POST" enctype="multipart/form-data"
      action="{{ $editing ? route('owner.vehicles.update', $vehicle) : route('owner.vehicles.store') }}"
      class="bg-white p-6 rounded-lg shadow grid gap-4 md:grid-cols-2">
    @csrf
    @if ($editing) @method('PUT') @endif

    <label class="text-sm">Marca
        <input name="brand" value="{{ old('brand', $vehicle->brand) }}" required class="{{ $field }}"></label>
    <label class="text-sm">Modelo
        <input name="model" value="{{ old('model', $vehicle->model) }}" required class="{{ $field }}"></label>
    <label class="text-sm">Ano
        <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" required class="{{ $field }}"></label>
    <label class="text-sm">Placa
        <input name="plate" value="{{ old('plate', $vehicle->plate) }}" required class="{{ $field }} uppercase"></label>
    <label class="text-sm">Cor
        <input name="color" value="{{ old('color', $vehicle->color) }}" required class="{{ $field }}"></label>
    <label class="text-sm">Câmbio
        <select name="transmission" class="{{ $field }}">
            @foreach (['manual' => 'Manual', 'automatico' => 'Automático'] as $k => $v)
                <option value="{{ $k }}" @selected(old('transmission', $vehicle->transmission) === $k)>{{ $v }}</option>
            @endforeach
        </select></label>
    <label class="text-sm">Lugares
        <input type="number" name="seats" value="{{ old('seats', $vehicle->seats ?? 5) }}" required class="{{ $field }}"></label>
    <label class="text-sm">Cidade
        <input name="city" value="{{ old('city', $vehicle->city) }}" required class="{{ $field }}"></label>
    <label class="text-sm">Diária (R$)
        <input type="number" step="0.01" name="daily_price" value="{{ old('daily_price', $vehicle->daily_price) }}" required class="{{ $field }}"></label>
    <div class="text-sm md:col-span-2">
        <label>Fotos (até {{ \App\Models\Vehicle::MAX_PHOTOS }}, JPG/PNG/WebP, 3 MB cada)
            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="{{ $field }}">
        </label>

        @if ($editing && $vehicle->photos->isNotEmpty())
            <p class="mt-3 text-gray-500">Fotos atuais — escolha a capa ou marque para remover:</p>
            <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach ($vehicle->photos as $photo)
                    <div class="border rounded p-2">
                        <img src="{{ $photo->url }}" alt="" class="h-24 w-full object-cover rounded">
                        <label class="mt-2 flex items-center gap-1">
                            <input type="radio" name="cover_photo_id" value="{{ $photo->id }}" @checked(old('cover_photo_id', $vehicle->photos->first()->id) == $photo->id)>
                            Capa
                        </label>
                        <label class="flex items-center gap-1 text-red-600">
                            <input type="checkbox" name="remove_photos[]" value="{{ $photo->id }}" @checked(in_array($photo->id, old('remove_photos', [])))>
                            Remover
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <label class="text-sm md:col-span-2">Descrição
        <textarea name="description" rows="4" class="{{ $field }}">{{ old('description', $vehicle->description) }}</textarea></label>

    <label class="text-sm flex items-center gap-2 md:col-span-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vehicle->is_active)) class="rounded">
        Anúncio ativo (visível nas buscas)
    </label>

    <div class="md:col-span-2 flex gap-3">
        <button class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Salvar</button>
        <a href="{{ route('owner.vehicles.index') }}" class="px-5 py-2 text-gray-600 hover:underline">Cancelar</a>
    </div>
</form>
@endsection
