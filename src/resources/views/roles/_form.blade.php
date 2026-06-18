@csrf
@php($selected = old('modules', $role->modules ?? []))
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                       class="form-control @error('name') is-invalid @enderror" placeholder="Ex : Comptable, Commercial…">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Partir d'un rôle intégré (optionnel)</label>
                <select id="roleTemplate" class="form-select">
                    <option value="">— Aucun modèle —</option>
                    @foreach($templates as $key => $tpl)
                        <option value="{{ $key }}">{{ $tpl['label'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">Pré-coche les modules de ce rôle, que vous pouvez ensuite ajuster.</div>
            </div>
        </div>

        <label class="form-label">Modules accessibles à ce rôle</label>
        <p class="text-muted small">Cochez les applications que ce rôle pourra voir et utiliser. (Paramètres et Congés sont toujours accessibles.)</p>
        <div class="row g-2">
            @foreach($modules as $m)
                <div class="col-md-4 col-sm-6">
                    <label class="d-flex align-items-center gap-2 border rounded-3 p-2 h-100" style="cursor:pointer;">
                        <input type="checkbox" class="form-check-input mt-0" name="modules[]" value="{{ $m['key'] }}"
                               @checked(in_array($m['key'], $selected, true))>
                        <span class="rounded d-inline-flex align-items-center justify-content-center text-white"
                              style="width:32px;height:32px;background:{{ $m['color'] }}"><i class="bi {{ $m['icon'] }}"></i></span>
                        <span class="small fw-semibold">{{ $m['label'] }}</span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const templates = @json(collect($templates)->map(fn ($t) => $t['modules']));
    const select = document.getElementById('roleTemplate');
    if (!select) return;
    select.addEventListener('change', function () {
        const mods = templates[this.value] || [];
        document.querySelectorAll('input[name="modules[]"]').forEach(cb => {
            cb.checked = mods.includes(cb.value);
        });
    });
})();
</script>
@endpush
