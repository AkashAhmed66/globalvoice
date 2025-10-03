<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Users\App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Users\App\Models\User>
 */
class UserFactory extends Factory
{
  protected $model = User::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'user_group_id' => 4,
      'name' => fake()->name(),
      'username' => fake()->unique()->userName(),
      'email' => fake()->unique()->safeEmail(),
      'email_verified_at' => now(),
      'password' => bcrypt('123456'), // password
      'address' => fake()->address(),
      'mobile' => fake()->phoneNumber(),
      'last_login_time' => now(),
      'status' => fake()->randomElement(['Active', 'Inactive']),
      'photo' => null,
      'tps' => fake()->numberBetween(1, 100),
      'dipping' => fake()->randomElement(['Active', 'Inactive']),
      'created_by' => fake()->numberBetween(1, 10),
      'apikey' => fake()->uuid(),
      'api_status' => fake()->randomElement(['ACTIVE', 'INACTIVE', 'PENDING']),
      'billing_type' => fake()->randomElement(['prepaid', 'postpaid']),
      'mrc_otc' => fake()->randomFloat(2, 0.01, 2.00),
      'duration_validity' => 100,
      'bill_start' => 0,
      'reseller_id' => fake()->numberBetween(1, 10),
      'assign_user_id' => null,
      'sms_rate_id' => fake()->numberBetween(1, 100),
      'email_rate_id' => null,
      'remember_token' => null,
      'available_balance' => fake()->numberBetween(100, 1000),
      'created_at' => now(),
      'updated_at' => now(),
    ];
  }
}
