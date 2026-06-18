@csrf
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name', $employee->name) }}" required
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Rôle</label>
                @php($current = old('role', $employee->role === 'CUSTOM' ? 'custom:' . $employee->role_id : $employee->role))
                <select name="role" class="form-select" @disabled($employee->exists && $employee->id === auth()->id())>
                    <optgroup label="Rôles intégrés">
                        <option value="EMPLOYE" @selected($current === 'EMPLOYE')>Employé</option>
                        <option value="GERANT" @selected($current === 'GERANT')>Gérant</option>
                        <option value="ADMIN" @selected($current === 'ADMIN')>Administrateur</option>
                    </optgroup>
                    @if(($roles ?? collect())->isNotEmpty())
                        <optgroup label="Rôles personnalisés">
                            @foreach($roles as $r)
                                <option value="custom:{{ $r->id }}" @selected($current === 'custom:' . $r->id)>{{ $r->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
                <div class="form-text"><a href="{{ route('roles.index') }}">Gérer les rôles &amp; accès</a></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Compétence</label>
                <input type="text" name="skill" value="{{ old('skill', $employee->skill) }}" class="form-control" placeholder="Plombier, Électricien…">
            </div>
            <div class="col-md-4">
                <label class="form-label">Téléphone</label>
                <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" class="form-control">
            </div>

            <div class="col-md-6">
                <label class="form-label">Mot de passe @unless($employee->exists)<span class="text-danger">*</span>@endunless</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                       @required(!$employee->exists)>
                @if($employee->exists)<div class="form-text">Laissez vide pour conserver le mot de passe actuel.</div>@endif
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirmation</label>
                <input type="password" name="password_confirmation" class="form-control" @required(!$employee->exists)>
            </div>

            @if($employee->exists)
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active"
                               @checked(old('is_active', $employee->is_active)) @disabled($employee->id === auth()->id())>
                        <label class="form-check-label" for="active">Compte actif</label>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
