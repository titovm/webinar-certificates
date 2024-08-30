<?php

namespace App\Models;

use App\Models\Participant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Certificate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'webinar_name',
        'lecturer_name',
        'date',
        'lecture_type',
        'hours',
    ];

    protected static function booted()
    {
        static::deleting(function ($certificate) {
            // Delete all participants and their certificate files
            foreach ($certificate->participants as $participant) {
                if ($participant->certificate_url) {
                    $filePath = str_replace('/storage/', '', $participant->certificate_url);
                    Storage::disk('public')->delete($filePath);
                }

                // Delete the participant
                $participant->delete();
            }
        });
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
