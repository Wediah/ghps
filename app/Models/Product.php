<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'farm_id', 'name', 'description', 'category', 'price', 'quantity',
        'image_url', 'is_approved'
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function orders()
    {
        return $this->hasMany(OrderItem::class);
    }
}
