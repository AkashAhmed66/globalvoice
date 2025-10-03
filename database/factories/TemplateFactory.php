<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Messages\App\Models\Template;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Message\App\Models\Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'description' => fake()->text(320),
            'user_id' => fake()->numberBetween(1, 1000),
            'status' => fake()->randomElement(['Active', 'Inactive']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
