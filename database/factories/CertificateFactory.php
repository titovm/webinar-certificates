<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition()
    {
        return [
            'webinar_name' => $this->faker->sentence(3),
            'lecturer_name' => $this->faker->name(),
            'date' => $this->faker->date(),
            'lecture_type' => $this->faker->randomElement(['Webinar', 'Module', 'Masterclass']),
            'hours' => $this->faker->numberBetween(1, 10),
        ];
    }
}
