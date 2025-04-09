<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['slug', 'name', 'description','image','price','compare_price','is_featured','status','category_id'];

    // use slug instead of id
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = static::generateUniqueSlug();
        });
    }

    // generate new slug when product created
    private static function generateUniqueSlug()
    {
        do {
            $slug = Str::random(8); // Generates an 8-character random string
        } while (self::where('slug', $slug)->exists()); // Ensure uniqueness

        return $slug;
    }

    // Relationship with category
    public function category(){
        return $this->belongsTo(Category::class);
    }
}
