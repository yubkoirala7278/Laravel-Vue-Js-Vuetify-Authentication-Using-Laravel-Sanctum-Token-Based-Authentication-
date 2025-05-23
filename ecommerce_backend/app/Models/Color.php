<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Color extends Model
{
    use HasFactory;
    protected $fillable = ['slug', 'name', 'status'];
    // use slug instead of id
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($color) {
            $color->slug = static::generateUniqueSlug();
        });
    }

    // generate new slug when color created
    private static function generateUniqueSlug()
    {
        do {
            $slug = Str::random(8); // Generates an 8-character random string
        } while (self::where('slug', $slug)->exists()); // Ensure uniqueness

        return $slug;
    }
}
