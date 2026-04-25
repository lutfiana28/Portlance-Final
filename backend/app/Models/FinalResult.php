<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'preview_link',
        'final_link',
        'final_file',
        'final_note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}