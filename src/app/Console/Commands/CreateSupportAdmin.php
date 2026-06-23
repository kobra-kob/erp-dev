<?php

namespace App\Console\Commands;

use App\Models\SupportUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Crée un compte de la console de support (super-admin).
 * Idempotent : si l'e-mail existe déjà, ne fait rien.
 */
class CreateSupportAdmin extends Command
{
    protected $signature = 'support:create-admin
        {--email= : Adresse e-mail du compte support}
        {--password= : Mot de passe}
        {--name=Support : Nom affiché}';

    protected $description = 'Crée un compte super-admin pour la console de support.';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $password = (string) $this->option('password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email'], 'password' => ['required', Password::min(12)]],
        );
        if ($validator->fails()) {
            $this->error('Données invalides : ' . implode(' ', $validator->errors()->all()));

            return self::INVALID;
        }

        if (SupportUser::where('email', $email)->exists()) {
            $this->warn("Un compte support existe déjà pour {$email} — aucune création.");

            return self::SUCCESS;
        }

        SupportUser::create([
            'name'     => $this->option('name'),
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Compte support créé : {$email}.");

        return self::SUCCESS;
    }
}
