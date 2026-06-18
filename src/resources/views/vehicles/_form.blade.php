@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Marque <span class="text-danger">*</span></label>
                <input type="text" name="brand" value="{{ old('brand', $vehicle->brand) }}" required
                       class="form-control @error('brand') is-invalid @enderror">
                @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Modèle <span class="text-danger">*</span></label>
                <input type="text" name="model" value="{{ old('model', $vehicle->model) }}" required
                       class="form-control @error('model') is-invalid @enderror">
                @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Prix (€)</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $vehicle->price ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">VIN (n° de série)</label>
                <input type="text" name="vin" value="{{ old('vin', $vehicle->vin) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Immatriculation</label>
                <input type="text" name="registration" value="{{ old('registration', $vehicle->registration) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Année</label>
                <input type="number" name="year" value="{{ old('year', $vehicle->year) }}" class="form-control" placeholder="{{ now()->year }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Kilométrage</label>
                <input type="number" min="0" name="mileage" value="{{ old('mileage', $vehicle->mileage) }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Énergie</label>
                <select name="energy" class="form-select">
                    @foreach(\App\Models\Vehicle::ENERGIES as $k => $l)<option value="{{ $k }}" @selected(old('energy', $vehicle->energy) === $k)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">État</label>
                <select name="condition" class="form-select">
                    <option value="occasion" @selected(old('condition', $vehicle->condition) === 'occasion')>Occasion</option>
                    <option value="neuf" @selected(old('condition', $vehicle->condition) === 'neuf')>Neuf</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Vehicle::STATUSES as $k => $l)<option value="{{ $k }}" @selected(old('status', $vehicle->status) === $k)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $vehicle->description) }}</textarea>
            </div>
        </div>
    </div>
</div>
