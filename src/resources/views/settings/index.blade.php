@extends('layouts.app')
@section('title', 'Paramètres')

@section('content')
    <h1 class="h3 fw-bold mb-4"><i class="bi bi-gear-fill text-secondary me-2"></i>Paramètres</h1>

    <div class="row g-4">
        {{-- Entreprise --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h6 text-uppercase text-muted mb-0">Mon entreprise</h2>
                        @if($user->canViewTenantInfo())
                            <span class="d-inline-flex align-items-center gap-1 small text-muted">
                                <span>ID support</span>
                                <code class="bg-light px-2 py-1 rounded">{{ $company->supportId() }}</code>
                                <button type="button" class="btn btn-sm btn-link p-0 text-secondary" title="Copier"
                                        onclick="navigator.clipboard.writeText('{{ $company->supportId() }}');this.innerHTML='&lt;i class=&quot;bi bi-check2&quot;&gt;&lt;/i&gt;'">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </span>
                        @endif
                    </div>

                    @error('name')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

                    <form method="POST" action="{{ route('settings.company.update') }}">
                        @csrf
                        @method('PUT')
                        <fieldset @disabled(!$user->isAdmin())>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" value="{{ old('name', $company->name) }}" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SIRET</label>
                                <input type="text" name="siret" value="{{ old('siret', $company->siret) }}" maxlength="14" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="address" value="{{ old('address', $company->address) }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Code postal</label>
                                <input type="text" name="zip" value="{{ old('zip', $company->zip) }}" maxlength="10" class="form-control">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Ville</label>
                                <input type="text" name="city" value="{{ old('city', $company->city) }}" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-control">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">E-mail</label>
                                <input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-control">
                            </div>
                        </div>
                        @if($user->isAdmin())
                            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
                        @else
                            <p class="text-muted small mt-3 mb-0"><i class="bi bi-lock me-1"></i>Seul un administrateur peut modifier ces informations.</p>
                        @endif
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sécurité --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 text-uppercase text-muted mb-3">Sécurité</h2>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Double authentification</div>
                            <div class="small text-muted">Protégez votre compte avec un code TOTP.</div>
                        </div>
                        @if($user->hasTwoFactorEnabled())
                            <span class="badge text-bg-success">Activée</span>
                        @else
                            <span class="badge text-bg-secondary">Inactive</span>
                        @endif
                    </div>
                    <div class="mt-3">
                        @if($user->hasTwoFactorEnabled())
                            <form method="POST" action="{{ route('two-factor.destroy') }}"
                                  onsubmit="return confirm('Désactiver la double authentification ?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-shield-slash me-1"></i>Désactiver</button>
                            </form>
                        @else
                            <a href="{{ route('two-factor.show') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-shield-check me-1"></i>Activer la 2FA</a>
                        @endif
                    </div>

                    <hr>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">Mot de passe</div>
                            <div class="small text-muted">Recevez un lien sécurisé par e-mail pour le changer.</div>
                        </div>
                        <form method="POST" action="{{ route('settings.password-reset') }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-envelope-lock me-1"></i>Changer par e-mail</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small text-uppercase">Mon compte</span>
                        <span class="badge text-bg-primary">{{ $user->roleLabel() }}</span>
                    </div>
                    <div class="fw-semibold mt-1">{{ $user->name }}</div>
                    <div class="small text-muted">{{ $user->email }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Apparence des devis / factures (admin) --}}
    @if($user->isAdmin())
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-4">
                <h2 class="h6 text-uppercase text-muted mb-3">Apparence des devis &amp; factures</h2>

                @error('brand_color')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
                @error('logo')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

                <form method="POST" action="{{ route('settings.branding.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Logo de l'entreprise</label>
                            <input type="file" name="logo" accept="image/*" class="form-control" id="logoInput">
                            <div class="form-text">PNG/JPG, max 2 Mo. Apparaît en haut des documents.</div>
                            <div class="mt-2">
                                <img id="logoPreview" src="{{ $company->logoUrl() ?: '' }}" alt=""
                                     class="border rounded bg-light p-1 {{ $company->logoUrl() ? '' : 'd-none' }}"
                                     style="max-height:70px;max-width:180px;object-fit:contain;">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Couleur principale</label>
                            <input type="color" name="brand_color" id="brandColor"
                                   value="{{ old('brand_color', $company->brandColor()) }}" class="form-control form-control-color" style="width:100%;">
                            <label class="form-label mt-3">Couleur du bandeau</label>
                            <input type="color" name="brand_accent" id="brandAccent"
                                   value="{{ old('brand_accent', $company->brandAccent()) }}" class="form-control form-control-color" style="width:100%;">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Forme</label>
                            <select name="document_shape" id="docShape" class="form-select">
                                @foreach(\App\Models\Company::SHAPES as $val => $label)
                                    <option value="{{ $val }}" @selected(old('document_shape', $company->document_shape ?? 'rounded') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Aperçu en direct --}}
                        <div class="col-md-3">
                            <label class="form-label">Aperçu</label>
                            <div id="docPreview" class="border p-2" style="border-radius:{{ $company->documentRadius() }};">
                                <div id="pvBrand" class="fw-bold" style="color:{{ $company->brandColor() }};">{{ $company->name }}</div>
                                <div id="pvBand" class="text-white small px-2 py-1 mt-1"
                                     style="background:{{ $company->brandAccent() }};border-radius:{{ $company->documentRadius() }};">Prestation — Total HT</div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-check-lg me-1"></i>Enregistrer l'apparence</button>
                </form>
            </div>
        </div>

        @push('scripts')
        <script>
        (function () {
            const color = document.getElementById('brandColor');
            const accent = document.getElementById('brandAccent');
            const shape = document.getElementById('docShape');
            const pvBrand = document.getElementById('pvBrand');
            const pvBand = document.getElementById('pvBand');
            const pv = document.getElementById('docPreview');
            const logoInput = document.getElementById('logoInput');
            const logoPreview = document.getElementById('logoPreview');

            function refresh() {
                pvBrand.style.color = color.value;
                pvBand.style.background = accent.value;
                const r = shape.value === 'square' ? '0' : '6px';
                pv.style.borderRadius = r;
                pvBand.style.borderRadius = r;
            }
            [color, accent, shape].forEach(el => el.addEventListener('input', refresh));

            logoInput.addEventListener('change', e => {
                const f = e.target.files[0];
                if (f) { logoPreview.src = URL.createObjectURL(f); logoPreview.classList.remove('d-none'); }
            });
        })();
        </script>
        @endpush
    @endif

    {{-- Journal d'audit --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <h2 class="h6 text-uppercase text-muted mb-3">Journal de connexion (audit)</h2>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Utilisateur</th><th>Évènement</th><th>IP</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                            <tr>
                                <td>{{ $audit->user?->name ?? $audit->email ?? 'Inconnu' }}</td>
                                <td><span class="badge text-bg-light text-dark">{{ $audit->action }}</span></td>
                                <td class="text-muted small">{{ $audit->ip_address }}</td>
                                <td class="text-muted small">{{ $audit->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Aucun évènement enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
