<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'discount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

     public function products()
    {
        return $this->belongsToMany(Product::class, 'quotation_product')
                    ->withPivot('quantity', 'price_at_time')
                    ->withTimestamps();
                    
    }
}
