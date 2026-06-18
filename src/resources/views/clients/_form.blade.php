@csrf
<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Type de client</label>
        <select name="type" class="form-select">
            <option value="particulier" @selected(old('type', $client->type) === 'particulier')>Particulier</option>
            <option value="professionnel" @selected(old('type', $client->type) === 'professionnel')>Professionnel</option>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Nom / Raison sociale <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $client->name) }}" required
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Interlocuteur</label>
        <input type="text" name="contact_name" value="{{ old('contact_name', $client->contact_name) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">SIRET</label>
        <input type="text" name="siret" value="{{ old('siret', $client->siret) }}" maxlength="14" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Téléphone</label>
        <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">E-mail</label>
        <input type="email" name="email" value="{{ old('email', $client->email) }}"
               class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Adresse</label>
        <input type="text" name="address" value="{{ old('address', $client->address) }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Code postal</label>
        <input type="text" name="zip" value="{{ old('zip', $client->zip) }}" maxlength="10" class="form-control">
    </div>
    <div class="col-md-6">
        <label class="form-label">Ville</label>
        <input type="text" name="city" value="{{ old('city', $client->city) }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Dernier contact</label>
        <input type="date" name="last_contact_at"
               value="{{ old('last_contact_at', optional($client->last_contact_at)->format('Y-m-d')) }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $client->notes) }}</textarea>
    </div>
</div>
