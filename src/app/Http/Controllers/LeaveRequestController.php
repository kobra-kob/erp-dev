<?php

namespace App\Http\Controllers;

use App\Mail\LeaveReviewedMail;
use App\Mail\LeaveSubmittedMail;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isManager = $user->hasRole('ADMIN', 'GERANT');
        $status = $request->query('status');

        $leaves = LeaveRequest::with('user', 'reviewer')
            ->when(! $isManager, fn ($q) => $q->where('user_id', $user->id))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('start_date')->latest('id')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = LeaveRequest::where('status', 'pending')
            ->when(! $isManager, fn ($q) => $q->where('user_id', $user->id))
            ->count();

        return view('leaves.index', compact('leaves', 'status', 'isManager', 'pendingCount'));
    }

    public function create(): View
    {
        return view('leaves.create', [
            'leave' => new LeaveRequest([
                'type'       => 'conges_payes',
                'start_date' => now()->toDateString(),
                'end_date'   => now()->addDay()->toDateString(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'       => ['required', Rule::in(array_keys(LeaveRequest::TYPES))],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'reason'     => ['nullable', 'string', 'max:1000'],
        ]);

        $leave = LeaveRequest::create($data + [
            'user_id' => $request->user()->id,
            'status'  => 'pending',
        ]);

        // Notifie les responsables (ADMIN / GERANT) de l'entreprise.
        $managers = User::where('company_id', $request->user()->company_id)
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_GERANT])
            ->where('is_active', true)
            ->pluck('email')->all();
        Notifier::send($managers, new LeaveSubmittedMail($leave->load('user')));

        return redirect()->route('leaves.index')->with('status', 'Demande de congés envoyée.');
    }

    /** Annulation par le demandeur (ou un responsable), tant que c'est en attente. */
    public function cancel(Request $request, LeaveRequest $leave): RedirectResponse
    {
        abort_unless(
            $leave->user_id === $request->user()->id || $request->user()->hasRole('ADMIN', 'GERANT'),
            403
        );

        if (! $leave->isPending()) {
            return back()->withErrors(['leave' => 'Seule une demande en attente peut être annulée.']);
        }

        $leave->update(['status' => 'cancelled']);

        return back()->with('status', 'Demande annulée.');
    }

    public function approve(Request $request, LeaveRequest $leave): RedirectResponse
    {
        return $this->review($request, $leave, 'approved', 'Demande approuvée.');
    }

    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        return $this->review($request, $leave, 'rejected', 'Demande refusée.');
    }

    private function review(Request $request, LeaveRequest $leave, string $status, string $message): RedirectResponse
    {
        if (! $leave->isPending()) {
            return back()->withErrors(['leave' => 'Cette demande a déjà été traitée.']);
        }

        $data = $request->validate([
            'review_comment' => ['nullable', 'string', 'max:255'],
        ]);

        $leave->update([
            'status'         => $status,
            'reviewed_by'    => $request->user()->id,
            'reviewed_at'    => now(),
            'review_comment' => $data['review_comment'] ?? null,
        ]);

        // Notifie l'employé de la décision.
        Notifier::send($leave->user?->email, new LeaveReviewedMail($leave->load('user', 'reviewer')));

        return back()->with('status', $message);
    }
}
