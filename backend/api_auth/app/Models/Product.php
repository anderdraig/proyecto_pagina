<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'img_url'
    ];

    public function quotations()
    {
        return $this->belongsToMany(Quotation::class);
    }

    public function orders(){
        return $this->belongsToMany(Order::class, 'order_product')
                    ->withPivot('quantity', 'price_at_time')
                    ->withTimestamps();
    }
}
