<?php

namespace Database\Factories;

use App\Models\ContentLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use TetOtt\HelperModule\Constants\ContentActions;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentLog>
 */
class ContentLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContentLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_id' => $this->faker->numberBetween(1, 100),
            'action' => $this->faker->randomElement(ContentActions::ACTIONS),
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}

