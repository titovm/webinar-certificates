<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Participant;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    public function run()
    {
        // Generate 10 certificates
        Certificate::factory()
            ->count(10)
            ->create()
            ->each(function ($certificate) {
                // For each certificate, generate 5 participants
                Participant::factory()
                    ->count(5)
                    ->create([
                        'certificate_id' => $certificate->id,
                    ]);
            });
    }
}
