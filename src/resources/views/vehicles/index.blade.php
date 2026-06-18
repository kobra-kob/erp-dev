@extends('layouts.app')
@section('title', 'Parc véhicules')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-car-front-fill me-2" style="color:#dc2626"></i>Parc véhicules</h1>
            <p class="text-muted mb-0">{{ $vehicles->total() }} véhicule(s).</p>
        </div>
        <a href="{{ route('vehicles.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouveau véhicule</a>
    </div>

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('vehicles.index') }}" class="btn btn-sm {{ !$status && !$condition ? 'btn-dark' : 'btn-outline-secondary' }}">Tous</a>
        <a href="{{ route('vehicles.index', ['condition' => 'neuf']) }}" class="btn btn-sm {{ $condition === 'neuf' ? 'btn-dark' : 'btn-outline-secondary' }}">Neuf</a>
        <a href="{{ route('vehicles.index', ['condition' => 'occasion']) }}" class="btn btn-sm {{ $condition === 'occasion' ? 'btn-dark' : 'btn-outline-secondary' }}">Occasion</a>
        @foreach(\App\Models\Vehicle::STATUSES as $key => $label)
            <a href="{{ route('vehicles.index', ['status' => $key]) }}" class="btn btn-sm {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Véhicule</th><th>Immat.</th><th>Année</th><th>Km</th><th>Énergie</th><th>État</th><th class="text-end">Prix</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($vehicles as $v)
                        <tr>
                            <td class="fw-semibold">{{ $v->name() }}
                                @if($v->vin)<div class="text-muted small">VIN {{ $v->vin }}</div>@endif
                            </td>
                            <td>{{ $v->registration ?: '—' }}</td>
                            <td>{{ $v->year ?: '—' }}</td>
                            <td>{{ $v->mileage !== null ? number_format($v->mileage, 0, ',', ' ').' km' : '—' }}</td>
                            <td>{{ $v->energyLabel() }}</td>
                            <td>{{ $v->condition === 'neuf' ? 'Neuf' : 'Occasion' }}</td>
                            <td class="text-end fw-semibold">@eur($v->price)</td>
                            <td><span class="badge text-bg-{{ $v->statusColor() }}">{{ $v->statusLabel() }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('vehicles.edit', $v) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('vehicles.destroy', $v) }}" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-car-front fs-1 d-block mb-2"></i>Aucun véhicule.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $vehicles->links() }}</div>
@endsection
