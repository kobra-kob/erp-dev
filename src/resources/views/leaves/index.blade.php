@extends('layouts.app')
@section('title', 'Congés')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-umbrella-fill me-2" style="color:#14b8a6"></i>Congés</h1>
            <p class="text-muted mb-0">
                {{ $isManager ? 'Demandes de l\'équipe' : 'Mes demandes' }} · {{ $leaves->total() }} au total.
            </p>
        </div>
        <a href="{{ route('leaves.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle demande</a>
    </div>

    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    @if($isManager && $pendingCount > 0)
        <div class="alert alert-warning d-flex align-items-center">
            <i class="bi bi-hourglass-split me-2"></i>{{ $pendingCount }} demande(s) en attente de validation.
        </div>
    @endif

    <div class="mb-3 d-flex gap-1 flex-wrap">
        <a href="{{ route('leaves.index') }}" class="btn btn-sm {{ !$status ? 'btn-dark' : 'btn-outline-secondary' }}">Toutes</a>
        @foreach(\App\Models\LeaveRequest::STATUSES as $key => $label)
            <a href="{{ route('leaves.index', ['status' => $key]) }}"
               class="btn btn-sm {{ $status === $key ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @if($isManager)<th>Employé</th>@endif
                        <th>Type</th><th>Période</th><th class="text-center">Jours</th><th>Statut</th><th>Suivi</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            @if($isManager)<td class="fw-semibold">{{ $leave->user?->name }}</td>@endif
                            <td>{{ $leave->typeLabel() }}</td>
                            <td>{{ $leave->start_date->format('d/m/Y') }} → {{ $leave->end_date->format('d/m/Y') }}
                                @if($leave->reason)<i class="bi bi-info-circle text-muted ms-1" title="{{ $leave->reason }}"></i>@endif
                            </td>
                            <td class="text-center">{{ $leave->durationDays() }}</td>
                            <td><span class="badge text-bg-{{ $leave->statusColor() }}">{{ $leave->statusLabel() }}</span></td>
                            <td class="small text-muted">
                                @if($leave->reviewed_by)
                                    {{ $leave->reviewer?->name }}
                                    @if($leave->review_comment)<div>« {{ $leave->review_comment }} »</div>@endif
                                @else — @endif
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                @if($leave->isPending())
                                    @if($isManager)
                                        <form method="POST" action="{{ route('leaves.approve', $leave) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-success" title="Approuver"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form method="POST" action="{{ route('leaves.reject', $leave) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-danger" title="Refuser"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    @endif
                                    @if($leave->user_id === auth()->id())
                                        <form method="POST" action="{{ route('leaves.cancel', $leave) }}" class="d-inline"
                                              onsubmit="return confirm('Annuler cette demande ?');">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-secondary" title="Annuler"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $isManager ? 7 : 6 }}" class="text-center text-muted py-5"><i class="bi bi-umbrella fs-1 d-block mb-2"></i>Aucune demande de congés.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $leaves->links() }}</div>
@endsection
