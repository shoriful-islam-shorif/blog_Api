<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Blog extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'custom_url', 
        'thumbnail', 
        'content',
        'status', 
        'category_id', 
        'user_id',
    ];
    // using accessor my blog thumbnail
    protected function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => asset('post_thumbnails/'.$value),
        );
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
