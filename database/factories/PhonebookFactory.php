<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Phonebook\App\Models\Phonebook;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Phonebook\App\Models\Phonebook>
 */
class PhonebookFactory extends Factory
{
    protected $model = Phonebook::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => fake()->numberBetween(1, 5000),
            'name_en' => fake()->name(),
            'name_bn' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'profession' => fake()->word(5, true),
            'gender' => fake()->randomElement(['Male', 'Female', 'Other']),
            'dob' => fake()->date(),
            'division' => fake()->word(5, true),
            'district' => fake()->word(5, true),
            'upazilla' => fake()->word(5, true),
            'blood_group' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']),
            'user_id' => fake()->numberBetween(1, 1000),
            'status' => fake()->randomElement(['Active', 'Inactive']),
            'subscribed' => 1,
            'remarks' => fake()->sentence(),
            'unsubscribe_date' => fake()->date(),
            'reseller_id' => fake()->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
