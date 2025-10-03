<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Messages\App\Models\Message;
use Modules\Messages\App\Models\Outbox;
use Modules\Smsconfig\App\Models\SenderId;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Message\App\Models\Outbox>
 */
class OutboxFactory extends Factory
{
    protected $model = Outbox::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'srcmn' => fake()->randomElement(['WEB', 'API']),
            'mask' => SenderId::inRandomOrder()->value('senderid'),
            'destmn' => fake()->numerify(str_repeat('#', 15)),
            'message' => fake()->sentence(320),
            'country_code' => fake()->randomElement(['91', '1']),
            'operator_prefix' => fake()->randomElement(['17', '18','15', '16', '19']),
            'status' => fake()->randomElement(['Delivered', 'Sent', 'Failed', 'Processing', 'Queue', 'Hold']),
            'created_at' => fake()->dateTime(),
            'sent_time' => fake()->dateTime(),
            'ton' => fake()->randomElement(['1', '2']),
            'npi' => fake()->randomElement(['1', '2']),
            'message_type' => fake()->randomElement(['text']),
            'is_unicode' => fake()->randomElement(['0', '1']),
            'smscount' => fake()->numberBetween(1, 10),
            'esm_class' => null,
            'data_coding' => null,
            'reference_id' => Message::inRandomOrder()->value('id'),
            'last_updated' => fake()->dateTime(),
            'schedule_time' => null,
            'retry_count' => fake()->numberBetween(0, 5),
            'user_id' => fake()->numberBetween(1, 1000),
            'remarks' => fake()->sentence(10),
            'uuid' => fake()->unique()->numberBetween(0, 6553595959599),
            'priority' => 0,
            'blocked_status' => 1,
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
            'error_code' => null,
            'error_message' => null,
            'sms_cost' => fake()->randomFloat(2, 0.01, 100.00),
            'sms_uniq_id' => fake()->uuid(),
            'dlr_status_code' => 200,
            'dlr_status' => null,
            'dlr_status_meaning' => fake()->sentence(10),
        ];
    }
}
