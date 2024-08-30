<?php

namespace App\Models;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Participant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'certificate_id',
        'certificate_url',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class);
    }
}
