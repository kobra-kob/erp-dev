@extends('layouts.app')
@section('title', 'Planning')

@push('head')
<style>
    .fc { background:#fff; border-radius:.75rem; padding:1rem; }
    .fc .fc-button-primary { background:#2563eb; border-color:#2563eb; }
    .fc-event { cursor:pointer; }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-calendar-week-fill text-warning me-2"></i>Planning</h1>
            <p class="text-muted mb-0">Interventions et rendez-vous.</p>
        </div>
        <a href="{{ route('interventions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle intervention</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'fr',
        firstDay: 1,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        buttonText: { today: "Aujourd'hui", month: 'Mois', week: 'Semaine', day: 'Jour' },
        events: "{{ route('interventions.events') }}",
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            if (info.event.url) window.location = info.event.url;
        },
        dateClick: function (info) {
            window.location = "{{ route('interventions.create') }}?date=" + info.dateStr.substring(0, 10);
        },
    });
    calendar.render();
});
</script>
@endpush
