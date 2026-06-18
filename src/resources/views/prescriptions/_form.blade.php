@csrf
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Client <span class="text-danger">*</span></label>
                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $prescription->client_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Prescripteur</label>
                <input type="text" name="prescriber" value="{{ old('prescriber', $prescription->prescriber) }}" class="form-control" placeholder="Dr …">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="prescribed_at" value="{{ old('prescribed_at', optional($prescription->prescribed_at)->format('Y-m-d')) }}" class="form-control" required>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <h2 class="h6 fw-bold mb-3">Correction</h2>
        <table class="table align-middle">
            <thead class="table-light"><tr><th>Œil</th><th>Sphère</th><th>Cylindre</th><th>Axe (°)</th><th>Addition</th></tr></thead>
            <tbody>
                @foreach(['od' => 'Œil droit (OD)', 'og' => 'Œil gauche (OG)'] as $eye => $eyeLabel)
                    <tr>
                        <td class="fw-semibold">{{ $eyeLabel }}</td>
                        <td><input type="number" step="0.25" name="{{ $eye }}_sphere" value="{{ old("{$eye}_sphere", $prescription->{"{$eye}_sphere"}) }}" class="form-control form-control-sm" placeholder="+0.00"></td>
                        <td><input type="number" step="0.25" name="{{ $eye }}_cylinder" value="{{ old("{$eye}_cylinder", $prescription->{"{$eye}_cylinder"}) }}" class="form-control form-control-sm" placeholder="-0.00"></td>
                        <td><input type="number" step="1" min="0" max="180" name="{{ $eye }}_axis" value="{{ old("{$eye}_axis", $prescription->{"{$eye}_axis"}) }}" class="form-control form-control-sm"></td>
                        <td><input type="number" step="0.25" min="0" name="{{ $eye }}_addition" value="{{ old("{$eye}_addition", $prescription->{"{$eye}_addition"}) }}" class="form-control form-control-sm" placeholder="+0.00"></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Écart pupillaire (mm)</label>
                <input type="number" min="40" max="90" name="pupillary_distance" value="{{ old('pupillary_distance', $prescription->pupillary_distance) }}" class="form-control">
            </div>
            <div class="col-md-9">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" value="{{ old('notes', $prescription->notes) }}" class="form-control">
            </div>
        </div>
    </div>
</div>
