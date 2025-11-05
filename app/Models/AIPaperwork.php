<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIPaperwork extends Model
{
    protected $fillable = [
        'user_id',
        'brand_id',
        'filepath',
        'original_filename',
        'extracted_text',
        'prompt_input',
        'prompt_output',
        'ai_extracted_customer',
        'ai_extracted_paperwork',
        'transfers_history',
        'status'
    ];

    protected $casts = [
        'ai_extracted_customer' => 'array',
        'ai_extracted_paperwork' => 'array',
        'transfers_history' => 'array',
        'status' => 'integer',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $table = 'ai_paperworks';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('c'); // ISO 8601 format with timezone
    }
} 
