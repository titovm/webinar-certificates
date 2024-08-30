<?php

namespace App\Imports;

use App\Models\Participant;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ParticipantsImport implements ToModel, WithStartRow
{
    protected $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Start reading from row 2, skipping the header row.
     */
    public function startRow(): int
    {
        return 2;
    }

    /**
     * Map each row to a Participant model.
     * Use column numbers for name and email, and filter by "completed" in column 6.
     */
    public function model(array $row)
    {
        // Check if column 6 contains the word "completed"
        if (isset($row[5]) && strtolower($row[5]) === 'completed') {
            // Create the participant
            $participant = Participant::create([
                'name' => $row[7],  // Column 8 for name
                'email' => $row[8], // Column 9 for email
                'certificate_id' => $this->certificate->id,
                'certificate_url' => '',  // This will be updated after generating the PDF
            ]);

            // Generate the PDF certificate
            $pdf = Pdf::loadView('certificates.pdf', [
                'certificate' => $this->certificate,
                'participant' => $participant,
            ]);

            // Save the PDF to storage
            $fileName = 'certificates/' . $participant->id . '-certificate.pdf';
            Storage::disk('public')->put($fileName, $pdf->output());

            // Update the participant's certificate_url field with the storage URL
            $participant->update([
                'certificate_url' => Storage::url($fileName),
            ]);

            return $participant;
        }

        // Return null to skip rows that don't match the condition
        return null;
    }
}
