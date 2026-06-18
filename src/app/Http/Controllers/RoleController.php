<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestion des rôles personnalisés de l'entreprise (réservé ADMIN).
 * Un rôle = un nom + une liste de modules autorisés (cases à cocher).
 */
class RoleController extends Controller
{
    /** Modules pouvant être attribués à un rôle (hors modules toujours accessibles / admin). */
    public static function assignableModules(): array
    {
        return collect(config('modules'))
            ->reject(fn ($m, $key) => in_array($key, ['settings', 'leaves', 'employees'], true))
            ->map(fn ($m, $key) => ['key' => $key] + $m)
            ->values()->all();
    }

    /**
     * Jeux de modules des rôles intégrés, pour pré-remplir un nouveau rôle.
     *
     * @return array<string, array{label: string, modules: array<int, string>}>
     */
    public static function builtinTemplates(): array
    {
        $assignable = array_column(self::assignableModules(), 'key');
        $roles = ['ADMIN' => 'Administrateur', 'GERANT' => 'Gérant', 'EMPLOYE' => 'Employé'];

        $templates = [];
        foreach ($roles as $key => $label) {
            $modules = collect(config('modules'))
                ->filter(fn ($m, $k) => in_array($k, $assignable, true) && in_array($key, $m['roles'], true))
                ->keys()->values()->all();
            $templates[$key] = ['label' => $label, 'modules' => $modules];
        }

        return $templates;
    }

    public function index(): View
    {
        return view('roles.index', [
            'roles'    => Role::withCount('users')->orderBy('name')->get(),
            'modules'  => self::assignableModules(),
        ]);
    }

    public function create(): View
    {
        return view('roles.create', [
            'role'      => new Role(['modules' => []]),
            'modules'   => self::assignableModules(),
            'templates' => self::builtinTemplates(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Role::create($this->validated($request));

        return redirect()->route('roles.index')->with('status', 'Rôle créé.');
    }

    public function edit(Role $role): View
    {
        return view('roles.edit', [
            'role'      => $role,
            'modules'   => self::assignableModules(),
            'templates' => self::builtinTemplates(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $role->update($this->validated($request));

        return redirect()->route('roles.index')->with('status', 'Rôle mis à jour.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Ce rôle est attribué à des employés. Réaffectez-les avant de le supprimer.']);
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'Rôle supprimé.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $allowed = array_column(self::assignableModules(), 'key');

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'modules'   => ['array'],
            'modules.*' => [Rule::in($allowed)],
        ]);

        return [
            'name'    => $data['name'],
            'modules' => array_values($data['modules'] ?? []),
        ];
    }
}
