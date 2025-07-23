<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'location', 'latitude', 'longitude',
        'contact_phone', 'contact_email', 'is_approved', 'image_url'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function reviews()
    {
        return $this->hasMany(FarmReview::class);
    }
}
