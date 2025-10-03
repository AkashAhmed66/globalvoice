<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Phonebook\App\Models\Group;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Phonebook\App\Models\Group>
 */
class GroupFactory extends Factory
{
  protected $model = Group::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'user_id' => fake()->numberBetween(990, 1990),
      'name' => fake()->words(3, true),
      'type' => fake()->randomElement(['Public', 'Private']),
      'status' => fake()->randomElement(['Active', 'Inactive']),
      'reseller_id' => fake()->numberBetween(1, 10),
      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
}
