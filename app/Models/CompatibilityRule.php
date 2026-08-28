<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompatibilityRule extends Model
{
    protected $fillable = [
        'category_a_id', 'category_b_id', 
        'spec_key_a', 'spec_key_b', 'rule_type'
    ];

    public function categoryA()
    {
        return $this->belongsTo(Category::class, 'category_a_id');
    }

    public function categoryB()
    {
        return $this->belongsTo(Category::class, 'category_b_id');
    }
}
