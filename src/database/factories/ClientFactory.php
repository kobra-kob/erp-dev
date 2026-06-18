<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $pro = fake()->boolean(40);

        return [
            'type'            => $pro ? 'professionnel' : 'particulier',
            'name'            => $pro ? fake()->company() : fake()->name(),
            'contact_name'    => $pro ? fake()->name() : null,
            'email'           => fake()->safeEmail(),
            'phone'           => fake()->phoneNumber(),
            'address'         => fake()->streetAddress(),
            'city'            => fake()->city(),
            'zip'             => fake()->postcode(),
            'siret'           => $pro ? fake()->numerify('##############') : null,
            'notes'           => fake()->boolean(30) ? fake()->sentence() : null,
            'last_contact_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-3 months', 'now') : null,
        ];
    }
}
