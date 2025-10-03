<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Smsconfig\App\Models\Rate;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Smsconfig\App\Models\Rate>
 */
class RateFactory extends Factory
{
  protected $model = Rate::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'rate_name' => fake()->word(),
      'masking_rate' => fake()->randomFloat(2, 0.01, 2.00),
      'selling_masking_rate' => fake()->randomFloat(2, 0.01, 2.00),
      'nonmasking_rate' => fake()->randomFloat(2, 0.01, 2.00),
      'selling_nonmasking_rate' => fake()->randomFloat(2, 0.01, 2.00),
      'email_rate' => fake()->randomFloat(2, 0.01, 2.00),
      'created_by' => fake()->numberBetween(1, 10),
      'rate_type' => 'sms',
      'reseller_id' => fake()->numberBetween(1, 10),
    ];
  }
}
