<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => (float) $this->price,
            'category' => $this->category,
            'colors' => $this->colors,
            'images' => $this->image_urls,
            'image_url' => $this->image_url,
            'is_new' => $this->is_new,
            'is_best_seller' => $this->is_best_seller,
            'is_active' => $this->is_active,
            'stock_quantity' => $this->stock_quantity,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'created_at' => $this->created_at,
        ];
    }
}
