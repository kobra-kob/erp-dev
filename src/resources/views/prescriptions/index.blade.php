@extends('layouts.app')
@section('title', 'Ordonnances')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-eyeglasses me-2" style="color:#0ea5e9"></i>Ordonnances</h1>
            <p class="text-muted mb-0">{{ $prescriptions->total() }} ordonnance(s).</p>
        </div>
        <a href="{{ route('prescriptions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle ordonnance</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Client</th><th>Date</th><th>Prescripteur</th><th>OD (S/C/A)</th><th>OG (S/C/A)</th><th>Add.</th><th>EP</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($prescriptions as $p)
                        <tr>
                            <td class="fw-semibold">{{ $p->client?->name }}</td>
                            <td class="text-muted">{{ $p->prescribed_at->format('d/m/Y') }}</td>
                            <td>{{ $p->prescriber ?: '—' }}</td>
                            <td>{{ \App\Models\Prescription::fmt($p->od_sphere) }} / {{ \App\Models\Prescription::fmt($p->od_cylinder) }} / {{ $p->od_axis ?? '—' }}°</td>
                            <td>{{ \App\Models\Prescription::fmt($p->og_sphere) }} / {{ \App\Models\Prescription::fmt($p->og_cylinder) }} / {{ $p->og_axis ?? '—' }}°</td>
                            <td>{{ $p->od_addition || $p->og_addition ? '+'.number_format(max($p->od_addition,$p->og_addition),2) : '—' }}</td>
                            <td>{{ $p->pupillary_distance ? $p->pupillary_distance.' mm' : '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('prescriptions.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('prescriptions.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-eyeglasses fs-1 d-block mb-2"></i>Aucune ordonnance.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $prescriptions->links() }}</div>
@endsection
