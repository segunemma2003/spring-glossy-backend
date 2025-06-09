<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'category',
        'colors',
        'is_new',
        'is_best_seller',
        'is_active',
        'stock_quantity',
        'weight',
        'dimensions',
        'sku',
        'images', // JSON array of image paths
    ];

    protected function casts(): array
    {
        return [
            'category' => 'array',
            'colors' => 'array',
            'images' => 'array',
            'is_new' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getImageUrlAttribute()
    {
        if (!$this->images || empty($this->images)) {
            return null;
        }

        $firstImage = $this->images[0];
        return Storage::disk('s3')->url($firstImage);
    }

    public function getImageUrlsAttribute()
    {
        if (!$this->images || empty($this->images)) {
            return [];
        }

        return collect($this->images)->map(function ($image) {
            return Storage::disk('s3')->url($image);
        })->toArray();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->slug = Str::slug($product->name);
            $product->sku = 'SG-' . strtoupper(Str::random(8));
        });
    }
}
