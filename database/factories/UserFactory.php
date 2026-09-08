<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * La contraseña usada por la factory.
     */
    protected static ?string $password;

    /**
     * Define los datos por defecto del usuario.
     */
    public function definition(): array
    {
        $nombre = fake()->name();

        return [
            'name' => $nombre,
            'nombre_completo' => $nombre,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'fecha_registro' => now(),
        ];
    }

    /**
     * Usuario con correo sin verificar.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}