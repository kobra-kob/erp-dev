@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Nom du chantier <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Project::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $project->status) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Client</label>
                <select name="client_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $project->client_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Budget (€)</label>
                <input type="number" step="0.01" min="0" name="budget" value="{{ old('budget', $project->budget) }}" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" value="{{ old('address', $project->address) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Code postal</label>
                <input type="text" name="zip" value="{{ old('zip', $project->zip) }}" maxlength="10" class="form-control">
            </div>
            <div class="col-md-5">
                <label class="form-label">Ville</label>
                <input type="text" name="city" value="{{ old('city', $project->city) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Début</label>
                <input type="date" name="start_date" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fin</label>
                <input type="date" name="end_date" value="{{ old('end_date', optional($project->end_date)->format('Y-m-d')) }}"
                       class="form-control @error('end_date') is-invalid @enderror">
                @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">Avancement : <span id="progressVal">{{ old('progress', $project->progress) }}</span> %</label>
                <input type="range" min="0" max="100" step="5" name="progress" value="{{ old('progress', $project->progress) }}"
                       class="form-range" oninput="document.getElementById('progressVal').textContent = this.value">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $project->description) }}</textarea>
            </div>
        </div>
    </div>
</div>
