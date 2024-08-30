<?php

namespace Database\Factories;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'certificate_url' => $this->faker->url(),  // You can use fake URLs or leave this empty initially
            'certificate_id' => null,  // This will be set when creating participants related to a certificate
        ];
    }
}
