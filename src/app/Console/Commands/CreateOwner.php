<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

/**
 * Crée une entreprise et son compte propriétaire (owner/ADMIN).
 * Scriptable (utilisé par l'installeur) et idempotent : si l'e-mail existe déjà,
 * la commande s'arrête sans rien créer.
 */
class CreateOwner extends Command
{
    protected $signature = 'app:create-owner
        {--email= : Adresse e-mail du propriétaire}
        {--password= : Mot de passe}
        {--name=Administrateur : Nom du propriétaire}
        {--company=Mon entreprise : Nom de l\'entreprise}';

    protected $description = 'Crée une entreprise et son compte propriétaire (ADMIN).';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email'], 'password' => ['required', Password::min(8)]],
        );
        if ($validator->fails()) {
            $this->error('Données invalides : ' . implode(' ', $validator->errors()->all()));

            return self::INVALID;
        }

        if (User::where('email', $email)->exists()) {
            $this->warn("Un compte existe déjà pour {$email} — aucune création.");

            return self::SUCCESS;
        }

        $company = Company::create(['name' => $this->option('company'), 'subscription' => 'pro']);
        $user = User::create([
            'company_id' => $company->id,
            'name'       => $this->option('name'),
            'email'      => $email,
            'password'   => Hash::make($password),
            'role'       => User::ROLE_ADMIN,
        ]);
        $company->update(['owner_id' => $user->id]);

        $this->info("Propriétaire créé : {$email} (entreprise « {$company->name} »).");

        return self::SUCCESS;
    }
}
