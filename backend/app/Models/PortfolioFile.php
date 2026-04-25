<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortfolioFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'file_type',
        'file_path',
        'original_name',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}