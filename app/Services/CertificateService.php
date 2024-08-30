<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Participant;

class CertificateService
{
    /**
     * Generate a certificate, store it, and update the participant's record with the URL.
     *
     * @param Participant $participant
     * @param $certificate
     * @return string The URL of the generated certificate
     */
    public function generateAndStoreCertificate(Participant $participant, $certificate): string
    {
        // Generate the PDF certificate
        $pdf = Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'participant' => $participant,
        ]);

        // Define the file path
        $fileName = 'certificates/' . $participant->id . '-certificate.pdf';

        // Store the PDF in public disk
        Storage::disk('public')->put($fileName, $pdf->output());

        // Get the public URL of the stored certificate
        $certificateUrl = Storage::url($fileName);

        // Update the participant's certificate_url field
        $participant->update([
            'certificate_url' => $certificateUrl,
        ]);

        return $certificateUrl;
    }
}