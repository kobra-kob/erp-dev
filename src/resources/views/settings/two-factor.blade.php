@extends('layouts.app')
@section('title', 'Activer la double authentification')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('settings.index') }}" class="text-decoration-none">Paramètres</a></li>
            <li class="breadcrumb-item active">Double authentification</li>
        </ol>
    </nav>

    <h1 class="h3 fw-bold mb-4">Activer la double authentification</h1>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">1. Scannez le QR code</h2>
                    <p class="text-muted small">Avec Google Authenticator, Microsoft Authenticator, FreeOTP…</p>
                    <div class="text-center my-3">
                        <div id="qr" class="d-inline-block p-3 bg-white border rounded-3"></div>
                    </div>
                    <p class="small text-muted mb-1">Ou saisissez cette clé manuellement :</p>
                    <code class="d-block bg-light p-2 rounded text-center user-select-all">{{ $secret }}</code>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h6 mb-3">2. Confirmez avec un code</h2>

                    @error('code')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

                    <form method="POST" action="{{ route('two-factor.confirm') }}">
                        @csrf
                        <label class="form-label">Code à 6 chiffres</label>
                        <input type="text" name="code" inputmode="numeric" maxlength="7" required autofocus
                               class="form-control form-control-lg text-center" placeholder="000000" style="letter-spacing:.4rem;">
                        <button type="submit" class="btn btn-primary w-100 mt-3"><i class="bi bi-shield-check me-1"></i>Activer</button>
                        <a href="{{ route('settings.index') }}" class="btn btn-light w-100 mt-2">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    new QRCode(document.getElementById('qr'), {
        text: @json($otpauthUrl),
        width: 200, height: 200,
    });
</script>
@endpush
