@csrf
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Client <span class="text-danger">*</span></label>
                <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $invoice->client_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-7">
                <label class="form-label">Objet</label>
                <input type="text" name="title" value="{{ old('title', $invoice->title) }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date d'émission <span class="text-danger">*</span></label>
                <input type="date" name="issue_date" value="{{ old('issue_date', optional($invoice->issue_date)->format('Y-m-d')) }}" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Échéance</label>
                <input type="date" name="due_date" value="{{ old('due_date', optional($invoice->due_date)->format('Y-m-d')) }}"
                       class="form-control @error('due_date') is-invalid @enderror">
                @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <h2 class="h6 fw-bold mb-3">Prestations</h2>
        @include('partials.line-items', ['document' => $invoice, 'products' => $products ?? collect()])
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $invoice->notes) }}</textarea>
    </div>
</div>
