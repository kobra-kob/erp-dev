<?php

namespace App\Http\Controllers;

use App\Mail\AccountWelcomeMail;
use App\Models\Role;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Gestion des utilisateurs (employés) de l'entreprise. Réservé aux ADMIN.
 * L'isolation par entreprise est vérifiée manuellement car le modèle User
 * n'est pas soumis au CompanyScope (sinon la connexion casserait).
 */
class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = User::where('company_id', Auth::user()->company_id)
            ->orderBy('name')
            ->paginate(15);

        return view('employees.index', [
            'employees' => $employees,
            'company'   => Auth::user()->company,
        ]);
    }

    public function create(): View
    {
        return view('employees.create', [
            'employee' => new User(['role' => User::ROLE_EMPLOYE, 'is_active' => true]),
            'roles'    => $this->companyRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->company;
        if (! $company->canAddEmployee()) {
            return back()->withErrors([
                'employee' => 'Limite atteinte : ' . \App\Models\Company::MAX_EMPLOYEES
                    . ' employés maximum par entreprise (en plus du propriétaire).',
            ])->withInput();
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', $this->roleRule()],
            'skill'    => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $employee = User::create([
            'company_id' => Auth::user()->company_id,
            'name'       => $data['name'],
            'email'      => $data['email'],
            'skill'      => $data['skill'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'password'   => Hash::make($data['password']),
            'is_active'  => true,
        ] + $this->resolveRole($data['role']));

        Notifier::send($employee->email, new AccountWelcomeMail($employee, createdByAdmin: true));

        return redirect()->route('employees.index')->with('status', 'Employé ajouté (e-mail de bienvenue envoyé).');
    }

    public function show(User $employee): View
    {
        $this->authorizeCompany($employee);
        $employee->load('documents');

        return view('employees.show', compact('employee'));
    }

    public function edit(User $employee): View
    {
        $this->authorizeCompany($employee);

        return view('employees.edit', ['employee' => $employee, 'roles' => $this->companyRoles()]);
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $this->authorizeCompany($employee);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee->id)],
            'role'     => ['required', $this->roleRule()],
            'skill'    => ['nullable', 'string', 'max:255'],
            'phone'    => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $employee->fill([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'skill'     => $data['skill'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ] + $this->resolveRole($data['role']));

        // Anti-verrouillage : un admin ne peut pas se rétrograder ni se désactiver.
        if ($employee->id === Auth::id()) {
            $employee->role = User::ROLE_ADMIN;
            $employee->role_id = null;
            $employee->is_active = true;
        }

        if (! empty($data['password'])) {
            $employee->password = Hash::make($data['password']);
        }

        $employee->save();

        return redirect()->route('employees.index')->with('status', 'Employé mis à jour.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        $this->authorizeCompany($employee);

        if ($employee->id === Auth::id()) {
            return back()->withErrors(['employee' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        if ($employee->id === $employee->company->owner_id) {
            return back()->withErrors(['employee' => 'Le propriétaire de l\'entreprise ne peut pas être supprimé.']);
        }

        $employee->delete();

        return redirect()->route('employees.index')->with('status', 'Employé supprimé.');
    }

    private function authorizeCompany(User $employee): void
    {
        abort_unless($employee->company_id === Auth::user()->company_id, 404);
    }

    /** Rôles personnalisés de l'entreprise (pour le sélecteur). */
    private function companyRoles()
    {
        return Role::where('company_id', Auth::user()->company_id)->orderBy('name')->get();
    }

    /** Règle de validation : un rôle intégré ou « custom:{id} » de l'entreprise. */
    private function roleRule(): \Illuminate\Validation\Rules\In
    {
        $custom = $this->companyRoles()->map(fn ($r) => 'custom:' . $r->id)->all();

        return Rule::in(array_merge(User::ROLES, $custom));
    }

    /**
     * Traduit la valeur du sélecteur en couple (role, role_id).
     *
     * @return array{role: string, role_id: ?int}
     */
    private function resolveRole(string $value): array
    {
        if (str_starts_with($value, 'custom:')) {
            return ['role' => User::ROLE_CUSTOM, 'role_id' => (int) substr($value, 7)];
        }

        return ['role' => $value, 'role_id' => null];
    }
}
