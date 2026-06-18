@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" name="title" value="{{ old('title', $intervention->title) }}" required
                       class="form-control @error('title') is-invalid @enderror" placeholder="Ex : Dépannage chauffe-eau">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Statut</label>
                <select name="status" class="form-select">
                    @foreach(\App\Models\Intervention::STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected(old('status', $intervention->status ?? 'planned') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Début <span class="text-danger">*</span></label>
                <input type="datetime-local" name="start_at"
                       value="{{ old('start_at', optional($intervention->start_at)->format('Y-m-d\TH:i')) }}"
                       class="form-control @error('start_at') is-invalid @enderror" required>
                @error('start_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Fin <span class="text-danger">*</span></label>
                <input type="datetime-local" name="end_at"
                       value="{{ old('end_at', optional($intervention->end_at)->format('Y-m-d\TH:i')) }}"
                       class="form-control @error('end_at') is-invalid @enderror" required>
                @error('end_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">Technicien</label>
                <select name="technician_id" class="form-select">
                    <option value="">— Non assigné —</option>
                    @foreach($technicians as $t)
                        <option value="{{ $t->id }}" @selected(old('technician_id', $intervention->technician_id) == $t->id)>{{ $t->name }}@if($t->skill) ({{ $t->skill }})@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Client</label>
                <select name="client_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $intervention->client_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Chantier</label>
                <select name="project_id" class="form-select">
                    <option value="">— Aucun —</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(old('project_id', $intervention->project_id) == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Adresse</label>
                <input type="text" name="address" value="{{ old('address', $intervention->address) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $intervention->notes) }}</textarea>
            </div>
        </div>
    </div>
</div>
