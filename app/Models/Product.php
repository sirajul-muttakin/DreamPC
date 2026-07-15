<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'sku', 'brand', 'price', 
        'stock_quantity', 'description', 'image_path'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function specifications()
    {
        return $this->hasMany(Specification::class);
    }
}