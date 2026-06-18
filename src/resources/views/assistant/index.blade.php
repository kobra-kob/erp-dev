@extends('layouts.app')
@section('title', 'Assistant Artisan')

@push('head')
<style>
    .chat-box { height: 60vh; overflow-y: auto; background: #f8fafc; border-radius: .75rem; }
    .msg { max-width: 80%; padding: .6rem .9rem; border-radius: 1rem; white-space: pre-wrap; line-height: 1.45; }
    .msg-user { background: #2563eb; color: #fff; border-bottom-right-radius: .25rem; }
    .msg-bot  { background: #fff; border: 1px solid #e2e8f0; border-bottom-left-radius: .25rem; }
    .typing span { animation: blink 1.2s infinite; }
    @keyframes blink { 0%,100%{opacity:.2} 50%{opacity:1} }
</style>
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-robot text-primary me-2"></i>Assistant Artisan</h1>
            <p class="text-muted mb-0">Posez vos questions sur votre activité.</p>
        </div>
        @if($aiEnabled)
            <span class="badge text-bg-success"><i class="bi bi-stars me-1"></i>IA activée</span>
        @else
            <span class="badge text-bg-secondary" title="Réponses calculées depuis vos données (gratuit). Ajoutez une clé pour l'IA."><i class="bi bi-database me-1"></i>Mode données</span>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div id="chat" class="chat-box p-3 mb-3 d-flex flex-column gap-2">
                <div class="msg msg-bot align-self-start">
                    Bonjour {{ explode(' ', auth()->user()->name)[0] }} 👋 Je suis votre assistant. Comment puis-je aider ?
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach($suggestions as $s)
                    <button type="button" class="btn btn-sm btn-outline-secondary suggestion">{{ $s }}</button>
                @endforeach
            </div>

            <form id="chatForm" class="d-flex gap-2">
                <input type="text" id="message" class="form-control" placeholder="Écrivez votre question…" autocomplete="off" required>
                <button type="submit" class="btn btn-primary px-3"><i class="bi bi-send"></i></button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const chat = document.getElementById('chat');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('message');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const url = "{{ route('assistant.message') }}";
    const history = [];

    function bubble(text, who) {
        const div = document.createElement('div');
        div.className = 'msg align-self-' + (who === 'user' ? 'end msg-user' : 'start msg-bot');
        div.textContent = text;
        chat.appendChild(div);
        chat.scrollTop = chat.scrollHeight;
        return div;
    }

    async function send(text) {
        bubble(text, 'user');
        history.push({ role: 'user', content: text });
        const typing = bubble('···', 'bot');
        typing.classList.add('typing');

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ message: text, history: history.slice(-6) }),
            });
            const data = await res.json();
            typing.remove();
            const b = bubble(data.answer || 'Désolé, une erreur est survenue.', 'bot');
            history.push({ role: 'assistant', content: data.answer || '' });
            if (data.source === 'local') {
                const tag = document.createElement('div');
                tag.className = 'text-muted small mt-1';
                tag.textContent = '↳ réponse calculée depuis vos données';
                b.appendChild(tag);
            }
        } catch (e) {
            typing.remove();
            bubble("Impossible de joindre l'assistant.", 'bot');
        }
    }

    form.addEventListener('submit', e => {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        send(text);
    });

    document.querySelectorAll('.suggestion').forEach(btn =>
        btn.addEventListener('click', () => send(btn.textContent)));
})();
</script>
@endpush
