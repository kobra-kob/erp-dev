@extends('support.layouts.app')
@section('title', 'Audit')

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h1 class="h3 text-white mb-0">Journal d'audit</h1>
        <form method="GET" class="d-flex gap-2">
            <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Toutes les actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card border-secondary">
        <div class="table-responsive">
            <table class="table table-dark table-hover table-sm align-middle mb-0">
                <thead>
                    <tr><th>Date</th><th>Opérateur</th><th>Action</th><th>Tenant</th><th>Description</th><th>IP</th></tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="small text-white-50">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td class="small">{{ $log->supportUser?->name ?? '—' }}</td>
                            <td><code class="text-info">{{ $log->action }}</code></td>
                            <td class="small">{{ $log->company?->name ?? '—' }}</td>
                            <td class="small text-white-50">{{ $log->description }}</td>
                            <td class="small text-white-50">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-white-50 py-4">Aucune entrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
@endsection
