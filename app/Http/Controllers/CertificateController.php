<?php
namespace App\Http\Controllers;

use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCertificateMail;

class CertificateController extends Controller
{
    public function generate(Participant $participant)
    {
        $certificate = $participant->certificate;

        // Generate the PDF certificate
        $pdf = Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'participant' => $participant,
        ]);

        // Save the PDF to storage
        $fileName = 'certificates/' . $participant->id . '-certificate.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        // Update the participant's certificate_url field with the storage URL
        $participant->update([
            'certificate_url' => Storage::url($fileName),
        ]);

        // Send the email with the new certificate
        Mail::to($participant->email)->send(new SendCertificateMail($participant));

        return redirect()->back()->with('success', 'Certificate generated and sent successfully!');
    }
}