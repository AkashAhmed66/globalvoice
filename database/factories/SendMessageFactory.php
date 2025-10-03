<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Messages\App\Models\Message;
use Modules\Phonebook\App\Models\Group;
use Modules\Phonebook\App\Models\Phonebook;
use Modules\Smsconfig\App\Models\SenderId;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modules\Message\App\Models\Message>
 */
class SendMessageFactory extends Factory
{
    protected $model = Message::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_type' => fake()->randomElement(['text', 'unicode']),
            'user_id' => fake()->numberBetween(1, 1000),
            'orderid' => fake()->numberBetween(1, 500000),
            'source' => fake()->randomElement(['WEB', 'API', 'IPTSP']),
            'mobile_no_column' => fake()->phoneNumber(),
            'message' => fake()->sentence(320),
            'json_data' => null,
            'senderid' => SenderId::inRandomOrder()->value('senderid'),
            'recipient' => Phonebook::inRandomOrder()->value('phone'),
            'group_id' => Group::inRandomOrder()->value('id'),
            'date' => now(),
            'pages' => fake()->numberBetween(1, 10),
            'status' => fake()->randomElement(['Draft', 'Sending', 'Sent', 'Failed']),
            'units' => fake()->numberBetween(1, 10),
            'sentFrom' => 'Panel',
            'is_mms' => 0,
            'sms_count' => fake()->numberBetween(1, 2),
            'is_unicode' => 0,
            'IP' => fake()->ipv4(),
            'gateway_id' => null,
            'sms_type' => 'sendSms',
            'scheduleDateTime' => null,
            'search_param' => null,
            'error' => null,
            'file' => null,
            'priority' => null,
            'blocked_status' => 2,
            'created_at' => now(),
            'updated_at' => now(),
            'content_type' => 'Text',
            'campaign_name' => null,
            'campaign_id' => null,
            'sms_from' => fake()->randomElement(['WEB', 'API']),
            'start_time' => fake()->dateTime(),
            'end_time' => fake()->dateTime(),
            'sms_queued' => 0,
            'sms_processing' => 0,
            'sms_sent' => 0,
            'sms_delivered' => 0,
            'sms_failed' => 0,
            'sms_blocked' => 0,
            'is_complete' => 0,
            'is_pause' => fake()->randomElement(['Active', 'Inactive']),
            'archived' => 0,
            'total_recipient' => fake()->numberBetween(1, 100),
            'total_cost' => fake()->randomFloat(2, 0.01, 100.00),
            'is_dnd_applicable' => fake()->randomElement(['Yes', 'No']),
            'client_transaction_id' => fake()->uuid(),
            'rn_code' => null,
            'type' => fake()->randomElement(['text', 'unicode']),
            'long_sms' => null,
            'is_long_sms' => 0,
            'unicode' => 0,
            'data_coding' => 0,
            'is_flash' => 0,
            'flash' => 0,
            'is_promotional' => 1,
            'is_file_processed' => 0,
        ];
    }
}
