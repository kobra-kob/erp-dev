<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Derrière un reverse-proxy HTTPS : si APP_URL est en https, forcer
        // toutes les URLs générées (formulaires, liens) en https — évite le
        // "mixed content" du formulaire de login envoyé en http. En dev local
        // (APP_URL=http://localhost), rien n'est forcé.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Transport Brevo (envoi via API HTTPS, port 443) — contourne le blocage
        // SMTP des FAI/pfSense. Activé en mettant MAIL_MAILER=brevo + BREVO_API_KEY.
        Mail::extend('brevo', function () {
            return (new BrevoTransportFactory())->create(
                new Dsn('brevo+api', 'default', config('services.brevo.key'))
            );
        });

        // Pagination rendue avec le thème Bootstrap 5.
        Paginator::useBootstrapFive();

        // @eur(1234.5) => "1 234,50 €"
        Blade::directive('eur', fn ($expr) => "<?php echo number_format((float)($expr), 2, ',', ' ').' €'; ?>");
    }
}
