<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'company_name',
        'address',
        'city',
        'region',
        'country',
        'year_established',
        'website',
        'brochure_path',
    ];

    protected function casts(): array
    {
        return [
            'year_established' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function getBrochureUrlAttribute(): ?string
    {
        if (!$this->brochure_path) {
            return null;
        }

        return asset('storage/' . $this->brochure_path);
    }
}
