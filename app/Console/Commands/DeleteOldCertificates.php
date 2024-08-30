<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Participant;
use Carbon\Carbon;

class DeleteOldCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-old-certificates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $participants = Participant::whereNotNull('certificate_url')->get();

        foreach ($participants as $participant) {
            $filePath = str_replace('/storage/', '', $participant->certificate_url);
            $fullPath = Storage::disk('public')->path($filePath);

            if (Storage::disk('public')->exists($filePath)) {
                $lastModified = Carbon::createFromTimestamp(Storage::disk('public')->lastModified($filePath));

                if ($lastModified->lt(now()->subMonth())) {
                    Storage::disk('public')->delete($filePath);
                    $participant->update(['certificate_url' => null]);

                    $this->info("Deleted certificate for participant {$participant->name}.");
                }
            }
        }

        return Command::SUCCESS;
    }
}
