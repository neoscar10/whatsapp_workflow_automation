<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AutomationNode>
 */
class AutomationNodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'automation_flow_id' => \App\Models\AutomationFlow::factory(),
            'type' => 'action',
            'subtype' => 'whatsapp_message',
            'label' => $this->faker->words(2, true),
            'config' => [],
        ];
    }
}
