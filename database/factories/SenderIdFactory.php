<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Smsconfig\App\Models\SenderId;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Smsconfig\App\Models\SenderId>
 */
class SenderIdFactory extends Factory
{
    protected $model = SenderId::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => fake()->numberBetween(991, 1991),
            'reseller_id' => fake()->numberBetween(1, 10),
            'senderid' => fake()->unique()->buildingNumber(),
            'status' => fake()->randomElement(['Active', 'Inactive']),
            'count' => fake()->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
            'assigned_user_id' => fake()->numberBetween(1, 1000),
        ];
    }
}
