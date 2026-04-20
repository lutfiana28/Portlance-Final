<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'user_id',
        'template_id',
        'package_id',
        'total_price',
        'payment_status',
        'order_status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function portfolio()
    {
        return $this->hasOne(OrderPortfolio::class);
    }

    public function files()
    {
        return $this->hasMany(PortfolioFile::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class);
    }

    public function finalResult()
    {
        return $this->hasOne(FinalResult::class);
    }
}