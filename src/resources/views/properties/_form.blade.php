@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title', $property->title) }}" required
                       class="form-control @error('title') is-invalid @enderror" placeholder="Ex : T3 lumineux centre-ville">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Référence</label>
                <input type="text" name="reference" value="{{ old('reference', $property->reference) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    @foreach(\App\Models\Property::TYPES as $k => $l)<option value="{{ $k }}" @selected(old('type', $property->type) === $k)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Transaction</label>
                <select name="transaction" class="form-select">
                    <option value="vente" @selected(old('transaction', $property->transaction) === 'vente')>Vente</option>
                    <option value="location" @selected(old('transaction', $property->transaction) === 'location')>Location</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Property::STATUSES as $k => $l)<option value="{{ $k }}" @selected(old('status', $property->status) === $k)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix (€)</label>
                <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $property->price ?? 0) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Surface (m²)</label>
                <input type="number" step="0.01" min="0" name="surface" value="{{ old('surface', $property->surface) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Pièces</label>
                <input type="number" min="0" name="rooms" value="{{ old('rooms', $property->rooms) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">DPE</label>
                <select name="dpe" class="form-select">
                    <option value="">—</option>
                    @foreach(['A','B','C','D','E','F','G'] as $g)<option value="{{ $g }}" @selected(old('dpe', $property->dpe) === $g)>{{ $g }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Mandant / propriétaire</label>
                <input type="text" name="owner_name" value="{{ old('owner_name', $property->owner_name) }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" value="{{ old('address', $property->address) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Code postal</label>
                <input type="text" name="zip" value="{{ old('zip', $property->zip) }}" maxlength="10" class="form-control">
            </div>
            <div class="col-md-9">
                <label class="form-label">Ville</label>
                <input type="text" name="city" value="{{ old('city', $property->city) }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $property->description) }}</textarea>
            </div>
        </div>
    </div>
</div>
