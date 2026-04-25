<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPortfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'full_name',
        'photo_profile',
        'profession',
        'short_bio',
        'contact_email',
        'phone_number',
        'domicile',
        'social_links',
        'skills',
        'tools',
        'capability_summary',
        'projects',
        'services',
        'testimonials',
        'certificates',
        'faqs',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'skills' => 'array',
            'tools' => 'array',
            'projects' => 'array',
            'services' => 'array',
            'testimonials' => 'array',
            'certificates' => 'array',
            'faqs' => 'array',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}